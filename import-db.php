<?php
// Import Database Script for XAMPP
require_once __DIR__ . '/config/db.php';

echo "=== LPPM FDK - Import Database ===\n\n";

try {
    // Create database if not exists
    echo "1. Creating database 'suratlppm'...\n";
    $stmt = db()->query("CREATE DATABASE IF NOT EXISTS suratlppm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✓ Database created!\n\n";

    // Use the new database
    db()->query("USE suratlppm");

    // Read and execute SQL file
    $sqlFile = __DIR__ . '/database.sql';
    
    if (!file_exists($sqlFile)) {
        echo "❌ Error: SQL file not found!\n";
        exit;
    }

    $sqlContent = file_get_contents($sqlFile);
    echo "2. Importing schema from database.sql...\n";
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        try {
            db()->query($statement);
            echo "   ✓ Executed: " . basename(str_replace(__DIR__ . '/', '', $sqlFile)) . "\n";
        } catch(Exception $e) {
            // Ignore errors for some statements (like INSERT with auto_increment)
            if (!preg_match('/Unknown column|Duplicate entry/', $e->getMessage())) {
                echo "   ⚠ Warning: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n3. Database import completed!\n";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Import Finished ===\n";
echo "Database 'suratlppm' is ready at localhost\n";
echo "Default admin credentials:\n";
echo "  Email: admin@suratlppm.fdk.ac.id\n";
echo "  Password: admin123\n";
