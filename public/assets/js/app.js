class InvoiceApp {
    constructor() {
        this.items = [];
        this.itemCounter = 0;
        this.availableItems = [];
        this.customers = [];
        this.init();
    }

    async init() {
        await this.loadCustomers();
        await this.loadItems();
        await this.loadNextInvoiceNumber();
        await this.loadRecentInvoices();
        this.setDefaultDates();
        this.attachEventListeners();
        this.addLineItem();
    }

    async loadCustomers() {
        try {
            const response = await fetch('/api/customers');
            const data = await response.json();
            
            if (Array.isArray(data)) {
                this.customers = data;
                this.populateCustomerSelect();
            }
        } catch (error) {
            this.showMessage('Error loading customers: ' + error.message, 'error');
        }
    }

    populateCustomerSelect() {
        const select = document.getElementById('customerSelect');
        select.innerHTML = '<option value="">Choose a customer...</option>';
        
        this.customers.forEach(customer => {
            const option = document.createElement('option');
            option.value = customer.id;
            option.textContent = customer.name;
            option.dataset.email = customer.email || '';
            select.appendChild(option);
        });
    }

    async loadItems() {
        try {
            const response = await fetch('/api/items');
            const data = await response.json();
            
            if (Array.isArray(data)) {
                this.availableItems = data;
            }
        } catch (error) {
            this.showMessage('Error loading items: ' + error.message, 'error');
        }
    }

    async loadNextInvoiceNumber() {
        try {
            const response = await fetch('/api/invoices/next-number/generate');
            const data = await response.json();
            
            if (data.invoice_number) {
                document.getElementById('invoiceNumberDisplay').textContent = data.invoice_number;
            }
        } catch (error) {
            this.showMessage('Error loading invoice number: ' + error.message, 'error');
        }
    }

    async loadRecentInvoices() {
        try {
            const response = await fetch('/api/invoices');
            const data = await response.json();
            
            const container = document.getElementById('recentInvoices');
            
            if (Array.isArray(data) && data.length > 0) {
                container.innerHTML = data.slice(0, 5).map(invoice => `
                    <div class="invoice-card">
                        <div class="invoice-header">
                            <strong>${invoice.invoice_number}</strong>
                            <span class="status status-${invoice.status}">${invoice.status}</span>
                        </div>
                        <div class="invoice-details">
                            <p><i class="fas fa-user"></i> ${invoice.customer_name || 'N/A'}</p>
                            <p><i class="fas fa-calendar"></i> ${invoice.invoice_date}</p>
                            <p class="invoice-total"><i class="fas fa-dollar-sign"></i> ${parseFloat(invoice.total).toFixed(2)}</p>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="no-data">No recent invoices</p>';
            }
        } catch (error) {
            document.getElementById('recentInvoices').innerHTML = '<p class="error">Error loading invoices</p>';
        }
    }

    setDefaultDates() {
        const today = new Date().toISOString().split('T')[0];
        const dueDate = new Date();
        dueDate.setDate(dueDate.getDate() + 30);
        const dueDateStr = dueDate.toISOString().split('T')[0];
        
        document.getElementById('invoiceDate').value = today;
        document.getElementById('dueDate').value = dueDateStr;
    }

    attachEventListeners() {
        document.getElementById('addItemBtn').addEventListener('click', () => this.addLineItem());
        document.getElementById('invoiceForm').addEventListener('submit', (e) => this.handleSubmit(e));
        document.getElementById('resetBtn').addEventListener('click', () => this.resetForm());
    }

    addLineItem() {
        const itemsList = document.getElementById('itemsList');
        const itemRow = document.createElement('div');
        itemRow.className = 'item-row';
        itemRow.dataset.index = this.itemCounter;
        
        itemRow.innerHTML = `
            <div class="item-col item-desc">
                <select class="item-select" data-index="${this.itemCounter}">
                    <option value="">Select item...</option>
                    ${this.availableItems.map(item => `
                        <option value="${item.id}" 
                                data-price="${item.unit_price}" 
                                data-tax="${item.tax_rate}" 
                                data-desc="${item.description}">
                            ${item.code} - ${item.description}
                        </option>
                    `).join('')}
                </select>
                <input type="text" class="item-desc-input" placeholder="Description" required>
            </div>
            <div class="item-col item-qty">
                <input type="number" class="item-qty-input" value="1" min="0.01" step="0.01" required>
            </div>
            <div class="item-col item-price">
                <input type="number" class="item-price-input" value="0" min="0" step="0.01" required>
            </div>
            <div class="item-col item-tax">
                <input type="number" class="item-tax-input" value="0" min="0" max="100" step="0.01" required>
            </div>
            <div class="item-col item-total">
                <span class="line-total">0.00</span>
            </div>
            <div class="item-col item-action">
                <button type="button" class="btn-remove">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        itemsList.appendChild(itemRow);
        
        const select = itemRow.querySelector('.item-select');
        const descInput = itemRow.querySelector('.item-desc-input');
        const qtyInput = itemRow.querySelector('.item-qty-input');
        const priceInput = itemRow.querySelector('.item-price-input');
        const taxInput = itemRow.querySelector('.item-tax-input');
        const removeBtn = itemRow.querySelector('.btn-remove');
        
        select.addEventListener('change', (e) => this.handleItemSelect(e, itemRow));
        qtyInput.addEventListener('input', () => this.updateLineTotal(itemRow));
        priceInput.addEventListener('input', () => this.updateLineTotal(itemRow));
        taxInput.addEventListener('input', () => this.updateLineTotal(itemRow));
        removeBtn.addEventListener('click', () => this.removeLineItem(itemRow));
        
        this.itemCounter++;
    }

    handleItemSelect(e, itemRow) {
        const option = e.target.selectedOptions[0];
        if (option.value) {
            const descInput = itemRow.querySelector('.item-desc-input');
            const priceInput = itemRow.querySelector('.item-price-input');
            const taxInput = itemRow.querySelector('.item-tax-input');
            
            descInput.value = option.dataset.desc;
            priceInput.value = parseFloat(option.dataset.price).toFixed(2);
            taxInput.value = parseFloat(option.dataset.tax).toFixed(2);
            
            this.updateLineTotal(itemRow);
        }
    }

    updateLineTotal(itemRow) {
        const qty = parseFloat(itemRow.querySelector('.item-qty-input').value) || 0;
        const price = parseFloat(itemRow.querySelector('.item-price-input').value) || 0;
        const lineTotal = qty * price;
        
        itemRow.querySelector('.line-total').textContent = lineTotal.toFixed(2);
        this.updateTotals();
    }

    removeLineItem(itemRow) {
        const itemsList = document.getElementById('itemsList');
        if (itemsList.children.length > 1) {
            itemRow.remove();
            this.updateTotals();
        } else {
            this.showMessage('Invoice must have at least one item', 'warning');
        }
    }

    updateTotals() {
        let subtotal = 0;
        let taxTotal = 0;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.item-price-input').value) || 0;
            const taxRate = parseFloat(row.querySelector('.item-tax-input').value) || 0;
            
            const lineTotal = qty * price;
            const lineTax = lineTotal * (taxRate / 100);
            
            subtotal += lineTotal;
            taxTotal += lineTax;
        });
        
        const grandTotal = subtotal + taxTotal;
        
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('taxTotal').textContent = taxTotal.toFixed(2);
        document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        const formData = this.collectFormData();
        
        if (!formData) {
            return;
        }
        
        try {
            const saveBtn = document.getElementById('saveBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            const response = await fetch('/api/invoices', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (response.ok) {
                this.showMessage('Invoice saved successfully!', 'success');
                this.resetForm();
                await this.loadRecentInvoices();
                await this.loadNextInvoiceNumber();
            } else {
                this.showMessage(data.error || 'Error saving invoice', 'error');
            }
        } catch (error) {
            this.showMessage('Error: ' + error.message, 'error');
        } finally {
            const saveBtn = document.getElementById('saveBtn');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Invoice';
        }
    }

    collectFormData() {
        const customerId = document.getElementById('customerSelect').value;
        const invoiceDate = document.getElementById('invoiceDate').value;
        const dueDate = document.getElementById('dueDate').value;
        const notes = document.getElementById('notes').value;
        const invoiceNumber = document.getElementById('invoiceNumberDisplay').textContent;
        
        if (!customerId) {
            this.showMessage('Please select a customer', 'warning');
            return null;
        }
        
        const items = [];
        const itemRows = document.querySelectorAll('.item-row');
        
        if (itemRows.length === 0) {
            this.showMessage('Please add at least one item', 'warning');
            return null;
        }
        
        for (const row of itemRows) {
            const itemId = row.querySelector('.item-select').value || null;
            const description = row.querySelector('.item-desc-input').value;
            const quantity = parseFloat(row.querySelector('.item-qty-input').value);
            const unitPrice = parseFloat(row.querySelector('.item-price-input').value);
            const taxRate = parseFloat(row.querySelector('.item-tax-input').value);
            
            if (!description || quantity <= 0 || unitPrice < 0) {
                this.showMessage('Please fill in all item fields correctly', 'warning');
                return null;
            }
            
            items.push({
                item_id: itemId,
                description: description,
                quantity: quantity,
                unit_price: unitPrice,
                tax_rate: taxRate
            });
        }
        
        return {
            invoice_number: invoiceNumber,
            customer_id: customerId,
            invoice_date: invoiceDate,
            due_date: dueDate,
            notes: notes,
            items: items
        };
    }

    resetForm() {
        document.getElementById('invoiceForm').reset();
        document.getElementById('itemsList').innerHTML = '';
        this.itemCounter = 0;
        this.setDefaultDates();
        this.addLineItem();
        this.updateTotals();
        this.loadNextInvoiceNumber();
    }

    showMessage(message, type = 'info') {
        const messagesContainer = document.getElementById('messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message message-${type}`;
        messageDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
            <button class="message-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        messagesContainer.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new InvoiceApp();
});
