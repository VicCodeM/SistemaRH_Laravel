<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=sistemarh2', 'root', '');
$rows = $pdo->query("SELECT nombre_empresa, estado FROM empresas ORDER BY id LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    echo implode('|', $row), PHP_EOL;
}
