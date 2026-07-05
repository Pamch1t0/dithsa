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
        $puesto = $_POST['puesto'] ?? 'No especificado';
        $name = $_POST['name'] ?? 'Sin nombre';
        $age = $_POST['age'] ?? 'No especificada';
        $sexo = $_POST['sexo'] ?? 'No especificado';
        $phone = $_POST['phone'] ?? 'No especificado';
        $email = $_POST['email'] ?? '';
        $city_municipality = $_POST['city-municipality'] ?? 'No especificada';
        $xp = $_POST['xp'] ?? 'No especificado';
        $experience = $_POST['field-experience'] ?? 'No especificada';

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

        if (isset($_FILES['curriculum']) && $_FILES['curriculum']['error'] == UPLOAD_ERR_OK) {
            $ruta_temporal = $_FILES['curriculum']['tmp_name'];
            $nombre_archivo = $_FILES['curriculum']['name'];
            $mail->addAttachment($ruta_temporal, $nombre_archivo);
        }

        $mail->isHTML(true);
        $mail->Subject = 'Nueva Postulación: ' . $puesto;

        $mail->Body = "
            <h2> Nuevo formulario de postulación </h2>
            <hr>
            <p><strong>Puesto al que aplica:</strong> $puesto</p>
            <p><strong>Nombre:</strong> $name</p>
            <p><strong>Edad:</strong> $age</p>
            <p><strong>Sexo:</strong> $sexo</p>
            <p><strong>Teléfono:</strong> $phone</p>
            <p><strong>Correo:</strong> $email</p>
            <p><strong>Ciudad/Municipio:</strong> $city_municipality</p>
            <p><strong>¿Tiene experiencia?:</strong> $xp</p>
            <p><strong>Detalle de experiencia:</strong> $experience</p>
        ";

        $mail->send();

        echo json_encode([
            'success' => true,
            'message' => 'Tu solicitud fue enviada correctamente. Te contactaremos pronto.'
        ]);
        exit;

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo enviar tu solicitud. Intenta de nuevo más tarde.'
        ]);
        exit;
    }
?>
