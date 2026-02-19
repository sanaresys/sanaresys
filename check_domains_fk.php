<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FOREIGN KEYS EN TABLA DOMAINS ===\n\n";

$foreignKeys = DB::select("
    SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM
        INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE
        TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'domains'
        AND REFERENCED_TABLE_NAME IS NOT NULL
");

if (empty($foreignKeys)) {
    echo "No hay foreign keys en la tabla domains\n";
} else {
    foreach($foreignKeys as $fk) {
        echo "Constraint: {$fk->CONSTRAINT_NAME}\n";
        echo "  Column: {$fk->COLUMN_NAME}\n";
        echo "  References: {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
        echo "\n";
    }
}
