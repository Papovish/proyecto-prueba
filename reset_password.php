<?php
require 'setup/config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_GET['token'] ?? '';

    // Debug temporal para mostrar token recibido
    echo "Token recibido: " . htmlspecialchars($token) . "<br>";

    if (empty($token)) {
        die('Token inválido.');
    }

    // Mostrar formulario para nueva contraseña
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8" />
        <title>Restablecer Contraseña</title>
        <link rel="stylesheet" href="css/form.css" />
    </head>
    <body>
        <div class="contenedor">
            <h2>Restablecer Contraseña</h2>
            <form method="POST" action="reset_password.php">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />
                <label for="password">Nueva Contraseña:</label>
                <input type="password" name="password" id="password" required />
                <label for="password_confirm">Confirmar Contraseña:</label>
                <input type="password" name="password_confirm" id="password_confirm" required />
                <button type="submit">Cambiar Contraseña</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($token) || empty($password) || empty($password_confirm)) {
        die('Todos los campos son obligatorios.');
    }

    if ($password !== $password_confirm) {
        die('Las contraseñas no coinciden.');
    }

    try {
        $con = conectar();

        // Verificar token y expiración
        $stmt = $con->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ?");
        $stmt->bindValue(1, $token);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            die('Token inválido.');
        }

        if (new DateTime() > new DateTime($row['expires_at'])) {
            die('El token ha expirado.');
        }

        $userId = $row['user_id'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Actualizar contraseña
        $stmt = $con->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt->bindValue(1, $hashedPassword);
        $stmt->bindValue(2, $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Eliminar token usado
        $stmt = $con->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->bindValue(1, $token);
        $stmt->execute();

        echo "Contraseña actualizada correctamente. Puedes <a href='login.html'>iniciar sesión</a> ahora.";
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
