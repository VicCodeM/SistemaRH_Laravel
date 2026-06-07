<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
echo $pdo->query('SELECT @@datadir')->fetchColumn(), PHP_EOL;
