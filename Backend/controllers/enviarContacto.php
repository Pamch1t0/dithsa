<?php

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

    require_once __DIR__ . '/../config/database.php';

    header('Content-Type: application/json');

    $config = require __DIR__ . '/../config/mail.php';

    $mail = new PHPMailer(true);

    try {
        if (!empty($_POST['website'])) {
            echo json_encode(
                ['success' => true, 'message' => 'Mensaje Enviado.']
            );
            exit;
        }

        $name = $_POST['name'] ?? 'No especificado';
        
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            echo json_encode(
                ['success' => false, 'message' => 'Correo invalido.']
            );
            exit;
        }

        $phone = $_POST['phone'] ?? 'No especificado';
        $asunto = $_POST['asunto'] ?? 'No especificado';

        $mensaje = $_POST['mensaje'] ?? 'No especificado';
        if (strlen($mensaje) > 2000) {
            echo json_encode(
                ['success' => false, 'message' => 'El mensaje es demasiado largo']
            );
            exit;
        }

        $sql = "INSERT INTO  contactos
        (nombre, correo, telefono, asunto, mensaje)
        VALUES 
        (:nombre, :correo, :telefono, :asunto, :mensaje)";
        
        $stmt = $conexion->prepare($sql);

        $stmt->execute([
            ':nombre' => $name,
            ':correo' => $email,
            ':telefono' => $phone,
            ':asunto' => $asunto,
            ':mensaje' => $mensaje
        ]);

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

        $name_safe = htmlspecialchars($name);
        $email_safe = htmlspecialchars($email);
        $phone_safe = htmlspecialchars($phone);
        $asunto_safe = htmlspecialchars($asunto);
        $mensaje_safe = htmlspecialchars($mensaje);

        $mail->Body = "
            <h2> Solicitar informacion </h2>
            <p><strong>Nombre:</strong> $name_safe </p>
            <p><strong>Correo:</strong> $email_safe </p>
            <p><strong>Numero:</strong> $phone_safe </p>
            <p><strong>Asunto:</strong> $asunto_safe </p>
            <p><strong>Mensaje:</strong> $mensaje_safe </p>
        ";

        $mail->send();

        echo json_encode([
            'success' => true,
            'message' => 'Tu mensaje fue enviado correctamente. Te contactaremos pronto.'
        ]);
        exit;

    } catch (\Exception $e) {
        error_log("Error en enviarContacto.php" . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo enviar el correo. Intenta de nuevo más tarde.'
        ]);
        exit;
    }