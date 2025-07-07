<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'setup/error_handling.php';
safeInclude('setup/config.php');

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

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $con = conectar();
    try {
        $stmt = $con->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$delete_id]);
    } catch (PDOException $e) {
        die("Error al eliminar usuario: " . $e->getMessage());
    }
    $stmt = null;
    header("Location: usuarios.php");
    exit();
}

// Handle insert action
if (isset($_POST['insert'])) {
    $nombres = $_POST['nombres'];
$rut = substr(trim($_POST['rut']), 0, 12); // Limitar a 12 caracteres para permitir el ingreso completo del RUT
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];
    $telefono = $_POST['telefono'];
    $tipo = $_POST['tipo'];
    $estado = (string)$_POST['estado']; // Ensure estado is string "activo" or "inactivo"
    $sexo = isset($_POST['sexo']) ? $_POST['sexo'] : ''; // Check if sexo is set
    $num_propiedad = isset($_POST['num_propiedad']) && $_POST['num_propiedad'] !== '' ? (int)$_POST['num_propiedad'] : 0; // Set to int or 0
    $fecha_nacimiento = isset($_POST['fecha_nacimiento']) && $_POST['fecha_nacimiento'] !== '' ? $_POST['fecha_nacimiento'] : '0000-00-00'; // Set to value or default date

    // Handle file upload if tipo is Gestor
    $archivo_path = '';
    if ($tipo === 'Gestor' && isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        $file_mime = mime_content_type($_FILES['archivo']['tmp_name']);
        $allowed_mimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
            'image/jpg'
        ];
        error_log("Archivo subido: " . $_FILES['archivo']['name'] . " - Ext: " . $file_extension . " - MIME: " . $file_mime);
        if (!in_array($file_extension, $allowed_extensions) || !in_array($file_mime, $allowed_mimes)) {
            error_log("Archivo no válido subido: " . $_FILES['archivo']['name']);
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>window.onload = function() { Swal.fire({icon: 'error', title: 'Archivo no válido', text: 'Solo se permiten archivos PDF, DOC, DOCX, JPG, JPEG y PNG.'}).then(() => { window.location.href = 'usuarios.php'; }); };</script>";
            exit();
        }
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $archivo_name = basename($_FILES['archivo']['name']);
        $archivo_path = $upload_dir . uniqid() . '_' . $archivo_name;
        if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $archivo_path)) {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>window.onload = function() { Swal.fire({icon: 'error', title: 'Error al subir archivo', text: 'No se pudo guardar el archivo en el servidor.'}).then(() => { window.location.href = 'usuarios.php'; }); };</script>";
            exit();
        }
    }

    // Hash the password using MD5 as requested
    $clave_hash = md5($clave);

    $con = conectar();
    $insert_sql = "INSERT INTO usuarios (nombres, rut, usuario, clave, telefono, tipo, estado, archivo, sexo, num_propiedad, fecha_nacimiento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($insert_sql);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . htmlspecialchars($con->errorInfo()[2]));
    }
    $stmt->bindValue(1, $nombres, PDO::PARAM_STR);
    $stmt->bindValue(2, $rut, PDO::PARAM_STR);
    $stmt->bindValue(3, $usuario, PDO::PARAM_STR);
    $stmt->bindValue(4, $clave_hash, PDO::PARAM_STR);
    $stmt->bindValue(5, $telefono, PDO::PARAM_STR);
    $stmt->bindValue(6, $tipo, PDO::PARAM_STR);
    $stmt->bindValue(7, $estado, PDO::PARAM_STR);
    $stmt->bindValue(8, $archivo_path, PDO::PARAM_STR);
    $stmt->bindValue(9, $sexo, PDO::PARAM_STR);
    $stmt->bindValue(10, $num_propiedad, PDO::PARAM_INT);
    $stmt->bindValue(11, $fecha_nacimiento, PDO::PARAM_STR);
    try {
        $stmt->execute();
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Conversion from collation') !== false) {
            // Try to convert parameters to UTF-8
            $nombres = mb_convert_encoding($nombres, 'UTF-8', 'auto');
            $rut = mb_convert_encoding($rut, 'UTF-8', 'auto');
            $usuario = mb_convert_encoding($usuario, 'UTF-8', 'auto');
            $clave_hash = mb_convert_encoding($clave_hash, 'UTF-8', 'auto');
            $telefono = mb_convert_encoding($telefono, 'UTF-8', 'auto');
            $tipo = mb_convert_encoding($tipo, 'UTF-8', 'auto');
            $estado = mb_convert_encoding($estado, 'UTF-8', 'auto');
            $archivo_path = mb_convert_encoding($archivo_path, 'UTF-8', 'auto');
            $sexo = mb_convert_encoding($sexo, 'UTF-8', 'auto');
            $fecha_nacimiento = mb_convert_encoding($fecha_nacimiento, 'UTF-8', 'auto');
            // Rebind with converted values
            $stmt->bindValue(1, $nombres, PDO::PARAM_STR);
            $stmt->bindValue(2, $rut, PDO::PARAM_STR);
            $stmt->bindValue(3, $usuario, PDO::PARAM_STR);
            $stmt->bindValue(4, $clave_hash, PDO::PARAM_STR);
            $stmt->bindValue(5, $telefono, PDO::PARAM_STR);
            $stmt->bindValue(6, $tipo, PDO::PARAM_STR);
            $stmt->bindValue(7, $estado, PDO::PARAM_STR);
            $stmt->bindValue(8, $archivo_path, PDO::PARAM_STR);
            $stmt->bindValue(9, $sexo, PDO::PARAM_STR);
            $stmt->bindValue(10, $num_propiedad, PDO::PARAM_INT);
            $stmt->bindValue(11, $fecha_nacimiento, PDO::PARAM_STR);
            try {
                $stmt->execute();
            } catch (PDOException $e) {
                die("Error en la inserción tras conversión: " . htmlspecialchars($e->getMessage()));
            }
        } else {
            die("Error en la inserción: " . htmlspecialchars($e->getMessage()));
        }
    }
    $stmt = null;
    header("Location: usuarios.php");
    exit();
}

