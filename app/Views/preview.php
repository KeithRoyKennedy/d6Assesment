<?php

/**
 * Invoice Preview View
 *
 * Displays a formatted invoice for viewing and printing.
 * Shows customer details, line items, and totals in a professional layout.
 *
 * @package Keith\D6assesment\Views
 */

// Get invoice ID from query parameter
$invoiceId = $_GET['id'] ?? null;

if (!$invoiceId) {
    die('Invoice ID is required');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Preview</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2563eb;
        }

        .company-info h1 {
            color: #2563eb;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .company-info p {
            color: #666;
            font-size: 14px;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-meta h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .invoice-meta p {
            margin: 5px 0;
            color: #666;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .status-draft { background: #fef3c7; color: #92400e; }
        .status-sent { background: #dbeafe; color: #1e40af; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-overdue { background: #fee2e2; color: #991b1b; }

        .parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .party h3 {
            color: #2563eb;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .party p {
            color: #333;
            line-height: 1.6;
            margin: 5px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table thead {
            background: #f8fafc;
        }

        .items-table th {
            padding: 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #333;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        .items-table tbody tr:hover {
            background: #f8fafc;
        }

        .totals {
            margin-left: auto;
            width: 300px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            color: #333;
        }

        .totals-row.subtotal,
        .totals-row.tax {
            border-bottom: 1px solid #e2e8f0;
        }

        .totals-row.grand-total {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
            border-top: 2px solid #2563eb;
            padding-top: 15px;
            margin-top: 10px;
        }

        .notes {
            margin-top: 40px;
            padding: 20px;
            background: #f8fafc;
            border-left: 4px solid #2563eb;
        }

        .notes h3 {
            color: #2563eb;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .notes p {
            color: #333;
            line-height: 1.6;
        }

        .actions {
            margin-top: 30px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #64748b;
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .loading {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .error {
            text-align: center;
            padding: 60px 20px;
            color: #dc2626;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
                padding: 20px;
            }

            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div id="invoiceContent" class="loading">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Loading invoice...</p>
        </div>
    </div>

    <script>
        /**
         * Load and display invoice data
         */
        async function loadInvoice() {
            const invoiceId = new URLSearchParams(window.location.search).get('id');
            
            if (!invoiceId) {
                showError('Invoice ID is required');
                return;
            }

            try {
                const response = await fetch(`/api/invoices/${invoiceId}`);
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Failed to load invoice');
                }

                renderInvoice(data);
            } catch (error) {
                showError(error.message);
            }
        }

        /**
         * Render invoice HTML
         * @param {Object} invoice - Invoice data object
         */
        function renderInvoice(invoice) {
            const container = document.getElementById('invoiceContent');
            
            // Calculate totals
            let subtotal = 0;
            let taxTotal = 0;
            
            invoice.items.forEach(item => {
                const lineTotal = parseFloat(item.quantity) * parseFloat(item.unit_price);
                const lineTax = lineTotal * (parseFloat(item.tax_rate) / 100);
                subtotal += lineTotal;
                taxTotal += lineTax;
            });
            
            const grandTotal = subtotal + taxTotal;

            container.innerHTML = `
                <div class="invoice-header">
                    <div class="company-info">
                        <h1><i class="fas fa-file-invoice-dollar"></i> Invoice</h1>
                        <p>Invoice Capture System</p>
                    </div>
                    <div class="invoice-meta">
                        <h2>${invoice.invoice_number}</h2>
                        <p><strong>Date:</strong> ${invoice.invoice_date}</p>
                        <p><strong>Due Date:</strong> ${invoice.due_date}</p>
                        <span class="status-badge status-${invoice.status}">${invoice.status}</span>
                    </div>
                </div>

                <div class="parties">
                    <div class="party">
                        <h3>Bill To</h3>
                        <p><strong>${invoice.customer_name || 'N/A'}</strong></p>
                        ${invoice.customer_email ? `<p><i class="fas fa-envelope"></i> ${invoice.customer_email}</p>` : ''}
                        ${invoice.customer_phone ? `<p><i class="fas fa-phone"></i> ${invoice.customer_phone}</p>` : ''}
                        ${invoice.customer_address ? `<p><i class="fas fa-map-marker-alt"></i> ${invoice.customer_address}</p>` : ''}
                    </div>
                    <div class="party">
                        <h3>Invoice Details</h3>
                        <p><strong>Invoice Number:</strong> ${invoice.invoice_number}</p>
                        <p><strong>Invoice Date:</strong> ${invoice.invoice_date}</p>
                        <p><strong>Due Date:</strong> ${invoice.due_date}</p>
                        <p><strong>Status:</strong> ${invoice.status.toUpperCase()}</p>
                    </div>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="text-align: center;">Quantity</th>
                            <th style="text-align: right;">Unit Price</th>
                            <th style="text-align: right;">Tax Rate</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${invoice.items.map(item => {
                            const lineTotal = parseFloat(item.quantity) * parseFloat(item.unit_price);
                            return `
                                <tr>
                                    <td>${item.description}</td>
                                    <td style="text-align: center;">${parseFloat(item.quantity).toFixed(2)}</td>
                                    <td style="text-align: right;">$${parseFloat(item.unit_price).toFixed(2)}</td>
                                    <td style="text-align: right;">${parseFloat(item.tax_rate).toFixed(2)}%</td>
                                    <td style="text-align: right;">$${lineTotal.toFixed(2)}</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>

                <div class="totals">
                    <div class="totals-row subtotal">
                        <span>Subtotal:</span>
                        <span>$${subtotal.toFixed(2)}</span>
                    </div>
                    <div class="totals-row tax">
                        <span>Tax:</span>
                        <span>$${taxTotal.toFixed(2)}</span>
                    </div>
                    <div class="totals-row grand-total">
                        <span>Total:</span>
                        <span>$${grandTotal.toFixed(2)}</span>
                    </div>
                </div>

                ${invoice.notes ? `
                    <div class="notes">
                        <h3><i class="fas fa-sticky-note"></i> Notes</h3>
                        <p>${invoice.notes}</p>
                    </div>
                ` : ''}

                <div class="actions">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print"></i> Print Invoice
                    </button>
                    <button onclick="window.close()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            `;
        }

        /**
         * Show error message
         * @param {string} message - Error message to display
         */
        function showError(message) {
            const container = document.getElementById('invoiceContent');
            container.className = 'error';
            container.innerHTML = `
                <i class="fas fa-exclamation-circle fa-2x"></i>
                <p>${message}</p>
                <button onclick="window.close()" class="btn btn-secondary" style="margin-top: 20px;">
                    <i class="fas fa-times"></i> Close
                </button>
            `;
        }

        // Load invoice on page load
        document.addEventListener('DOMContentLoaded', loadInvoice);
    </script>
</body>
</html>
