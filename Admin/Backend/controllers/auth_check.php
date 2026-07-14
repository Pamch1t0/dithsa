<?php
    session_start();

    if (!isset($_SESSION['admin_id'])) {
        header('Location: Login.php');
        exit;
    }

    $tiempo_limite = 1800;

    if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad']) > $tiempo_limite) {

        $config = require __DIR__ . '/../config/db.php';
        $db = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database']);

        if ($db && isset($_SESSION['admin_id'])) {
            $admin_inactivo = "UPDATE administradores SET activo = 0, ultimo_acceso = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($db, $admin_inactivo);
            mysqli_stmt_bind_param($stmt, 'i', $_SESSION['admin_id']);
            mysqli_stmt_execute($stmt);
        }

        session_unset();
        session_destroy();
        header('Location: Login.php?expirado=1');
        exit;
    }

    $_SESSION['ultima_actividad'] = time();