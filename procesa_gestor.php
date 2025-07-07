<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'setup/config.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir y sanitizar datos
    $rut = trim($_POST['rut']);
    $nombres = trim($_POST['nombre']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $usuario = trim($_POST['correo']);
    $clave = $_POST['clave'];
$sexo = isset($_POST['sexo']) ? trim($_POST['sexo']) : '';
    $telefono = trim($_POST['telefono']);

    // Validar campos obligatorios
    if (
        empty($rut) || empty($nombres) || empty($fecha_nacimiento) || empty($usuario) ||
        empty($clave) || empty($sexo) || empty($telefono) || !isset($_FILES['archivo'])
    ) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor complete todos los campos.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.history.back();
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
                window.location.href = 'gestor_form.php';
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
                window.history.back();
            });
        </script>";
        exit;
    }

    // Validar archivo PDF
    if ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al subir el archivo.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit;
    }

    $fileTmpPath = $_FILES['archivo']['tmp_name'];
    $fileName = $_FILES['archivo']['name'];
    $fileSize = $_FILES['archivo']['size'];
    $fileType = $_FILES['archivo']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Validar extensión PDF
    if ($fileExtension !== 'pdf') {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El archivo debe ser un PDF.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit;
    }

    // Crear carpeta uploads si no existe
    $uploadFileDir = './uploads/';
    if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0755, true);
    }

    // Generar nombre único para el archivo
    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
    $dest_path = $uploadFileDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $dest_path)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al mover el archivo subido.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit;
    }

$hash_clave = md5($clave);

    // Insertar en la base de datos
    $con = conectar();

$stmt = $con->prepare("INSERT INTO usuarios (rut, nombres, fecha_nacimiento, usuario, clave, sexo, telefono, archivo, tipo, estado, num_propiedad) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $con->error);
}
$tipo = "gestor";
$estado = "activo";
$num_propiedad = 0;
$stmt->bindValue(1, $rut, PDO::PARAM_STR);
$stmt->bindValue(2, $nombres, PDO::PARAM_STR);
$stmt->bindValue(3, $fecha_nacimiento, PDO::PARAM_STR);
$stmt->bindValue(4, $usuario, PDO::PARAM_STR);
$stmt->bindValue(5, $hash_clave, PDO::PARAM_STR);
$stmt->bindValue(6, $sexo, PDO::PARAM_STR);
$stmt->bindValue(7, $telefono, PDO::PARAM_STR);
$stmt->bindValue(8, $newFileName, PDO::PARAM_STR);
$stmt->bindValue(9, $tipo, PDO::PARAM_STR);
$stmt->bindValue(10, $estado, PDO::PARAM_STR);
$stmt->bindValue(11, $num_propiedad, PDO::PARAM_INT);

if ($stmt->execute()) {
    // Redirección PHP en lugar de JavaScript
    header("Location: login.html");
    exit;
} else {
    $error = $stmt->error;
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un error al registrar el gestor inmobiliario: " . addslashes($error) . "',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.history.back();
        });
    </script>";
}

    $stmt->close();
    $con->close();
} else {
    header("Location: gestor_form.php");
    exit;
}
?>
