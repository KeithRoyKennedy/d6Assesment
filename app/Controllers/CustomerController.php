<?php

namespace Keith\D6assesment\Controllers;

use Keith\D6assesment\Models\Customer;

class CustomerController extends Controller
{
    private $customerModel;

    public function __construct()
    {
        parent::__construct();
        $this->customerModel = new Customer();
    }

    public function index()
    {
        try {
            $customers = $this->customerModel->findAll();
            $this->jsonResponse($customers);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $customer = $this->customerModel->findById($id);
            
            if (!$customer) {
                $this->errorResponse('Customer not found', 404);
            }
            
            $this->jsonResponse($customer);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store()
    {
        try {
            $data = $this->getRequestData();
            
            $required = ['name'];
            $missing = $this->validateRequired($data, $required);
            
            if (!empty($missing)) {
                $this->errorResponse('Missing required fields: ' . implode(', ', $missing), 422);
            }
            
            $customerId = $this->customerModel->create($data);
            $customer = $this->customerModel->findById($customerId);
            
            $this->jsonResponse($customer, 201);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function update($id)
    {
        try {
            $data = $this->getRequestData();
            
            $customer = $this->customerModel->findById($id);
            if (!$customer) {
                $this->errorResponse('Customer not found', 404);
            }
            
            $this->customerModel->updateCustomer($id, $data);
            $updatedCustomer = $this->customerModel->findById($id);
            
            $this->jsonResponse($updatedCustomer);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $customer = $this->customerModel->findById($id);
            if (!$customer) {
                $this->errorResponse('Customer not found', 404);
            }
            
            $this->customerModel->delete($id);
            $this->jsonResponse(['message' => 'Customer deleted successfully']);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function search()
    {
        try {
            $data = $this->getRequestData();
            
            if (!isset($data['name'])) {
                $this->errorResponse('Search term required', 422);
            }
            
            $customers = $this->customerModel->searchByName($data['name']);
            $this->jsonResponse($customers);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }
}