try {
    $con = conectar();
    $stmt = $con->prepare("SELECT id, nombres, rut, usuario, tipo, estado FROM usuarios WHERE estado = 'inactivo'");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt_all = $con->prepare("SELECT id, nombres, rut, usuario, tipo, estado FROM usuarios");
    $stmt_all->execute();
    $result_all = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener usuarios: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Administrar Usuarios</title>
    <link href="css/login_penca.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/form.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Administrar Usuarios - Usuarios Inactivos</h1>

        <h3>Agregar Nuevo Usuario</h3>
        <form method="POST" action="usuarios.php" class="mb-4" id="usuarioForm" enctype="multipart/form-data">
            <input type="hidden" name="insert" value="1" />
            <div class="mb-3">
                <label for="nombres" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombres" name="nombres" required>
            </div>
            <div class="mb-3">
<label for="rut" class="form-label">RUT</label>
<input type="text" class="form-control" id="rut" name="rut" maxlength="12" pattern="^[0-9]+[-|‐]{1}[0-9kK]{1}$" title="Ingrese un RUT válido, formato: 12345678-9" required>
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label">usuario</label>
                <input type="email" class="form-control" id="usuario" name="usuario" required>
            </div>
            <div class="mb-3">
                <label for="clave" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="clave" name="clave" required>
            </div>
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="telefono" name="telefono" required>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select" id="tipo" name="tipo" required>
                    <option value="Propietario">Propietario</option>
                    <option value="Gestor">Gestor</option>
                </select>
            </div>
            <div class="mb-3" id="fileUploadDiv" style="display:none;">
                <label for="archivo" class="form-label">Subir Archivo (solo para Gestor)</label>
            <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            </div>
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado" required>
                    <option value="activo">Activo</option>
                    <option value="inactivo" selected>Inactivo</option>
                </select>
            </div>
            <button type="submit">Agregar Usuario</button>
        </form>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.getElementById('tipo').addEventListener('change', function() {
                var fileDiv = document.getElementById('fileUploadDiv');
                if (this.value === 'Gestor') {
                    fileDiv.style.display = 'block';
                } else {
                    fileDiv.style.display = 'none';
                }
            });

            document.getElementById('usuarioForm').addEventListener('submit', function(event) {
                event.preventDefault();

                var nombres = document.getElementById('nombres').value.trim();
                var rut = document.getElementById('rut').value.trim();
                var usuario = document.getElementById('usuario').value.trim();
                var clave = document.getElementById('clave').value;
                var telefono = document.getElementById('telefono').value.trim();
                var tipo = document.getElementById('tipo').value;
                var estado = document.getElementById('estado').value;
                var archivo = document.getElementById('archivo');

                // Validar campos requeridos
                if (!nombres || !rut || !usuario || !clave || !telefono || !tipo || !estado) {
                    Swal.fire('Error', 'Por favor, complete todos los campos requeridos.', 'error');
                    return;
                }

                // Validar que el nombre no contenga números
                if (/\d/.test(nombres)) {
                    Swal.fire('Error', 'El nombre no puede contener números.', 'error');
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
                    Swal.fire('Error', 'RUT inválido.', 'error');
                    return;
                }

                // Validar contraseña robusta (mínimo 8 caracteres, al menos una mayúscula, una minúscula, un número y un carácter especial)
                var regexClave = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
                if (!regexClave.test(clave)) {
                    Swal.fire('Error', 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas, números y caracteres especiales.', 'error');
                    return;
                }

                // Validar teléfono (solo números, 8 a 15 dígitos)
                var regexTelefono = /^\d{8,15}$/;
                if (!regexTelefono.test(telefono)) {
                    Swal.fire('Error', 'El teléfono debe contener solo números y tener entre 8 y 15 dígitos.', 'error');
                    return;
                }

                // Validar correo electrónico
                var regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regexEmail.test(usuario)) {
                    Swal.fire('Error', 'Correo electrónico inválido.', 'error');
                    return;
                }

                // Validar archivo si tipo es Gestor
                if (tipo === 'Gestor') {
                    if (!archivo.value) {
                        Swal.fire('Error', 'Debe subir un archivo para el tipo Gestor.', 'error');
                        return;
                    }
                    // Validar tipo de archivo
                    var allowedExtensions = /(\.pdf|\.doc|\.docx|\.jpg|\.jpeg|\.png)$/i;
                    if (!allowedExtensions.exec(archivo.value)) {
                        Swal.fire('Error', 'Archivo no válido. Solo se permiten archivos PDF, DOC, DOCX, JPG, JPEG y PNG.', 'error');
                        archivo.value = '';
                        return;
                    }
                }

                // Si todo es válido, enviar el formulario
                this.submit();
            });

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.delete-link').forEach(function(element) {
                    element.addEventListener('click', function(event) {
                        event.preventDefault();
                        const href = this.getAttribute('href');
                        Swal.fire({
                            title: '¿Está seguro de eliminar este usuario?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = href;
                            }
                        });
                    });
                });
            });
        </script>

        <h3>Usuarios Inactivos</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>RUT</th>
                    <th>usuario</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
