<?php
    session_start();

    if (!isset($_SESSION['admin_id'])) {
        header('Location: Login.php');
        exit;
    }

    $tiempo_limite = 1800;

    if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad']) > $tiempo_limite) {
        session_unset();
        session_destroy();
        header('Location: Login.php?expirado=1');
        exit;
    }

    $_SESSION['ultima_actividad'] = time();