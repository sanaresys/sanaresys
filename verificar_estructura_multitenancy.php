<?php

echo "=== VERIFICACIÓN DE ESTRUCTURA MULTI-TENANT ===" . PHP_EOL . PHP_EOL;

$connection = new mysqli('localhost', 'root', '', '');

if ($connection->connect_error) {
    die("Error de conexión: " . $connection->connect_error);
}

// 1. Verificar BD Central
echo "1. BASE DE DATOS CENTRAL (db_clinica)" . PHP_EOL;
$connection->select_db('db_clinica');

$tablesResult = $connection->query("SHOW TABLES");
$centralTables = $tablesResult->num_rows;
echo "   ✓ Tablas: $centralTables" . PHP_EOL;

$centrosResult = $connection->query("SELECT COUNT(*) as count FROM centros_medicos");
$centrosCount = $centrosResult->fetch_assoc()['count'];
echo "   ✓ Centros médicos: $centrosCount" . PHP_EOL;

$tenantsResult = $connection->query("SELECT id FROM tenants");
$tenants = [];
while ($row = $tenantsResult->fetch_assoc()) {
    $tenants[] = $row['id'];
}
echo "   ✓ Tenants registrados: " . implode(", ", $tenants) . PHP_EOL;

$usersResult = $connection->query("SELECT COUNT(*) as count FROM users");
$usersCount = $usersResult->fetch_assoc()['count'];
echo "   ✓ Usuarios en central: $usersCount" . PHP_EOL;

echo PHP_EOL;

// 2. Verificar cada tenant
echo "2. BASES DE DATOS TENANT" . PHP_EOL;
$tenantDBs = $connection->query("SHOW DATABASES LIKE 'centro_%'");

while ($dbRow = $tenantDBs->fetch_row()) {
    $dbName = $dbRow[0];
    echo "   Tenant: $dbName" . PHP_EOL;
    
    $connection->select_db($dbName);
    
    // Contar tablas
    $tablesResult = $connection->query("SHOW TABLES");
    $tableCount = $tablesResult->num_rows;
    echo "   ├─ Tablas: $tableCount" . PHP_EOL;
    
    // Verificar usuarios
    $usersResult = $connection->query("SELECT COUNT(*) as count FROM users");
    $usersCount = $usersResult->fetch_assoc()['count'];
    echo "   ├─ Usuarios: $usersCount" . PHP_EOL;
    
    // Verificar médicos
    $medicosResult = $connection->query("SELECT COUNT(*) as count FROM medicos");
    $medicosCount = $medicosResult->fetch_assoc()['count'];
    echo "   ├─ Médicos: $medicosCount" . PHP_EOL;
    
    // Verificar FK a centros_medicos (NO debe existir)
    $fkResult = $connection->query("
        SELECT 
            TABLE_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME
        FROM 
            information_schema.KEY_COLUMN_USAGE
        WHERE 
            TABLE_SCHEMA = '$dbName'
            AND REFERENCED_TABLE_NAME = 'centros_medicos'
    ");
    
    $fkCount = $fkResult->num_rows;
    if ($fkCount > 0) {
        echo "   ❌ ADVERTENCIA: $fkCount FK a centros_medicos encontradas" . PHP_EOL;
        while ($fk = $fkResult->fetch_assoc()) {
            echo "      └─ " . $fk['TABLE_NAME'] . " -> " . $fk['CONSTRAINT_NAME'] . PHP_EOL;
        }
    } else {
        echo "   ✓ Sin FK a centros_medicos (correcto)" . PHP_EOL;
    }
    
    echo PHP_EOL;
}

echo "=== VERIFICACIÓN COMPLETADA ===" . PHP_EOL;

$connection->close();
