<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'setup/config.php';


// procesa_propietario.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir y sanitizar datos
    $rut = trim($_POST['rut']);
    $nombres = trim($_POST['nombre']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $usuario = trim($_POST['correo']);
    $clave = $_POST['clave'];
    $sexo = trim($_POST['sexo']);
    $telefono = trim($_POST['telefono']);
    $num_propiedad = trim($_POST['num_propiedad']);
    $num_propiedad_int = (int)$num_propiedad;

    // Validar campos obligatorios
    if (
        empty($rut) || empty($nombres) || empty($fecha_nacimiento) || empty($usuario) ||
        empty($clave) || empty($sexo) || empty($telefono) || empty($num_propiedad)
    ) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor complete todos los campos.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = 'propietario_form.php';
            });
        </script>";
        exit;
    }

    // Validar que el nombre no contenga números
    if (preg_match('/\d/', $nombres)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Nombre inválido',
                text: 'El nombre no puede contener números.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = 'propietario_form.php';
            });
        </script>";
        exit;
    }

    // Validar que la fecha de nacimiento no sea futura
    $fecha_hoy = date('Y-m-d');
    if ($fecha_nacimiento > $fecha_hoy) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Fecha inválida',
                text: 'La fecha de nacimiento no puede ser una fecha futura.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = 'propietario_form.php';
            });
        </script>";
        exit;
    }


// Hashear la contraseña con MD5
$hash_clave = md5($clave);

    // Insertar en la base de datos
    $con = conectar();

$stmt = $con->prepare("INSERT INTO usuarios (rut, nombres, fecha_nacimiento, usuario, clave, sexo, telefono, num_propiedad, tipo, estado, archivo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $con->error);
}
$tipo = "propietario";
$estado = "activo";
$archivo = "";
$stmt->bindValue(1, $rut, PDO::PARAM_STR);
$stmt->bindValue(2, $nombres, PDO::PARAM_STR);
$stmt->bindValue(3, $fecha_nacimiento, PDO::PARAM_STR);
$stmt->bindValue(4, $usuario, PDO::PARAM_STR);
$stmt->bindValue(5, $hash_clave, PDO::PARAM_STR);
$stmt->bindValue(6, $sexo, PDO::PARAM_STR);
$stmt->bindValue(7, $telefono, PDO::PARAM_STR);
$stmt->bindValue(8, $num_propiedad_int, PDO::PARAM_INT);
$stmt->bindValue(9, $tipo, PDO::PARAM_STR);
$stmt->bindValue(10, $estado, PDO::PARAM_STR);
$stmt->bindValue(11, $archivo, PDO::PARAM_STR);

if ($stmt->execute()) {
    // Redirigir inmediatamente a login.html después de registro exitoso
    ob_clean();
    header("Location: login.html");
    exit();
} else {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un error al registrar el dueño de inmueble. Intente nuevamente.',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.location.href = 'propietario_form.php';
        });
    </script>";
    exit;
}

$stmt->close();
$con->close();
} else {
    header("Location: propietario_form.php");
    exit;
}
ob_end_flush();
?>
