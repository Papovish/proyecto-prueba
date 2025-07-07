<?php
include 'setup/config.php';

// procesa_usuario.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir y sanitizar datos
    $rol = trim($_POST['rol']);
    $rut = trim($_POST['rut']);
    $nombres = trim($_POST['nombre']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $usuario = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    $sexo = trim($_POST['sexo']);
    $telefono = trim($_POST['telefono']);
    $num_propiedad = trim($_POST['num_propiedad']);

    // Validar campos obligatorios
    if (
        empty($rol) || empty($rut) || empty($nombres) || empty($fecha_nacimiento) || empty($usuario) ||
        empty($contrasena) || empty($sexo) || empty($telefono) ||
        ($rol === 'propietario' && empty($num_propiedad))
    ) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor complete todos los campos obligatorios.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit;
    }

    // Hashear la contraseña con bcrypt
    $hash_contrasena = password_hash($contrasena, PASSWORD_BCRYPT);

    // Insertar en la base de datos
    $con = conectar();

    // Preparar consulta para evitar inyección SQL
    $stmt = $con->prepare("INSERT INTO usuarios (rut, nombres, fecha_nacimiento, usuario, clave, sexo, telefono, num_propiedad, tipo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $con->error);
    }
    $stmt->bind_param("ssssssiss", $rut, $nombres, $fecha_nacimiento, $usuario, $hash_contrasena, $sexo, $telefono, $num_propiedad, $rol);

    if ($stmt->execute()) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Registro exitoso',
                text: 'El usuario ha sido registrado correctamente.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = 'usuario_form.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un error al registrar el usuario. Intente nuevamente.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }

    $stmt->close();
    $con->close();
} else {
    header("Location: usuario_form.php");
    exit;
}
?>
