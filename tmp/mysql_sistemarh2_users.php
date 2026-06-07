<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=sistemarh2', 'root', '');
$admin = $pdo->query("SELECT email, rol, estado FROM users ORDER BY id LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($admin as $row) {
    echo implode('|', $row), PHP_EOL;
}
