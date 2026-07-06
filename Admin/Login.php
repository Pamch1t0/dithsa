<?php
    session_start();

    $db = mysqli_connect('localhost', 'root', '', 'DITHSA');
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($usuario === '' || $password === '') {
            $error = 'Ingrese su usuario y contraseña';
        }
        
        $consulta = "SELECT * FROM administradores WHERE nombre = '$usuario'";
        $resultado = mysqli_query($db, $consulta);

        if (mysqli_num_rows($resultado) == 1) {

            $admin = mysqli_fetch_assoc($resultado);
            
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];

                $admin_activo = "UPDATE administradores SET activo = 1 WHERE id = " . $admin['id'];
                mysqli_query($db, $admin_activo);

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        } else {
            $error = 'Usuario o contraseña incorrectos';
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
                <img src="../assets/images/ei--user.png" alt="Logo DITHSA">
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
                    <label for=""> Contraseña </label>
                    <input type="password" name="password" id="password">
                </div>

                <button type="submit" class="btn-primary"> Entrar </button>
            </form>
        </div>
    </div>
</body>
</html>