# Resource Timesheet Management System (RTMS)

A production-ready Resource Timesheet Management System built with PHP 8.4, CodeIgniter 4 framework, Smarty templating engine, and MySQL database.

## ✅ Setup Status

- ✅ **PHP 8.4.16** - Latest version with MySQLi support
- ✅ **CodeIgniter 4.5.2** - Framework fully integrated
- ✅ **Smarty 5.5.1** - Template engine installed and configured
- ✅ **MySQL Driver** - MySQLi ready for database operations

## 🚀 Quick Start

1. **Configure Database** (`.env`):
   ```env
   database.default.hostname = localhost
   database.default.database = rtms
   database.default.username = root
   database.default.password = 
   ```

2. **Create Database**:
   ```sql
   CREATE DATABASE rtms CHARACTER SET utf8mb4;
   ```

3. **Run Migrations**:
   ```bash
   php spark migrate
   php spark db:seed UserSeeder
   ```

4. **Start Server**:
   ```bash
   php spark serve
   ```

5. **Access Application**: http://localhost:8080

## 📁 Project Structure

```
├── app/
│   ├── Config/
│   │   ├── Database.php      # Database configuration
│   │   ├── Routes.php        # Application routes
│   ├── Controllers/
│   │   └── Home.php          # Example controller
│   ├── Models/
│   │   └── UserModel.php     # User database model
│   ├── Libraries/
│   │   └── SmartyEngine.php  # Smarty integration
│   ├── Database/
│   │   ├── Migrations/
│   │   └── Seeds/
│   └── templates/            # Smarty templates (.tpl)
│       ├── home.tpl
│       ├── users.tpl
│       └── about.tpl
├── public/
│   └── index.php             # Entry point
├── writable/
│   ├── smarty_compile/
│   └── smarty_cache/
├── .env.example              # Configuration template
└── INSTALLATION.md           # Detailed setup guide
```

## 🎨 Using Smarty Templates

**Controller Example**:
```php
<?php 
use App\Libraries\SmartyEngine;

class Home extends BaseController {
    public function index() {
        $smarty = new SmartyEngine();
        return $smarty->render('home.tpl', [
            'title' => 'Welcome',
            'message' => 'Hello World'
        ]);
    }
}
```

**Template Example** (`app/templates/home.tpl`):
```smarty
<h1>{$title|escape}</h1>
<p>{$message|escape}</p>
{foreach $items as $item}
    <div>{$item|escape}</div>
{/foreach}
```

class Home extends BaseController
{
    protected $smarty;

    public function __construct()
    {
        $this->smarty = new SmartyTemplate();
    }

    public function index()
    {
        $this->smarty->assign('title', 'Hello World');
        return $this->smarty->render('home/index');
    }
}
```

### 4. MySQL Database Setup

**Option A: Install MySQL Community Server Manually**

1. Download MySQL Community Server from: https://dev.mysql.com/downloads/mysql/
2. Run the installer and follow the setup wizard
3. Remember your root password and port (default: 3306)

**Option B: Use MariaDB (MySQL-compatible alternative)**

1. Download from: https://mariadb.org/download/
2. Install using the MSI installer for Windows

**Option C: Use Docker (Recommended)**

```bash
docker run --name mysql-dev -e MYSQL_ROOT_PASSWORD=root -p 3306:3306 -d mysql:8.0
```

### 5. Database Configuration

After installing MySQL/MariaDB, update the `.env` file in the project root:

```env
database.default.hostname = localhost
database.default.database = rtms
database.default.username = root
database.default.password = your_password
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

### 6. Create Database and Tables

Using MySQL command line:

```bash
mysql -u root -p

CREATE DATABASE rtms;
USE rtms;

-- Create a sample users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Or use the CodeIgniter migration system:

```bash
php spark migrate
```

## Project Structure

```
php-codeigniter-smarty-mysql/
├── app/
│   ├── Controllers/          # Application controllers
│   ├── Libraries/
│   │   └── SmartyTemplate.php # Smarty wrapper library
│   ├── Models/               # Database models
│   └── Views/                # Smarty templates (.tpl files)
│       └── home/
│           ├── index.tpl
│           └── about.tpl
├── public/
│   ├── index.php             # Application entry point
│   ├── .htaccess
│   ├── css/
│   ├── js/
│   └── images/
├── writable/
│   ├── logs/                 # Application logs
│   └── smarty/
│       ├── compile/          # Compiled templates
│       └── cache/            # Cached templates
├── vendor/
│   └── smarty/               # Smarty library
├── system/                   # CodeIgniter framework
├── .env.example              # Environment template
├── spark                     # CLI commands
└── README.md
```

## Running the Project

### Using Built-in PHP Server (Development)

```bash
php spark serve
```

The application will be available at: `http://localhost:8080`

### Using Apache with Virtual Host

1. Update your `httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "C:\Users\Public\Documents\php-codeigniter-smarty-mysql\public"
    ServerName ci-smarty.local
    
    <Directory "C:\Users\Public\Documents\php-codeigniter-smarty-mysql\public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

2. Add to your `hosts` file:
```
127.0.0.1 ci-smarty.local
```

3. Restart Apache

## Creating Smarty Templates

**Example Template (app/Views/home/index.tpl):**

```smarty
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <h1>{$title}</h1>
    <p>Welcome, {$user_name|escape}!</p>
    
    {if $items}
        <ul>
        {foreach $items as $item}
            <li>{$item}</li>
        {/foreach}
        </ul>
    {else}
        <p>No items found.</p>
    {/if}
</body>
</html>
```

**Using in Controller:**

```php
$this->smarty->assign('title', 'Welcome');
$this->smarty->assign('user_name', 'John Doe');
$this->smarty->assign('items', ['Item 1', 'Item 2', 'Item 3']);
return $this->smarty->render('home/index');
```

## Useful CodeIgniter Commands

```bash
# Create a new controller
php spark make:controller UserController

# Create a new model
php spark make:model User

# Create a migration
php spark make:migration CreateUsersTable

# Run migrations
php spark migrate

# List all routes
php spark route:list
```

## Troubleshooting

### PHP Module Extensions

Ensure your PHP installation includes the required extensions:
- `php_pdo.dll` - Database abstraction
- `php_pdo_mysql.dll` - MySQL driver
- `php_curl.dll` - HTTP requests
- `php_mbstring.dll` - Multibyte string support
- `php_intl.dll` - Internationalization

To check installed extensions:
```bash
php -m
```

### Smarty Permission Issues

Ensure write permissions for:
- `writable/smarty/compile/`
- `writable/smarty/cache/`

```bash
# On Windows, adjust directory permissions through Properties
# On Linux/Mac:
chmod -R 755 writable/
```

### Database Connection Issues

1. Verify MySQL is running
2. Check `.env` database configuration
3. Test connection:

```php
$db = \Config\Database::connect();
if ($db->connID) {
    echo "Connected successfully!";
}
```

## Resources

- **CodeIgniter 4**: https://codeigniter.com/
- **Smarty Template Engine**: https://www.smarty.net/
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **PHP Official**: https://www.php.net/

## License

This project is open source and available under the MIT License.

## Support

For issues related to:
- **CodeIgniter**: https://forum.codeigniter.com/
- **Smarty**: https://www.smarty.net/discussion
- **PHP/MySQL**: https://stackoverflow.com/

---

**Last Updated**: February 19, 2026  
**PHP Version**: 8.4.16  
**CodeIgniter Version**: 4.4.6  
**Smarty Version**: 5.5.1
