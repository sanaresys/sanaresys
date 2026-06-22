<?php

echo "=== VERIFICANDO BASES DE DATOS TENANT ===" . PHP_EOL . PHP_EOL;

$connection = new mysqli('localhost', 'root', '', '');

if ($connection->connect_error) {
    die("Error de conexión: " . $connection->connect_error);
}

// Verificar bases de datos tenant
$result = $connection->query("SHOW DATABASES LIKE 'centro_%'");
$databases = [];

while ($row = $result->fetch_row()) {
    $databases[] = $row[0];
}

if (empty($databases)) {
    echo "❌ No se encontraron bases de datos tenant" . PHP_EOL;
    exit(1);
}

foreach ($databases as $dbName) {
    echo "✓ Base de datos: $dbName" . PHP_EOL;
    
    // Contar tablas en esta base de datos
    $connection->select_db($dbName);
    $tablesResult = $connection->query("SHOW TABLES");
    $tableCount = $tablesResult->num_rows;
    
    echo "  └─ Tablas: $tableCount" . PHP_EOL;
    
    // Verificar migrations
    $migrationsResult = $connection->query("SELECT COUNT(*) as count FROM migrations");
    if ($migrationsResult) {
        $migrationCount = $migrationsResult->fetch_assoc()['count'];
        echo "  └─ Migraciones ejecutadas: $migrationCount" . PHP_EOL;
    }
    
    echo PHP_EOL;
}

$connection->close();

echo "✓ Verificación completada" . PHP_EOL;
