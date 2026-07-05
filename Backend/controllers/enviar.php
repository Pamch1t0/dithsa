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

        $puesto = $_POST['puesto'] ?? 'No especificado';
        $name = $_POST['name'] ?? 'Sin nombre';
        $age = $_POST['age'] ?? 'No especificada';
        $sexo = $_POST['sexo'] ?? 'No especificado';
        $phone = $_POST['phone'] ?? 'No especificado';
        $city_municipality = $_POST['city-municipality'] ?? 'No especificada';
        $xp = $_POST['xp'] ?? 'No especificado';
        $experience = $_POST['field-experience'] ?? 'No especificada';

        if (empty($puesto) || empty($name) || empty($phone)) {
            echo json_encode(
                ['success' => false, 'message' => 'Faltan campos obligatorios.']
            );
            exit;
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            echo json_encode(
                ['success' => false, 'message' => 'Correo invalido']   
            );
            exit;
        }

        // Validacion del y guardado del CURRICULUM

        $nombrePDF = "";
        $carpeta = __DIR__ . "/../curriculums/";

        if (!isset($_FILES['curriculum']) || $_FILES['curriculum']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(
                ['success' => false, 'message' => 'Debe adjuntar tu curriculum en PDF.']
            );
            exit;
        }

        if ($_FILES['curriculum']['size']> 5 * 1024 * 1024) {
            echo json_encode(
                ['success' => false, 'message' => 'El archivo no debe superar a 5 MB.'] 
            );
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['curriculum']['tmp_name']);
        finfo_close($finfo);

        if ($mimeType !== 'application/pdf') {
            echo json_encode(
                ['success' => false, 'message' => 'El archcivo debe ser un PDF valido.']
            );
            exit;
        }

        $extension = strtolower(pathinfo($_FILES['curriculum']['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            echo json_encode(
                ['success' => false, 'message' => 'El archivo debe tener la extension .pdf.']
            );
            exit;
        }

        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        $nombrePDF = uniqid('cv_', true) . '.pdf';

        move_uploaded_file(
            $_FILES['curriculum']['tmp_name'],
            $carpeta . $nombrePDF
        );

        $sql = "INSERT INTO postulaciones
        (puesto, nombre, edad, sexo, telefono, correo, ciudad, experiencia, detalle_experiencia, curriculum)
        VALUES
        (:puesto, :nombre, :edad, :sexo, :telefono, :correo, :ciudad, :experiencia, :detalle, :curriculum)";

        $stmt = $conexion->prepare($sql);

        $stmt->execute([
            ':puesto' => $puesto,
            ':nombre' => $name,
            ':edad' => $age,
            ':sexo' => $sexo,
            ':telefono' => $phone,
            ':correo' => $email,
            ':ciudad' => $city_municipality,
            ':experiencia' => $xp,
            ':detalle' => $experience,
            ':curriculum' => $nombrePDF
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
        $mail->addReplyTo($email, $name);

        if (!empty($nombrePDF)) {
            $mail->addAttachment($carpeta . $nombrePDF, $nombrePDF);
        }

        $puesto_safe = htmlspecialchars($puesto);
        $name_safe = htmlspecialchars($name);
        $age_safe = htmlspecialchars($age);
        $sexo_safe = htmlspecialchars($sexo);
        $phone_safe = htmlspecialchars($phone);
        $email_safe = htmlspecialchars($email);
        $city_safe = htmlspecialchars($city_municipality);
        $xp_safe = htmlspecialchars($xp);
        $experience_safe = htmlspecialchars($experience);

        $mail->isHTML(true);
        $mail->Subject = "Nueva Postulación:  $puesto_safe";

        $mail->Body = "
            <h2>Nueva Postulación</h2>
            <hr>
            <p><strong>Puesto:</strong> {$puesto_safe}</p>
            <p><strong>Nombre:</strong> {$name_safe}</p>
            <p><strong>Edad:</strong> {$age_safe}</p>
            <p><strong>Sexo:</strong> {$sexo_safe}</p>
            <p><strong>Teléfono:</strong> {$phone_safe}</p>
            <p><strong>Correo:</strong> {$email_safe}</p>
            <p><strong>Ciudad/Municipio:</strong> {$city_safe}</p>
            <p><strong>¿Tiene experiencia?:</strong> {$xp_safe}</p>
            <p><strong>Experiencia:</strong> {$experience_safe}</p>
        ";

        $mail->send();

        echo json_encode([
            'success' => true,
            'message' => 'Tu solicitud fue enviada correctamente. Te contactaremos pronto.'
        ]);
        exit;

    } catch (\Throwable $e) {
        error_log("Error en enviar.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo enviar tu solicitud. Intenta de nuevo más tarde.'
        ]);
        exit;
    }
?>
