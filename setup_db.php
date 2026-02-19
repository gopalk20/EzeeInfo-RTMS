<?php
$dbFile = 'C:\Users\Public\Documents\php-codeigniter-smarty-mysql\writable\database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    
    // Create migrations table
    $pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY,
        version INTEGER NOT NULL,
        class TEXT NOT NULL,
        "group" TEXT NOT NULL,
        namespace TEXT NOT NULL,
        time INTEGER NOT NULL,
        batch INTEGER NOT NULL
    )');
    
    // Create users table
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Insert sample users
    $users = [
        ['Admin User', 'admin@example.com', 'password123'],
        ['Test User', 'test@example.com', 'test123'],
        ['Demo User', 'demo@example.com', 'demo123']
    ];
    
    foreach ($users as $user) {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $result = $stmt->execute($user);
            if ($result) echo "Inserted: {$user[0]} ({$user[1]})" . PHP_EOL;
            else echo "Failed to insert: {$user[0]}" . PHP_EOL;
        } catch (Exception $e) {
            echo "Error inserting {$user[0]}: " . $e->getMessage() . PHP_EOL;
        }
    }
    
    echo 'Database created successfully!' . PHP_EOL;
    echo 'Database file: ' . $dbFile . PHP_EOL;
    
    // Verify data
    $result = $pdo->query('SELECT COUNT(*) as count FROM users');
    $row = $result->fetch(PDO::FETCH_ASSOC);
    echo 'Users in database: ' . $row['count'] . PHP_EOL;
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>
