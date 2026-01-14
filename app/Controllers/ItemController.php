<?php

/**
 * Item Controller
 *
 * Handles all item-related API endpoints including CRUD operations,
 * search functionality, and item code validation.
 *
 * @package Keith\D6assesment\Controllers
 */

namespace Keith\D6assesment\Controllers;

use Keith\D6assesment\Models\Item;

/**
 * Item controller class
 */
class ItemController extends Controller
{
    /**
     * Item model instance
     *
     * @var Item
     */
    private $itemModel;

    /**
     * Constructor - initializes item model
     */
    public function __construct()
    {
        parent::__construct();
        $this->itemModel = new Item();
    }

    /**
     * Get all items
     *
     * @return void
     */
    public function index(): void
    {
        try {
            $items = $this->itemModel->findAll();
            $this->jsonResponse($items);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get a single item by ID
     *
     * @param int $id Item ID
     * @return void
     */
    public function show(int $id): void
    {
        try {
            $item = $this->itemModel->findById($id);

            if (!$item) {
                $this->errorResponse('Item not found', 404);
            }

            $this->jsonResponse($item);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Create a new item
     *
     * Required fields: code, description, unit_price
     * Optional fields: tax_rate
     *
     * @return void
     */
    public function store(): void
    {
        try {
            $data = $this->getRequestData();

            $required = ['code', 'description', 'unit_price'];
            $missing = $this->validateRequired($data, $required);

            if (!empty($missing)) {
                $this->errorResponse('Missing required fields: ' . implode(', ', $missing), 422);
            }

            $existingItem = $this->itemModel->findByCode($data['code']);
            if ($existingItem) {
                $this->errorResponse('Item code already exists', 422);
            }

            $itemId = $this->itemModel->create($data);
            $item = $this->itemModel->findById($itemId);

            $this->jsonResponse($item, 201);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update an existing item
     *
     * Validates that item code is unique if changed
     *
     * @param int $id Item ID
     * @return void
     */
    public function update(int $id): void
    {
        try {
            $data = $this->getRequestData();

            $item = $this->itemModel->findById($id);
            if (!$item) {
                $this->errorResponse('Item not found', 404);
            }

            if (isset($data['code']) && $data['code'] !== $item['code']) {
                $existingItem = $this->itemModel->findByCode($data['code']);
                if ($existingItem) {
                    $this->errorResponse('Item code already exists', 422);
                }
            }

            $this->itemModel->updateItem($id, $data);
            $updatedItem = $this->itemModel->findById($id);

            $this->jsonResponse($updatedItem);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete an item
     *
     * @param int $id Item ID
     * @return void
     */
    public function destroy(int $id): void
    {
        try {
            $item = $this->itemModel->findById($id);
            if (!$item) {
                $this->errorResponse('Item not found', 404);
            }

            $this->itemModel->delete($id);
            $this->jsonResponse(['message' => 'Item deleted successfully']);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Search items by description
     *
     * Required fields: description (search term)
     *
     * @return void
     */
    public function search(): void
    {
        try {
            $data = $this->getRequestData();

            if (!isset($data['description'])) {
                $this->errorResponse('Search term required', 422);
            }

            $items = $this->itemModel->searchByDescription($data['description']);
            $this->jsonResponse($items);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }
}
