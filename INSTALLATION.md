# Installation and Setup Guide

## Overview

This document provides step-by-step instructions to complete the setup of the PHP CodeIgniter 4 + Smarty + MySQL project.

## What's Been Installed

✅ **PHP 8.4.16** - Located in `C:\php-8.4.16`
✅ **CodeIgniter 4.4.6** - Framework files integrated
✅ **Smarty 5.5.1** - Template engine installed in `vendor/smarty/`
⏳ **MySQL Server** - Needs manual installation (see below)

## Step 1: Install MySQL Server (Manual)

Since automatic download failed, follow these steps:

### Windows Installation

1. **Download MySQL**
   - Visit: https://dev.mysql.com/downloads/mysql/
   - Choose "Windows (x86, 64-bit), MSI Installer"
   - Download the latest 8.0.x or 8.1.x version

2. **Run the Installer**
   - Double-click the downloaded MSI file
   - Choose "Server only" or "Full setup" (your preference)
   - Follow the configuration wizard

3. **Configuration**
   - Choose "Standalone MySQL Server" for development
   - Port: 3306 (default)
   - MySQL Server Type: Development Machine
   - Connectivity: TCP/IP
   - Authentication: Use Strong Password Encryption

4. **Service Setup**
   - Configure MySQL as Windows Service
   - Service Name: `MySQL80` (or as specified)
   - Start at system startup (recommended for development)

5. **Initial Setup**
   - Create root account with secure password
   - Configure MySQL users
   - Apply configuration

### Alternative: Use MariaDB (Drop-in MySQL Replacement)

1. **Download MariaDB**
   - Visit: https://mariadb.org/download/
   - Choose Windows MSI installer
   - Version: 11.0+ or 10.6+

2. **Follow Similar Installation Steps**
   - Install as Windows Service
   - Remember the root password
   - Keep default port 3306

### Alternative: Use Docker

If you have Docker installed:

```bash
docker run --name mysql-dev `
  -e MYSQL_ROOT_PASSWORD=root123 `
  -e MYSQL_DATABASE=codeigniter_db `
  -p 3306:3306 `
  -d mysql:8.0
```

## Step 2: Configure Database Connection

1. **Edit `.env` file** in project root:

```bash
# Copy from .env.example if it exists
cp .env.example .env
```

2. **Update database settings in `.env`:**

```env
database.default.hostname = localhost
database.default.database = codeigniter_db
database.default.username = root
database.default.password = your_secure_password
database.default.DBDriver = MySQLi
database.default.port = 3306
```

## Step 3: Create Database and Tables

### Using MySQL Command Line

```bash
mysql -u root -p
```

Then execute:

```sql
CREATE DATABASE codeigniter_db;
USE codeigniter_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Using MySQL GUI Tools

- **MySQL Workbench**: Free tool from MySQL
- **DBeaver**: Universal database tool
- **HeidiSQL**: Lightweight MySQL GUI

## Step 4: Verify PHP Extensions

Run this command to check required extensions:

```bash
php -m
```

Required extensions:
- pdo
- pdo_mysql
- curl
- mbstring
- intl

If missing, edit `php.ini` and uncomment the necessary extension lines:

```ini
extension=pdo_mysql
extension=curl
extension=mbstring
extension=intl
```

Then restart any running servers.

## Step 5: Test the Project

### Using Built-in PHP Server (Recommended for Development)

1. **Navigate to project directory:**
```bash
cd C:\Users\Public\Documents\php-codeigniter-smarty-mysql
```

2. **Start the server:**
```bash
php spark serve
```

3. **Access your application:**
Open browser and go to: `http://localhost:8080`

You should see:
- The CodeIgniter + Smarty welcome page
- Home and About navigation links
- System information

### Testing Routes

Visit these URLs:
- `http://localhost:8080/` - Home page
- `http://localhost:8080/home/about` - About page

## Step 6: Create Your First Model

1. **Create Migration:**
```bash
php spark make:migration CreateProductsTable
```

2. **Edit created migration file in `app/Database/Migrations/`**

3. **Run migration:**
```bash
php spark migrate
```

## Step 7: Create Your First Controller

1. **Create controller:**
```bash
php spark make:controller ProductController
```

2. **Edit `app/Controllers/ProductController.php`:**

```php
<?php

namespace App\Controllers;

use App\Libraries\SmartyTemplate;

class ProductController extends BaseController
{
    protected $smarty;

    public function __construct()
    {
        $this->smarty = new SmartyTemplate();
    }

    public function index()
    {
        $products = [
            ['id' => 1, 'name' => 'Product 1', 'price' => 29.99],
            ['id' => 2, 'name' => 'Product 2', 'price' => 49.99],
            ['id' => 3, 'name' => 'Product 3', 'price' => 19.99],
        ];

        $this->smarty->assign([
            'title' => 'Products',
            'products' => $products,
        ]);

        return $this->smarty->render('products/index');
    }
}
```

3. **Create template** at `app/Views/products/index.tpl`:

```smarty
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
        </tr>
        {foreach $products as $product}
            <tr>
                <td>{$product.id}</td>
                <td>{$product.name}</td>
                <td>${$product.price}</td>
            </tr>
        {/foreach}
    </table>
</body>
</html>
```

## Common Issues and Solutions

### Issue: PHP not found in terminal

**Solution:** 
- Reopen terminal after PATH update
- Or use full path: `C:\php-8.4.16\php.exe -v`

### Issue: Smarty template not rendering

**Solution:**
- Check template file exists in `app/Views/`
- Ensure template name matches: `render('folder/template')`
- Check writable directory permissions on `writable/smarty/`

### Issue: Cannot connect to MySQL

**Solution:**
- Verify MySQL service is running (Services panel)
- Check credentials in `.env` file
- Test with: `mysql -u root -p` from command line

### Issue: "Unable to load the requested controller"

**Solution:**
- Check controller class name matches filename
- Verify namespace is correct: `App\Controllers\`
- Restart PHP development server

## Next Steps

1. Explore CodeIgniter documentation: https://codeigniter.com/user_guide/
2. Learn Smarty syntax: https://www.smarty.net/documentation
3. Create additional models and controllers
4. Set up proper authentication system
5. Implement database migrations for version control
6. Add validation rules to forms
7. Create API endpoints if needed

## Project Directory Permissions

Ensure these directories are writable:

```bash
# Windows - via Command Prompt (Run as Administrator):
icacls "C:\Users\Public\Documents\php-codeigniter-smarty-mysql\writable" /grant Users:F /T

# Or manually through Properties > Security > Edit
```

## Environment Configuration

The project includes an `.env.example` file. Copy and customize it:

```bash
cp .env.example .env
```

Important `.env` variables:

```env
CI_ENVIRONMENT = development or production
APP_TIMEZONE = UTC or your timezone
database.default.hostname = localhost
database.default.database = codeigniter_db
database.default.username = root
database.default.password = your_password
```

## Additional Resources

| Resource | URL |
|----------|-----|
| CodeIgniter Docs | https://codeigniter.com/user_guide/ |
| Smarty Manual | https://www.smarty.net/documentation |
| PHP Manual | https://www.php.net/manual/ |
| MySQL Reference | https://dev.mysql.com/doc/refman/8.0/en/ |

---

**Setup completed on:** February 19, 2026  
**System:** Windows with PHP 8.4.16, CodeIgniter 4.4.6, Smarty 5.5.1

For questions or issues, refer to the official documentation sites listed above.
