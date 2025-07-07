<?php
include 'setup/config.php';

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: error.html");
    exit();
}

// Restricción de acceso para usuarios tipo propietario
if (isset($_SESSION['tipo']) && strtolower($_SESSION['tipo']) === 'propietario') {
    header("Location: error.html");
    exit();
}

$con = conectar();

if (!isset($_GET['id'])) {
    header("Location: usuarios.php");
    exit();
}

$id = intval($_GET['id']);

// Handle update
if (isset($_POST['update'])) {
    $nombres = $_POST['nombres'];
    $rut = $_POST['rut'];
    $usuario = $_POST['usuario'];
    $tipo = $_POST['tipo'];
    $estado = $_POST['estado'];

    $update_sql = "UPDATE usuarios SET nombres=?, rut=?, usuario=?, tipo=?, estado=? WHERE id=?";
    $stmt = $con->prepare($update_sql);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . htmlspecialchars($con->errorInfo()[2]));
    }
    $stmt->bindParam(1, $nombres);
    $stmt->bindParam(2, $rut);
    $stmt->bindParam(3, $usuario);
    $stmt->bindParam(4, $tipo);
    $stmt->bindParam(5, $estado);
    $stmt->bindParam(6, $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $stmt = null;
        header("Location: usuarios.php");
        exit();
    } else {
        $errorInfo = $stmt->errorInfo();
        $stmt = null;
        echo "Error al actualizar el usuario: " . htmlspecialchars($errorInfo[2]);
        echo "<br>Valor recibido para RUT: " . htmlspecialchars($rut);
        exit();
    }
}

// Handle delete
if (isset($_POST['delete'])) {
    $delete_sql = "DELETE FROM usuarios WHERE id=?";
    $stmt = $con->prepare($delete_sql);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . htmlspecialchars($con->errorInfo()[2]));
    }
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    $stmt->execute();
    $stmt = null;

    header("Location: usuarios.php");
    exit();
}

// Fetch user data
$sql = "SELECT nombres, rut, usuario, tipo, estado FROM usuarios WHERE id=?";
$stmt = $con->prepare($sql);
if (!$stmt) {
    die("Error en la preparación de la consulta: " . htmlspecialchars($con->errorInfo()[2]));
}
$stmt->bindParam(1, $id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt = null;
if (is_object($con) && $con instanceof mysqli) {
    mysqli_close($con);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Usuario</title>
    <link href="css/login_penca.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-4">
        <h1>Editar Usuario</h1>
        <form method="POST" action="editar_usuario.php?id=<?php echo $id; ?>" id="editarUsuarioForm">
            <div class="mb-3">
                <label for="nombres" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo htmlspecialchars($user['nombres'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="rut" class="form-label">RUT</label>
        <input type="text" class="form-control" id="rut" name="rut" value="<?php echo htmlspecialchars($user['rut'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label">usuario</label>
        <input type="email" class="form-control" id="usuario" name="usuario" value="<?php echo htmlspecialchars($user['usuario'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select" id="tipo" name="tipo" required>
                <option value="Propietario" <?php if (($user['tipo'] ?? '') == 'Propietario') echo 'selected'; ?>>Propietario</option>
                <option value="Gestor" <?php if (($user['tipo'] ?? '') == 'Gestor') echo 'selected'; ?>>Gestor</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado" required>
                <option value="activo" <?php if (($user['estado'] ?? '') == 'activo') echo 'selected'; ?>>Activo</option>
                <option value="inactivo" <?php if (($user['estado'] ?? '') == 'inactivo') echo 'selected'; ?>>Inactivo</option>
                </select>
            </div>
            <button type="submit" name="update" class="btn btn-primary">Guardar Cambios</button>
            <button type="submit" name="delete" id="deleteButton" class="btn btn-danger">Eliminar Usuario</button>
            <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
        </form>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.getElementById('editarUsuarioForm').addEventListener('submit', function(event) {
                var nombres = document.getElementById('nombres').value.trim();
                var rut = document.getElementById('rut').value.trim();
                var usuario = document.getElementById('usuario').value.trim();
                var tipo = document.getElementById('tipo').value;
                var estado = document.getElementById('estado').value;

                // Validar que el nombre no contenga números
                if (/\d/.test(nombres)) {
                    event.preventDefault();
                    Swal.fire('Error', 'El nombre no puede contener números.', 'error');
                    return;
                }

                if (!nombres || !rut || !usuario || !tipo || !estado) {
                    event.preventDefault();
                    Swal.fire('Error', 'Por favor, complete todos los campos requeridos.', 'error');
                    return;
                }

                // Validar RUT chileno
                function validarRut(rut) {
                    rut = rut.replace(/\./g, '').replace('-', '');
                    var cuerpo = rut.slice(0, -1);
                    var dv = rut.slice(-1).toUpperCase();

                    var suma = 0;
                    var multiplo = 2;

                    for (var i = cuerpo.length - 1; i >= 0; i--) {
                        suma += multiplo * parseInt(cuerpo.charAt(i));
                        multiplo = multiplo < 7 ? multiplo + 1 : 2;
                    }

                    var dvEsperado = 11 - (suma % 11);
                    dvEsperado = dvEsperado === 11 ? '0' : dvEsperado === 10 ? 'K' : dvEsperado.toString();

                    return dv === dvEsperado;
                }

                if (!validarRut(rut)) {
                    event.preventDefault();
                    Swal.fire('Error', 'RUT inválido.', 'error');
                    return;
                }

                // Validar correo electrónico
                var regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regexEmail.test(usuario)) {
                    event.preventDefault();
                    Swal.fire('Error', 'Correo electrónico inválido.', 'error');
                    return;
                }
            });

            document.getElementById('deleteButton').addEventListener('click', function(event) {
                event.preventDefault();
                Swal.fire({
                    title: '¿Está seguro de eliminar este usuario?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit the form with delete action
                        var form = document.getElementById('editarUsuarioForm');
                        var inputDelete = document.createElement('input');
                        inputDelete.type = 'hidden';
                        inputDelete.name = 'delete';
                        inputDelete.value = '1';
                        form.appendChild(inputDelete);
                        form.submit();
                    }
                });
            });
        </script>
    </div>
</body>
</html>
