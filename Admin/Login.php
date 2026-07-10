<?php
    session_start();

    $config = require __DIR__ . '/Backend/config/db.php';
    $db = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database']);

    if (!$db) {
        die('Error de conexión. Intenta más tarde.');
    }

    $error = '';

    if (!isset($_SESSION['intentos'])) {
        $_SESSION['intentos'] = 0;
        $_SESSION['ultimo_intento'] = time();
    }

    $bloqueado = $_SESSION['intentos'] >= 5 && (time() - $_SESSION['ultimo_intento']) < 300;

    if ($bloqueado) {
        $error = 'Demasiados intentos fallidos. Intenta de nuevo en unos minutos.';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$bloqueado) {

        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';   

        if ($usuario === '' || $password === '') {
            $error = 'Ingrese su usuario y contraseña';
        } else {
            $consulta = "SELECT * FROM administradores WHERE nombre = ?";
            $stmt = mysqli_prepare($db, $consulta);
            mysqli_stmt_bind_param($stmt, 's', $usuario);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            if ($resultado && mysqli_num_rows($resultado) === 1) {

                $admin = mysqli_fetch_assoc($resultado);

                if (password_verify($password, $admin['password'])) {

                    $_SESSION['intentos'] = 0;

                    session_regenerate_id(true);

                    $_SESSION['admin_id'] = $admin['id'];

                    $admin_activo = "UPDATE administradores SET activo = 1 WHERE id = ?";
                    $stmtUpdate = mysqli_prepare($db, $admin_activo);
                    mysqli_stmt_bind_param($stmtUpdate, 'i', $admin['id']);
                    mysqli_stmt_execute($stmtUpdate);

                    header('Location: dashboard.php');
                    exit;

                } else {
                    $_SESSION['intentos']++;
                    $_SESSION['ultimo_intento'] = time();
                    $error = 'Usuario o contraseña incorrectos';
                }

            } else {
                $_SESSION['intentos']++;
                $_SESSION['ultimo_intento'] = time();
                $error = 'Usuario o contraseña incorrectos';
            }
        }
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/Styles/Login.css">
    <title> DITHSA - Acceso Administrador </title>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-mark">
                <img src="assets/images/ei--user.png" alt="Logo DITHSA">
            </div>

            <h1> Panel Administrador </h1>
            <p class="subtitle"> DITHSA · Acceso restringido</p>

            <?php if($error): ?>
                <div class="error-msg"> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="Login.php" method="POST">
                <div class="campo">
                    <label> Usuario </label>
                    <input type="text" name="usuario" id="usuario">
                </div>
                <div class="campo">
                    <label for="password"> Contraseña </label>
                    <input type="password" name="password" id="password">
                </div>

                <button type="submit" class="btn-primary"> Entrar </button>
            </form>
        </div>
    </div>
</body>
</html>