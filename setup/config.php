<?php
// Configuración de conexión a la base de datos con PDO

$host = '3.80.48.215';
$dbname = 'pagina'; // Cambia esto por el nombre real de tu base de datos
$db_user = 'root';           // Usuario por defecto en localhost sin contraseña
$db_password = 'admin123';           // Contraseña vacía para localhost sin contraseña

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

function conectar() {
    global $host, $dbname, $db_user, $db_password, $options;
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    error_log("DSN en conectar(): " . var_export($dsn, true));
    try {
        if (empty($dsn)) {
            throw new PDOException("DSN está vacío o nulo");
        }
        return new PDO($dsn, $db_user, $db_password, $options);
    } catch (PDOException $e) {
        error_log("Error en la conexión a la base de datos: " . $e->getMessage());
        die("Error en la conexión a la base de datos: " . $e->getMessage());
    }
}
?>
