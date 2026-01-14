<?php

namespace Keith\D6assesment\Controllers;

use Keith\D6assesment\Models\Invoice;
use Keith\D6assesment\Models\InvoiceItem;
use Keith\D6assesment\Models\Customer;

class InvoiceController extends Controller
{
    private $invoiceModel;
    private $invoiceItemModel;
    private $customerModel;

    public function __construct()
    {
        parent::__construct();
        $this->invoiceModel = new Invoice();
        $this->invoiceItemModel = new InvoiceItem();
        $this->customerModel = new Customer();
    }

    public function index()
    {
        try {
            $invoices = $this->invoiceModel->findAllWithCustomers();
            $this->jsonResponse($invoices);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $invoice = $this->invoiceModel->findWithCustomer($id);
            
            if (!$invoice) {
                $this->errorResponse('Invoice not found', 404);
            }
            
            $invoice['items'] = $this->invoiceItemModel->findByInvoiceId($id);
            
            $this->jsonResponse($invoice);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store()
    {
        try {
            $data = $this->getRequestData();
            
            $required = ['customer_id', 'invoice_date', 'due_date', 'items'];
            $missing = $this->validateRequired($data, $required);
            
            if (!empty($missing)) {
                $this->errorResponse('Missing required fields: ' . implode(', ', $missing), 422);
            }
            
            $customer = $this->customerModel->findById($data['customer_id']);
            if (!$customer) {
                $this->errorResponse('Customer not found', 404);
            }
            
            if (!is_array($data['items']) || empty($data['items'])) {
                $this->errorResponse('Invoice must have at least one item', 422);
            }
            
            $this->db->beginTransaction();
            
            try {
                $subtotal = 0;
                $taxTotal = 0;
                
                foreach ($data['items'] as $item) {
                    if (!isset($item['quantity']) || !isset($item['unit_price']) || !isset($item['tax_rate'])) {
                        throw new \Exception('Invalid item data');
                    }
                    
                    $lineTotal = $item['quantity'] * $item['unit_price'];
                    $lineTax = $lineTotal * ($item['tax_rate'] / 100);
                    
                    $subtotal += $lineTotal;
                    $taxTotal += $lineTax;
                }
                
                $total = $subtotal + $taxTotal;
                
                $invoiceNumber = $data['invoice_number'] ?? $this->invoiceModel->getNextInvoiceNumber();
                
                $existingInvoice = $this->invoiceModel->findByInvoiceNumber($invoiceNumber);
                if ($existingInvoice) {
                    throw new \Exception('Invoice number already exists');
                }
                
                $invoiceData = [
                    'invoice_number' => $invoiceNumber,
                    'customer_id' => $data['customer_id'],
                    'invoice_date' => $data['invoice_date'],
                    'due_date' => $data['due_date'],
                    'subtotal' => $subtotal,
                    'tax_total' => $taxTotal,
                    'total' => $total,
                    'notes' => $data['notes'] ?? null,
                    'status' => $data['status'] ?? 'draft'
                ];
                
                $invoiceId = $this->invoiceModel->create($invoiceData);
                
                foreach ($data['items'] as $item) {
                    $lineTotal = $item['quantity'] * $item['unit_price'];
                    
                    $itemData = [
                        'invoice_id' => $invoiceId,
                        'item_id' => $item['item_id'] ?? null,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'tax_rate' => $item['tax_rate'],
                        'line_total' => $lineTotal
                    ];
                    
                    $this->invoiceItemModel->create($itemData);
                }
                
                $this->db->commit();
                
                $invoice = $this->invoiceModel->findWithCustomer($invoiceId);
                $invoice['items'] = $this->invoiceItemModel->findByInvoiceId($invoiceId);
                
                $this->jsonResponse($invoice, 201);
                
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function update($id)
    {
        try {
            $data = $this->getRequestData();
            
            $invoice = $this->invoiceModel->findById($id);
            if (!$invoice) {
                $this->errorResponse('Invoice not found', 404);
            }
            
            if (isset($data['customer_id'])) {
                $customer = $this->customerModel->findById($data['customer_id']);
                if (!$customer) {
                    $this->errorResponse('Customer not found', 404);
                }
            }
            
            $this->db->beginTransaction();
            
            try {
                if (isset($data['items'])) {
                    $subtotal = 0;
                    $taxTotal = 0;
                    
                    foreach ($data['items'] as $item) {
                        if (!isset($item['quantity']) || !isset($item['unit_price']) || !isset($item['tax_rate'])) {
                            throw new \Exception('Invalid item data');
                        }
                        
                        $lineTotal = $item['quantity'] * $item['unit_price'];
                        $lineTax = $lineTotal * ($item['tax_rate'] / 100);
                        
                        $subtotal += $lineTotal;
                        $taxTotal += $lineTax;
                    }
                    
                    $total = $subtotal + $taxTotal;
                    
                    $data['subtotal'] = $subtotal;
                    $data['tax_total'] = $taxTotal;
                    $data['total'] = $total;
                    
                    $this->invoiceItemModel->deleteByInvoiceId($id);
                    
                    foreach ($data['items'] as $item) {
                        $lineTotal = $item['quantity'] * $item['unit_price'];
                        
                        $itemData = [
                            'invoice_id' => $id,
                            'item_id' => $item['item_id'] ?? null,
                            'description' => $item['description'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'tax_rate' => $item['tax_rate'],
                            'line_total' => $lineTotal
                        ];
                        
                        $this->invoiceItemModel->create($itemData);
                    }
                    
                    unset($data['items']);
                }
                
                if (!empty($data)) {
                    $this->invoiceModel->updateInvoice($id, $data);
                }
                
                $this->db->commit();
                
                $invoice = $this->invoiceModel->findWithCustomer($id);
                $invoice['items'] = $this->invoiceItemModel->findByInvoiceId($id);
                
                $this->jsonResponse($invoice);
                
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $invoice = $this->invoiceModel->findById($id);
            if (!$invoice) {
                $this->errorResponse('Invoice not found', 404);
            }
            
            $this->invoiceModel->delete($id);
            $this->jsonResponse(['message' => 'Invoice deleted successfully']);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function updateStatus($id)
    {
        try {
            $data = $this->getRequestData();
            
            if (!isset($data['status'])) {
                $this->errorResponse('Status is required', 422);
            }
            
            $validStatuses = ['draft', 'sent', 'paid', 'overdue'];
            if (!in_array($data['status'], $validStatuses)) {
                $this->errorResponse('Invalid status. Must be one of: ' . implode(', ', $validStatuses), 422);
            }
            
            $invoice = $this->invoiceModel->findById($id);
            if (!$invoice) {
                $this->errorResponse('Invoice not found', 404);
            }
            
            $this->invoiceModel->updateStatus($id, $data['status']);
            $invoice = $this->invoiceModel->findById($id);
            
            $this->jsonResponse($invoice);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function getNextInvoiceNumber()
    {
        try {
            $nextNumber = $this->invoiceModel->getNextInvoiceNumber();
            $this->jsonResponse(['invoice_number' => $nextNumber]);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }
}
