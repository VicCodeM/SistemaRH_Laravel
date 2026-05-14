<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$pdo->exec('CREATE DATABASE IF NOT EXISTS sistemarh_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
echo "Base de datos creada exitosamente.\n";
