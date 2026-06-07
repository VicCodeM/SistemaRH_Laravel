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
        if (! in_array('users', $tables, true) || ! in_array('empresas', $tables, true)) {
            continue;
        }
        $userCols = $pdoDb->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
        $empresaCols = $pdoDb->query('SHOW COLUMNS FROM empresas')->fetchAll(PDO::FETCH_COLUMN);
        $matches = in_array('rol', $userCols, true) && in_array('estado', $userCols, true) && in_array('nombre_empresa', $empresaCols, true);
        echo $db, '|match=', $matches ? 'yes' : 'no', '|user_cols=', implode(',', array_slice($userCols, 0, 8)), '|empresa_cols=', implode(',', array_slice($empresaCols, 0, 8)), PHP_EOL;
    } catch (Throwable $e) {
        echo $db, '|ERROR|', $e->getMessage(), PHP_EOL;
    }
}
