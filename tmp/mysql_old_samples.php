<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=sistemarh2', 'root', '');
$queries = [
    "SELECT id,name,email,role FROM users ORDER BY id LIMIT 10",
    "SELECT id,nombre_comercial,razon_social,estado FROM empresas ORDER BY id LIMIT 10",
    "SELECT id,nombre,apellidos,email,estado,solicitud_estado FROM candidatos ORDER BY id LIMIT 10",
    "SELECT id,titulo,empresa_id,estado,nivel_jerarquico FROM vacantes ORDER BY id LIMIT 10"
];
foreach ($queries as $sql) {
    echo "SQL {$sql}", PHP_EOL;
    try {
        foreach ($pdo->query($sql) as $row) {
            echo json_encode($row, JSON_UNESCAPED_UNICODE), PHP_EOL;
        }
    } catch (Throwable $e) {
        echo 'ERROR|', $e->getMessage(), PHP_EOL;
    }
    echo str_repeat('-', 40), PHP_EOL;
}
