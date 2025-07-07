<?php
include 'setup/config.php';

$con = conectar();

$sql = "SELECT usuario, tipo, clave FROM usuarios";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Usuarios registrados:</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Usuario</th><th>Tipo</th><th>Clave (hash)</th><th>Tipo limpio</th><th>Clave bcrypt?</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $usuario = htmlspecialchars($row['usuario']);
        $tipo = $row['tipo'];
        $tipo_limpio = trim($tipo);
        $clave = $row['clave'];
        $es_bcrypt = (substr($clave, 0, 4) === '$2y$' || substr($clave, 0, 4) === '$2a$') ? 'Sí' : 'No';
        echo "<tr>";
        echo "<td>$usuario</td>";
        echo "<td>$tipo</td>";
        echo "<td>$clave</td>";
        echo "<td>$tipo_limpio</td>";
        echo "<td>$es_bcrypt</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No se encontraron usuarios.";
}

$con->close();
?>
