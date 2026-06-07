<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=sistemarh2', 'root', '');
foreach (['users', 'empresas', 'candidatos', 'vacantes', 'catalogo_servicios', 'servicios_asignados'] as $table) {
    echo "TABLE {$table}", PHP_EOL;
    try {
        foreach ($pdo->query("SHOW COLUMNS FROM {$table}") as $row) {
            echo $row['Field'], '|', $row['Type'], PHP_EOL;
        }
    } catch (Throwable $e) {
        echo 'ERROR|', $e->getMessage(), PHP_EOL;
    }
    echo str_repeat('-', 40), PHP_EOL;
}
