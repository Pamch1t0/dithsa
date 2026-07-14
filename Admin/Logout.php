<?php
    session_start();

    $config = require_once __DIR__ . '/Backend/config/db.php';
    $db = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database']);

    if ($db && isset($_SESSION['admin_id'])) {
        $admin_inactivo = "UPDATE administradores SET activo = 0, ultimo_acceso = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($db, $admin_inactivo);
        mysqli_stmt_bind_param($stmt, 'i', $_SESSION['admin_id']);
        mysqli_stmt_execute($stmt);
    }

    session_unset();
    session_destroy();

    header('Location: Login.php');
    exit();
?>
