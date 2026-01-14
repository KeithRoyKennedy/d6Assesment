<?php

/**
 * Application Class
 *
 * Main application class that bootstraps the Invoice Capture System.
 * Configures routing, initializes database connection, and handles request processing.
 *
 * @package Keith\D6assesment
 */

namespace Keith\D6assesment;

use Keith\D6assesment\Controllers\InvoiceController;
use Keith\D6assesment\Controllers\CustomerController;
use Keith\D6assesment\Controllers\ItemController;

/**
 * Main application class
 */
class App
{
    /**
     * Router instance for handling HTTP requests
     *
     * @var Router
     */
    private $router;

    /**
     * Constructor - initializes router and sets up routes
     */
    public function __construct()
    {
        $this->router = new Router();
        $this->setupRoutes();
    }

    /**
     * Configure all application routes
     *
     * Defines routes for:
     * - Frontend view (/)
     * - Invoice API endpoints (/api/invoices/*)
     * - Customer API endpoints (/api/customers/*)
     * - Item API endpoints (/api/items/*)
     * - 404 not found handler
     *
     * @return void
     */
    private function setupRoutes(): void
    {
        $this->router->get('/', function () {
            require_once __DIR__ . '/../app/Views/invoice.php';
        });

        $this->router->get('/api/invoices', [InvoiceController::class, 'index']);
        $this->router->get('/api/invoices/{id}', [InvoiceController::class, 'show']);
        $this->router->post('/api/invoices', [InvoiceController::class, 'store']);
        $this->router->put('/api/invoices/{id}', [InvoiceController::class, 'update']);
        $this->router->delete('/api/invoices/{id}', [InvoiceController::class, 'destroy']);
        $this->router->post('/api/invoices/{id}/status', [InvoiceController::class, 'updateStatus']);
        $this->router->get('/api/invoices/next-number/generate', [InvoiceController::class, 'getNextInvoiceNumber']);

        $this->router->get('/api/customers', [CustomerController::class, 'index']);
        $this->router->get('/api/customers/{id}', [CustomerController::class, 'show']);
        $this->router->post('/api/customers', [CustomerController::class, 'store']);
        $this->router->put('/api/customers/{id}', [CustomerController::class, 'update']);
        $this->router->delete('/api/customers/{id}', [CustomerController::class, 'destroy']);
        $this->router->post('/api/customers/search', [CustomerController::class, 'search']);

        $this->router->get('/api/items', [ItemController::class, 'index']);
        $this->router->get('/api/items/{id}', [ItemController::class, 'show']);
        $this->router->post('/api/items', [ItemController::class, 'store']);
        $this->router->put('/api/items/{id}', [ItemController::class, 'update']);
        $this->router->delete('/api/items/{id}', [ItemController::class, 'destroy']);
        $this->router->post('/api/items/search', [ItemController::class, 'search']);

        $this->router->setNotFound(function () {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Endpoint not found']);
        });
    }

    /**
     * Run the application
     *
     * Initializes database connection and processes the current HTTP request.
     * Handles exceptions and returns appropriate error responses.
     *
     * @return void
     */
    public function run(): void
    {
        try {
            $db = Database::getInstance();
            $this->router->run();
        } catch (\PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
