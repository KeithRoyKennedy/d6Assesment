/**
 * Invoice Application Class
 * 
 * Main application class for the Invoice Capture System.
 * Handles invoice creation, customer management, item management,
 * and all UI interactions.
 */
class InvoiceApp {
    /**
     * Constructor - initializes application state
     */
    constructor() {
        /** @type {Array} Line items in the current invoice */
        this.items = [];
        
        /** @type {number} Counter for generating unique item row IDs */
        this.itemCounter = 0;
        
        /** @type {Array} Available items from the database */
        this.availableItems = [];
        
        /** @type {Array} Available customers from the database */
        this.customers = [];
        
        this.init();
    }

    /**
     * Initialize the application
     * Loads data, sets up UI, and attaches event listeners
     * @async
     * @returns {Promise<void>}
     */
    async init() {
        await this.loadCustomers();
        await this.loadItems();
        await this.loadNextInvoiceNumber();
        await this.loadRecentInvoices();
        this.setDefaultDates();
        this.attachEventListeners();
        this.addLineItem();
    }

    /**
     * Load customers from the API
     * Fetches all customers and populates the customer dropdown
     * @async
     * @returns {Promise<void>}
     */
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

    /**
     * Populate the customer select dropdown
     * Adds "Add New Customer" option and all existing customers
     * @returns {void}
     */
    populateCustomerSelect() {
        const select = document.getElementById('customerSelect');
        select.innerHTML = '<option value="">Choose a customer...</option>';
        
        const addNewOption = document.createElement('option');
        addNewOption.value = 'add_new';
        addNewOption.textContent = '+ Add New Customer';
        addNewOption.style.fontWeight = 'bold';
        addNewOption.style.color = '#2563eb';
        select.appendChild(addNewOption);
        
        this.customers.forEach(customer => {
            const option = document.createElement('option');
            option.value = customer.id;
            option.textContent = customer.name;
            option.dataset.email = customer.email || '';
            select.appendChild(option);
        });
    }

    /**
     * Load items from the API
     * Fetches all available items for use in invoice line items
     * @async
     * @returns {Promise<void>}
     */
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

    /**
     * Load the next sequential invoice number
     * Fetches and displays the next available invoice number
     * @async
     * @returns {Promise<void>}
     */
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

    /**
     * Load and display recent invoices
     * Fetches the 5 most recent invoices and displays them in the sidebar
     * @async
     * @returns {Promise<void>}
     */
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

    /**
     * Set default dates for invoice
     * Sets invoice date to today and due date to 30 days from today
     * @returns {void}
     */
    setDefaultDates() {
        const today = new Date().toISOString().split('T')[0];
        const dueDate = new Date();
        dueDate.setDate(dueDate.getDate() + 30);
        const dueDateStr = dueDate.toISOString().split('T')[0];
        
        document.getElementById('invoiceDate').value = today;
        document.getElementById('dueDate').value = dueDateStr;
    }

    /**
     * Attach all event listeners
     * Sets up event handlers for form submission, modals, and user interactions
     * @returns {void}
     */
    attachEventListeners() {
        document.getElementById('addItemBtn').addEventListener('click', () => this.addLineItem());
        document.getElementById('invoiceForm').addEventListener('submit', (e) => this.handleSubmit(e));
        document.getElementById('resetBtn').addEventListener('click', () => this.resetForm());
        
        document.getElementById('customerSelect').addEventListener('change', (e) => this.handleCustomerSelect(e));
        
        document.getElementById('closeModal').addEventListener('click', () => this.closeCustomerModal());
        document.getElementById('cancelCustomer').addEventListener('click', () => this.closeCustomerModal());
        document.getElementById('customerForm').addEventListener('submit', (e) => this.handleCustomerSubmit(e));
        
        document.getElementById('customerPhone').addEventListener('input', (e) => this.validatePhoneInput(e));
        
        document.getElementById('customerModal').addEventListener('click', (e) => {
            if (e.target.id === 'customerModal') {
                this.closeCustomerModal();
            }
        });
        
        document.getElementById('closeItemModal').addEventListener('click', () => this.closeItemModal());
        document.getElementById('cancelItem').addEventListener('click', () => this.closeItemModal());
        document.getElementById('itemForm').addEventListener('submit', (e) => this.handleItemSubmit(e));
        
        document.getElementById('itemModal').addEventListener('click', (e) => {
            if (e.target.id === 'itemModal') {
                this.closeItemModal();
            }
        });
    }

