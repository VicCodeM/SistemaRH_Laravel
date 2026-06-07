<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$databases = [];
foreach ($pdo->query("SHOW DATABASES") as $row) {
    $name = $row[0];
    if (preg_match('/rh|sistema/i', $name)) {
        $databases[] = $name;
    }
}
foreach ($databases as $db) {
    try {
        $pdoDb = new PDO("mysql:host=127.0.0.1;port=3306;dbname={$db}", 'root', '');
        $tables = $pdoDb->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $hasCore = in_array('users', $tables, true) && in_array('empresas', $tables, true) && in_array('vacantes', $tables, true);
        if (! $hasCore) {
            continue;
        }
        $users = $pdoDb->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $empresas = $pdoDb->query('SELECT COUNT(*) FROM empresas')->fetchColumn();
        $candidatos = $pdoDb->query('SELECT COUNT(*) FROM candidatos')->fetchColumn();
        $vacantes = $pdoDb->query('SELECT COUNT(*) FROM vacantes')->fetchColumn();
        echo $db, '|users=', $users, '|empresas=', $empresas, '|candidatos=', $candidatos, '|vacantes=', $vacantes, PHP_EOL;
    } catch (Throwable $e) {
        echo $db, '|ERROR|', $e->getMessage(), PHP_EOL;
    }
}
