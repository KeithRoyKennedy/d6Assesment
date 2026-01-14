<?php

namespace Keith\D6assesment\Controllers;

use Keith\D6assesment\Models\Item;

class ItemController extends Controller
{
    private $itemModel;

    public function __construct()
    {
        parent::__construct();
        $this->itemModel = new Item();
    }

    public function index()
    {
        try {
            $items = $this->itemModel->findAll();
            $this->jsonResponse($items);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show($id)
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

    public function store()
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

    public function update($id)
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

    public function destroy($id)
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

    public function search()
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
