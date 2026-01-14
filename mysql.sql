-- sql/schema.sql
CREATE DATABASE IF NOT EXISTS db;
USE db;

-- Customers table (pre-populated)
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    address TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Items table (pre-populated products/services)
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Invoices table
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_total DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    notes TEXT,
    status ENUM('draft', 'sent', 'paid', 'overdue') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT
);

-- Invoice items (line items)
CREATE TABLE invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    item_id INT,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    tax_rate DECIMAL(5,2) NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL
);

-- Insert sample data
INSERT INTO customers (name, email, address, phone) VALUES
('Acme Corporation', 'contact@acme.com', '123 Business Rd, New York, NY', '555-0101'),
('Globex Corp', 'sales@globex.com', '456 Industry Ave, Chicago, IL', '555-0102'),
('Stark Industries', 'tony@stark.com', '890 Innovation Blvd, Malibu, CA', '555-0103'),
('Wayne Enterprises', 'bruce@wayne.com', '1007 Mountain Dr, Gotham', '555-0104'),
('Cyberdyne Systems', 'info@cyberdyne.com', '543 Future St, Los Angeles, CA', '555-0105');

INSERT INTO items (code, description, unit_price, tax_rate) VALUES
('SRV-001', 'Web Development Services', 85.00, 10.00),
('SRV-002', 'Consulting Services', 120.00, 10.00),
('SRV-003', 'Technical Support (hourly)', 65.00, 10.00),
('PRD-001', 'Software License - Basic', 299.00, 8.00),
('PRD-002', 'Software License - Professional', 699.00, 8.00),
('PRD-003', 'Software License - Enterprise', 1299.00, 8.00),
('SRV-004', 'System Integration', 150.00, 10.00),
('PRD-004', 'Annual Maintenance Contract', 199.00, 8.00);