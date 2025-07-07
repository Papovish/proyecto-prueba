<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP para Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'andres.perez.inacap@gmail.com'; // Cambia esto por tu email Gmail
    $mail->Password = 'aycb vsat masd lrjc'; // Cambia esto por tu password o app password de Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('andres.perez.inacap@gmail.com', 'Prueba de Correo');
    $mail->addAddress('andresp382@gmail.com'); // Cambia esto por el correo donde quieres recibir la prueba

    $mail->isHTML(true);
    $mail->Subject = 'Correo de prueba PHPMailer';
    $mail->Body    = 'Este es un correo de prueba para verificar la configuración de PHPMailer.';

    $mail->send();
    echo 'Correo enviado correctamente.';
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}
?>
