<?php

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

    header('Content-Type: application/json');

    $config = require __DIR__ . '/../config/mail.php';

    $mail = new PHPMailer(true);

    try {
        $name = $_POST['name'] ?? 'No especificado';
        $email = $_POST['email'] ?? 'No especificado';
        $phone = $_POST['phone'] ?? 'No especificado';
        $asunto = $_POST['asunto'] ?? 'No especificado';
        $mensaje = $_POST['mensaje'] ?? 'No especificado';

        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($config['from'], $config['form-name']);
        $mail->addAddress($config['to']);

        if (!empty($email)) {
            $mail->addReplyTo($email, $name);
        }

        $mail->isHTML(true);
        $mail->Subject = 'Solicitar informacion: ' . $name;

        $mail->Body = "
            <h2> Solicitar informacion </h2>
            <p><strong>Nombre:</strong> $name</p>
            <p><strong>Correo:</strong> $email</p>
            <p><strong>Numero:</strong> $phone</p>
            <p><strong>Asunto:</strong> $asunto</p>
            <p><strong>Mensaje:</strong> $mensaje</p>
        ";

        $mail->send();

        echo json_encode([
            'success' => true,
            'message' => 'Tu mensaje fue enviado correctamente. Te contactaremos pronto.'
        ]);
        exit;

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo enviar el correo. Intenta de nuevo más tarde.'
        ]);
        exit;
    }