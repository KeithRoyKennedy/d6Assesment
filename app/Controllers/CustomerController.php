<?php

/**
 * Customer Controller
 *
 * Handles all customer-related API endpoints including CRUD operations
 * and search functionality.
 *
 * @package Keith\D6assesment\Controllers
 */

namespace Keith\D6assesment\Controllers;

use Keith\D6assesment\Models\Customer;

/**
 * Customer controller class
 */
class CustomerController extends Controller
{
    /**
     * Customer model instance
     *
     * @var Customer
     */
    private $customerModel;

    /**
     * Constructor - initializes customer model
     */
    public function __construct()
    {
        parent::__construct();
        $this->customerModel = new Customer();
    }

    /**
     * Get all customers
     *
     * @return void
     */
    public function index(): void
    {
        try {
            $customers = $this->customerModel->findAll();
            $this->jsonResponse($customers);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get a single customer by ID
     *
     * @param int $id Customer ID
     * @return void
     */
    public function show($id): void
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

    /**
     * Create a new customer
     *
     * Required fields: name
     * Optional fields: email, phone, address
     *
     * @return void
     */
    public function store(): void
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

    /**
     * Update an existing customer
     *
     * @param int $id Customer ID
     * @return void
     */
    public function update(int $id): void
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

    /**
     * Delete a customer
     *
     * @param int $id Customer ID
     * @return void
     */
    public function destroy(int $id): void
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

    /**
     * Search customers by name
     *
     * Required fields: name (search term)
     *
     * @return void
     */
    public function search(): void
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
