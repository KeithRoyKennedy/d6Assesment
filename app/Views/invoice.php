<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Capture System</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-file-invoice-dollar"></i> Invoice Capture System</h1>
            <p class="subtitle">Create and manage invoices</p>
        </header>

        <main>
            <div class="app-container">
                <div class="left-panel">
                    <form id="invoiceForm" class="invoice-form">
                        <div class="form-header">
                            <h2><i class="fas fa-edit"></i> New Invoice</h2>
                            <div class="invoice-number">
                                <span>Invoice #:</span>
                                <strong id="invoiceNumberDisplay">Loading...</strong>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-user"></i> Customer Information</h3>
                            <div class="form-group">
                                <label for="customerSelect">Select Customer *</label>
                                <select id="customerSelect" name="customer_id" required>
                                    <option value="">Choose a customer...</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="invoiceDate">Invoice Date *</label>
                                    <input type="date" id="invoiceDate" name="invoice_date" required>
                                </div>
                                <div class="form-group">
                                    <label for="dueDate">Due Date *</label>
                                    <input type="date" id="dueDate" name="due_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="section-header">
                                <h3><i class="fas fa-list"></i> Line Items</h3>
                                <button type="button" id="addItemBtn" class="btn btn-secondary">
                                    <i class="fas fa-plus"></i> Add Item
                                </button>
                            </div>
                            
                            <div class="items-container">
                                <div class="items-header">
                                    <div class="item-col item-desc">Description</div>
                                    <div class="item-col item-qty">Qty</div>
                                    <div class="item-col item-price">Unit Price</div>
                                    <div class="item-col item-tax">Tax %</div>
                                    <div class="item-col item-total">Total</div>
                                    <div class="item-col item-action">Action</div>
                                </div>
                                <div id="itemsList">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-sticky-note"></i> Notes</h3>
                            <div class="form-group">
                                <textarea id="notes" name="notes" rows="3" 
                                          placeholder="Add any additional notes or terms..."></textarea>
                            </div>
                        </div>

                        <div class="totals-section">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span id="subtotal">0.00</span>
                            </div>
                            <div class="total-row">
                                <span>Tax Total:</span>
                                <span id="taxTotal">0.00</span>
                            </div>
                            <div class="total-row grand-total">
                                <span>Grand Total:</span>
                                <span id="grandTotal">0.00</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" id="resetBtn" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                            <button type="submit" id="saveBtn" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Invoice
                            </button>
                        </div>
                    </form>
                </div>

                <div class="right-panel">
                    <h2><i class="fas fa-history"></i> Recent Invoices</h2>
                    <div id="recentInvoices">
                        <div class="loading">Loading recent invoices...</div>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>Invoice Capture System &copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>

    <div id="messages"></div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
