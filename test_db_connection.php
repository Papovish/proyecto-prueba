<?php
include 'setup/config.php';

try {
    $con = conectar();
    echo "Conexión a la base de datos exitosa.";
} catch (Exception $e) {
    echo "Error en la conexión a la base de datos: " . $e->getMessage();
}
?>
