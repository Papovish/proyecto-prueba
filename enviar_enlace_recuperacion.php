<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'setup/config.php';
require 'vendor/autoload.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'El correo es obligatorio.']);
        exit;
    }

    $con = conectar();

    $stmt = $con->prepare("SELECT id, usuario FROM usuarios WHERE usuario = ?");
    $stmt->bindValue(1, $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'El correo no está registrado.']);
        exit;
    }

    $userId = $user['id'];

$token = bin2hex(random_bytes(16));
$expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

echo "Token generado: " . $token . "\n"; // Debug temporal

$stmt = $con->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)");
$stmt->bindValue(1, $userId, PDO::PARAM_INT);
$stmt->bindValue(2, $token);
$stmt->bindValue(3, $expiry);
$stmt->execute();

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'andres.perez.inacap@gmail.com';
    $mail->Password = 'aycb vsat masd lrjc'; // contraseña de app Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('andres.perez.inacap@gmail.com', 'Recuperar Contraseña');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Enlace para restablecer tu contraseña';
    $url = "http://34.229.163.236/reset_password.php?token=" . urlencode($token); // IP pública actual
    $mail->Body = "Haz clic en el siguiente enlace para restablecer tu contraseña: <a href='$url'>$url</a>. Este enlace expirará en 1 hora.";

    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'Correo de recuperación enviado.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "No se pudo enviar el correo. Error: {$mail->ErrorInfo}"]);
}

    $con = null;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