<?php if (!empty($result)): ?>
    <?php foreach ($result as $row): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['nombres']); ?></td>
            <td><?php echo htmlspecialchars($row['rut']); ?></td>
            <td><?php echo htmlspecialchars($row['usuario']); ?></td>
            <td><?php echo htmlspecialchars($row['tipo']); ?></td>
            <td><?php echo htmlspecialchars($row['estado']); ?></td>
            <td>
                <a href="editar_usuario.php?id=<?php echo $row['id']; ?>" class="btn-small btn-edit">Editar</a>
                <a href="usuarios.php?delete_id=<?php echo $row['id']; ?>" class="btn-small btn-delete delete-link">Eliminar</a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="6" class="text-center">No hay usuarios inactivos.</td></tr>
<?php endif; ?>
            </tbody>
        </table>

        <h3>Todos los Usuarios</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>RUT</th>
                    <th>usuario</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
<?php if (!empty($result_all)): ?>
    <?php foreach ($result_all as $row): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['nombres']); ?></td>
            <td><?php echo htmlspecialchars($row['rut']); ?></td>
            <td><?php echo htmlspecialchars($row['usuario']); ?></td>
            <td><?php echo htmlspecialchars($row['tipo']); ?></td>
            <td><?php echo htmlspecialchars($row['estado']); ?></td>
            <td>
                <a href="editar_usuario.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                <a href="usuarios.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger delete-link">Eliminar</a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="6" class="text-center">No hay usuarios.</td></tr>
<?php endif; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="btn-volver">Volver al Dashboard</a>
    </div>
</body>
</html>

<?php
if (is_object($con) && $con instanceof mysqli) {
    mysqli_close($con);
}
?>
