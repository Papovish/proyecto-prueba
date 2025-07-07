<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'setup/config.php';

$con = conectar();

$usuario = strtolower(trim($_POST['Usuario']));
$clave = $_POST['clave'];

// Buscar usuario por nombre de usuario
$sql = "SELECT * FROM usuarios WHERE LOWER(usuario) = ?";
$stmt = $con->prepare($sql);
$stmt->bindValue(1, $usuario, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($result) === 1) {
    $datos = $result[0];
    $tipo = strtolower(trim($datos['tipo']));
    $hash_clave = $datos['clave'];
    $estado = strtolower(trim($datos['estado']));

    // Verificar contraseña con md5 para todos los usuarios
    $password_correct = (md5($clave) === $hash_clave);

    // Debug output
    error_log("Login attempt: usuario={$usuario}, tipo={$tipo}, estado={$estado}, password_correct=" . ($password_correct ? "true" : "false"));

    if ($password_correct && $estado === 'activo') {
        session_start();
        $_SESSION['usuario'] = $datos['nombres'];
        $_SESSION['tipo'] = $tipo;

        if ($tipo === 'gestor') {
            header("Location:dashboard.php");
        } elseif ($tipo === 'propietario') {
            header("Location:dashboard.php");
        } else {
            header("Location:login.html?error=1");
        }
        exit();
    } else {
        header("Location:login.html?error=1");
        exit();
    }
} else {
    header("Location:login.html?error=1");
    exit();
}




