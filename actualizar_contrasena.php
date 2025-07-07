<?php
require 'vendor/autoload.php'; // Asegúrese de tener instalado firebase/php-jwt o el SDK de Firebase Admin para PHP

use Firebase\Auth\Token\Exception\InvalidToken;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$email = $_POST['email'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';

if (empty($email) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros requeridos']);
    exit;
}

// Configuración de Firebase Admin SDK
$serviceAccountFile = __DIR__ . '/path/to/firebase-service-account.json'; // Cambie esta ruta al archivo JSON de su cuenta de servicio

try {
    $factory = (new Factory)->withServiceAccount($serviceAccountFile);
    $auth = $factory->createAuth();

    // Buscar usuario por email
    $user = $auth->getUserByEmail($email);

    // Actualizar contraseña en Firebase
    $auth->updateUser($user->uid, [
        'password' => $newPassword,
    ]);

    // Actualizar contraseña en MySQL
    $mysqli = new mysqli('your-mysql-host', 'your-mysql-user', 'your-mysql-password', 'your-mysql-database');
    if ($mysqli->connect_errno) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión a la base de datos']);
        exit;
    }

    // Hashear la contraseña antes de guardar (por ejemplo, usando password_hash)
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare('UPDATE users SET password = ? WHERE email = ?');
    $stmt->bind_param('ss', $hashedPassword, $email);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado en la base de datos']);
        exit;
    }

    $stmt->close();
    $mysqli->close();

    echo json_encode(['success' => 'Contraseña actualizada correctamente']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar la contraseña: ' . $e->getMessage()]);
}
