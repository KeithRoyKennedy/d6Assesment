# d6Assessment - Invoice Capture System

A modern invoice capture UI and API built with PHP, following MVC architecture without frameworks or ORMs.

## Features

- **RESTful API** - Complete CRUD operations for invoices, customers, and items
- **Modern UI** - Responsive JavaScript frontend with real-time calculations
- **MVC Architecture** - Clean separation of concerns
- **Plain SQL** - No ORM, direct PDO queries for transparency
- **Transaction Support** - Ensures data integrity for complex operations
- **Input Validation** - Server-side validation and error handling

## Project Structure

```
/public          - Front controller and assets (CSS, JS)
  /assets
    /css         - Stylesheets
    /js          - JavaScript application
  index.php      - Entry point
  .htaccess      - URL rewriting rules

/app             - Core application files
  /Controllers   - Request handlers
  /Models        - Database interaction classes
  /Views         - Template files
  App.php        - Application bootstrap
  Router.php     - Routing system
  Database.php   - PDO singleton connection

/config          - Configuration files
  config.php     - Database and app configuration

/vendor          - Composer dependencies (auto-managed)
composer.json    - Project dependencies
.env             - Environment variables (not in git)
mysql.sql        - Database schema and seed data
```

## Requirements

- PHP 8.3+
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Web server (Apache/Nginx) or DDEV

## Installation

### Option 1: DDEV Setup (Recommended for Development)

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd d6Assesment
   ```

2. **Start DDEV**
   ```bash
   ddev start
   ```

3. **Install dependencies**
   ```bash
   ddev composer install
   ```

4. **Configure environment**
   
   The `.env` file is already configured for DDEV:
   ```env
   DB_HOST=db
   DB_NAME=db
   DB_USER=db
   DB_PASS=db
   ```

5. **Import the database**
   ```bash
   ddev import-db --src=mysql.sql
   ```

6. **Access the application**
   
   Open your browser to: `https://d6assesment.ddev.site`

7. **Access phpMyAdmin (via DDEV)**
   ```bash
   ddev launch -p
   ```
   Or visit: `https://d6assesment.ddev.site:8037`

### Option 2: Standard PHP Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd d6Assesment
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure your web server**
   
   Point your document root to the `/public` directory.
   
   **Apache Example (`httpd.conf` or virtual host):**
   ```apache
   <VirtualHost *:80>
       ServerName invoice.local
       DocumentRoot /path/to/d6Assesment/public
       
       <Directory /path/to/d6Assesment/public>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
   
   **Nginx Example:**
   ```nginx
   server {
       listen 80;
       server_name invoice.local;
       root /path/to/d6Assesment/public;
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

4. **Configure environment variables**
   
   Copy and edit the `.env` file:
   ```bash
   cp .env.example .env  # If .env doesn't exist
   ```
   
   Update with your database credentials:
   ```env
   DB_HOST=localhost
   DB_NAME=invoice_system
   DB_USER=your_db_user
   DB_PASS=your_db_password
   ```

5. **Create and import the database**
   
   **Using MySQL command line:**
   ```bash
   mysql -u root -p < mysql.sql
   ```
   
   **Using phpMyAdmin:**
   - Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
   - Create a new database named `invoice_system` (or your preferred name)
   - Click on the database
   - Go to the "Import" tab
   - Choose the `mysql.sql` file
   - Click "Go" to import

6. **Set permissions**
   ```bash
   chmod -R 755 /path/to/d6Assesment
   chmod -R 775 /path/to/d6Assesment/vendor
   ```

7. **Access the application**
   
   Open your browser to: `http://invoice.local` (or your configured domain)

## Database Schema

The application uses the following tables:

- **customers** - Customer information (pre-populated with sample data)
- **items** - Products/services catalog (pre-populated)
- **invoices** - Invoice headers
- **invoice_items** - Invoice line items

## API Endpoints

### Invoices
- `GET /api/invoices` - List all invoices
- `GET /api/invoices/{id}` - Get invoice details
- `POST /api/invoices` - Create new invoice
- `PUT /api/invoices/{id}` - Update invoice
- `DELETE /api/invoices/{id}` - Delete invoice
- `POST /api/invoices/{id}/status` - Update invoice status
- `GET /api/invoices/next-number/generate` - Get next invoice number

### Customers
- `GET /api/customers` - List all customers
- `GET /api/customers/{id}` - Get customer details
- `POST /api/customers` - Create new customer
- `PUT /api/customers/{id}` - Update customer
- `DELETE /api/customers/{id}` - Delete customer
- `POST /api/customers/search` - Search customers by name

### Items
- `GET /api/items` - List all items
- `GET /api/items/{id}` - Get item details
- `POST /api/items` - Create new item
- `PUT /api/items/{id}` - Update item
- `DELETE /api/items/{id}` - Delete item
- `POST /api/items/search` - Search items by description

## Development

### Running with DDEV
```bash
ddev start          # Start the environment
ddev stop           # Stop the environment
ddev restart        # Restart services
ddev ssh            # SSH into the container
ddev logs           # View logs
ddev describe       # Show project info
```

### Composer Commands
```bash
ddev composer install       # Install dependencies
ddev composer update        # Update dependencies
ddev composer dump-autoload # Regenerate autoload files
```

### Database Management
```bash
# DDEV
ddev import-db --src=mysql.sql    # Import database
ddev export-db --file=backup.sql  # Export database
ddev mysql                        # Access MySQL CLI

# Standard PHP
mysql -u root -p < mysql.sql      # Import
mysqldump -u root -p db_name > backup.sql  # Export
```

## Troubleshooting

### Database Connection Issues
- Verify `.env` credentials are correct
- Ensure MySQL service is running
- Check if database exists and is accessible

### 404 Errors
- Verify `.htaccess` is in `/public` directory
- Ensure `mod_rewrite` is enabled (Apache)
- Check web server document root points to `/public`

### Composer Autoload Issues
```bash
composer dump-autoload
# or with DDEV
ddev composer dump-autoload
```

### Permission Errors
```bash
chmod -R 755 /path/to/d6Assesment
chown -R www-data:www-data /path/to/d6Assesment  # Linux
```

## Security Notes

- The `.env` file is excluded from git (see `.gitignore`)
- Never commit database credentials
- Use prepared statements for all SQL queries (already implemented)
- Input validation is performed on all endpoints
- CSRF protection should be added for production use

## License

MIT

## Author

Keith Kennedy - keithkennedy5@gmail.com
