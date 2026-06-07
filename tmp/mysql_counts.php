<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=sistemarh_laravel', 'root', '');
$tables = ['users', 'empresas', 'candidatos', 'vacantes', 'catalogo_servicios'];
foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    echo $table . ':' . $count . PHP_EOL;
}