    /**
     * Add a new line item row to the invoice
     * Creates a new row with item select, quantity, price, tax, and total fields
     * @returns {void}
     */
    addLineItem() {
        const itemsList = document.getElementById('itemsList');
        const itemRow = document.createElement('div');
        itemRow.className = 'item-row';
        itemRow.dataset.index = this.itemCounter;
        
        itemRow.innerHTML = `
            <div class="item-col item-desc">
                <select class="item-select" data-index="${this.itemCounter}">
                    <option value="">Select item...</option>
                    <option value="add_new" style="font-weight: bold; color: #2563eb;">+ Add New Item</option>
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

    /**
     * Handle item selection from dropdown
     * Opens modal for new items or populates fields for existing items
     * @param {Event} e - Change event from item select dropdown
     * @param {HTMLElement} itemRow - The item row element
     * @returns {void}
     */
    handleItemSelect(e, itemRow) {
        const option = e.target.selectedOptions[0];
        if (option.value === 'add_new') {
            this.currentItemRow = itemRow;
            this.openItemModal();
            e.target.value = '';
        } else if (option.value) {
            const descInput = itemRow.querySelector('.item-desc-input');
            const priceInput = itemRow.querySelector('.item-price-input');
            const taxInput = itemRow.querySelector('.item-tax-input');
            
            descInput.value = option.dataset.desc;
            priceInput.value = parseFloat(option.dataset.price).toFixed(2);
            taxInput.value = parseFloat(option.dataset.tax).toFixed(2);
            
            this.updateLineTotal(itemRow);
        }
    }

    /**
     * Update the total for a specific line item
     * Calculates line total (quantity * price) and updates invoice totals
     * @param {HTMLElement} itemRow - The item row element to update
     * @returns {void}
     */
    updateLineTotal(itemRow) {
        const qty = parseFloat(itemRow.querySelector('.item-qty-input').value) || 0;
        const price = parseFloat(itemRow.querySelector('.item-price-input').value) || 0;
        const lineTotal = qty * price;
        
        itemRow.querySelector('.line-total').textContent = lineTotal.toFixed(2);
        this.updateTotals();
    }

    /**
     * Remove a line item from the invoice
     * Prevents removal if only one item remains
     * @param {HTMLElement} itemRow - The item row element to remove
     * @returns {void}
     */
    removeLineItem(itemRow) {
        const itemsList = document.getElementById('itemsList');
        if (itemsList.children.length > 1) {
            itemRow.remove();
            this.updateTotals();
        } else {
            this.showMessage('Invoice must have at least one item', 'warning');
        }
    }

    /**
     * Update invoice totals
     * Calculates subtotal, tax total, and grand total from all line items
     * @returns {void}
     */
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

    /**
     * Handle invoice form submission
     * Validates data, sends to API, and updates UI on success
     * @async
     * @param {Event} e - Form submit event
     * @returns {Promise<void>}
     */
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

    /**
     * Collect and validate form data
     * Gathers customer info, dates, and all line items
     * @returns {Object|null} Form data object or null if validation fails
     */
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

    /**
     * Reset the invoice form to default state
     * Clears all fields, resets dates, and loads new invoice number
     * @returns {void}
     */
    resetForm() {
        document.getElementById('invoiceForm').reset();
        document.getElementById('itemsList').innerHTML = '';
        this.itemCounter = 0;
        this.setDefaultDates();
        this.addLineItem();
        this.updateTotals();
        this.loadNextInvoiceNumber();
    }

    /**
     * Handle customer dropdown selection
     * Opens modal if "Add New Customer" is selected
     * @param {Event} e - Change event from customer select
     * @returns {void}
     */
    handleCustomerSelect(e) {
        if (e.target.value === 'add_new') {
            this.openCustomerModal();
            e.target.value = '';
        }
    }

    /**
     * Validate phone number input
     * Restricts input to numbers and phone formatting characters
     * @param {Event} e - Input event from phone field
     * @returns {void}
     */
    validatePhoneInput(e) {
        const input = e.target;
        const value = input.value;
        const validChars = /^[0-9\-\+\(\)\s]*$/;
        
        if (!validChars.test(value)) {
            input.value = value.replace(/[^0-9\-\+\(\)\s]/g, '');
            this.showMessage('Phone number can only contain numbers, spaces, hyphens, parentheses, and +', 'warning');
        }
    }

    /**
     * Open the customer creation modal
     * @returns {void}
     */
    openCustomerModal() {
        const modal = document.getElementById('customerModal');
        modal.classList.add('active');
        document.getElementById('customerName').focus();
    }

    /**
     * Close the customer creation modal
     * Resets the form and removes active class
     * @returns {void}
     */
    closeCustomerModal() {
        const modal = document.getElementById('customerModal');
        modal.classList.remove('active');
        document.getElementById('customerForm').reset();
    }

    /**
     * Handle customer form submission
     * Creates new customer via API and updates customer dropdown
     * @async
     * @param {Event} e - Form submit event
     * @returns {Promise<void>}
     */
    async handleCustomerSubmit(e) {
        e.preventDefault();
        
        const formData = {
            name: document.getElementById('customerName').value,
            email: document.getElementById('customerEmail').value || null,
            phone: document.getElementById('customerPhone').value || null,
            address: document.getElementById('customerAddress').value || null
        };
        
        try {
            const saveBtn = document.getElementById('saveCustomer');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            const response = await fetch('/api/customers', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (response.ok) {
                this.showMessage('Customer added successfully!', 'success');
                this.closeCustomerModal();
                await this.loadCustomers();
                
                document.getElementById('customerSelect').value = data.id;
            } else {
                this.showMessage(data.error || 'Error adding customer', 'error');
            }
        } catch (error) {
            this.showMessage('Error: ' + error.message, 'error');
        } finally {
            const saveBtn = document.getElementById('saveCustomer');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Customer';
        }
    }

    /**
     * Open the item creation modal
     * @returns {void}
     */
    openItemModal() {
        const modal = document.getElementById('itemModal');
        modal.classList.add('active');
        document.getElementById('itemCode').focus();
    }

    /**
     * Close the item creation modal
     * Resets the form and clears current item row reference
     * @returns {void}
     */
    closeItemModal() {
        const modal = document.getElementById('itemModal');
        modal.classList.remove('active');
        document.getElementById('itemForm').reset();
        this.currentItemRow = null;
    }

    /**
     * Handle item form submission
     * Creates new item via API and populates the current line item
     * @async
     * @param {Event} e - Form submit event
     * @returns {Promise<void>}
     */
    async handleItemSubmit(e) {
        e.preventDefault();
        
        const formData = {
            code: document.getElementById('itemCode').value,
            description: document.getElementById('itemDescription').value,
            unit_price: parseFloat(document.getElementById('itemUnitPrice').value),
            tax_rate: parseFloat(document.getElementById('itemTaxRate').value)
        };
        
        try {
            const saveBtn = document.getElementById('saveItem');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            const response = await fetch('/api/items', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (response.ok) {
                this.showMessage('Item added successfully!', 'success');
                this.closeItemModal();
                await this.loadItems();
                
                if (this.currentItemRow) {
                    const select = this.currentItemRow.querySelector('.item-select');
                    select.value = data.id;
                    
                    const descInput = this.currentItemRow.querySelector('.item-desc-input');
                    const priceInput = this.currentItemRow.querySelector('.item-price-input');
                    const taxInput = this.currentItemRow.querySelector('.item-tax-input');
                    
                    descInput.value = data.description;
                    priceInput.value = parseFloat(data.unit_price).toFixed(2);
                    taxInput.value = parseFloat(data.tax_rate).toFixed(2);
                    
                    this.updateLineTotal(this.currentItemRow);
                }
            } else {
                this.showMessage(data.error || 'Error adding item', 'error');
            }
        } catch (error) {
            this.showMessage('Error: ' + error.message, 'error');
        } finally {
            const saveBtn = document.getElementById('saveItem');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Item';
        }
    }

    /**
     * Display a toast notification message
     * Shows message with icon and auto-dismisses after 5 seconds
     * @param {string} message - Message text to display
     * @param {string} [type='info'] - Message type: 'success', 'error', 'warning', or 'info'
     * @returns {void}
     */
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

/**
 * Initialize the application when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    new InvoiceApp();
});
