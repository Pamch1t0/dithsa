<?php
    require __DIR__ . '/Backend/controllers/auth_check.php';

    $config = require __DIR__ . '/Backend/config/db.php';
    $db = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database']);
    
    if (!$db) {
        die('Error de conexión. Intenta más tarde.');
    }
    
    $periodos_validos = ['hoy', 'semana', 'mes', 'todo'];
    $periodo = $_GET['periodo'] ?? 'todo';
    
    if (!in_array($periodo, $periodos_validos)) {
        $periodo = 'todo';
    }

    $vista_validas = ['vacantes', 'contactos'];
    $vista = $_GET['vista'] ?? 'vacantes';

    if (!in_array($vista, $vista_validas)) {
        $vista = 'vacantes';
    }

    function condicionFecha($periodo, $columna) {
        switch ($periodo) {
            case 'hoy':
                return "WHERE DATE($columna) = CURDATE()";
            case 'semana':
                return "WHERE YEARWEEK($columna, 1) = YEARWEEK(CURDATE(), 1)";
            case 'mes':
                return "WHERE YEAR($columna) = YEAR(CURDATE()) AND MONTH($columna) = MONTH(CURDATE())";
            default:
                return "";
        }
    }
    
    $condicionPostulaciones = condicionFecha($periodo, 'fecha');
    $condicionContactos = condicionFecha($periodo, 'fecha');
    
    $totalPostulaciones = 0;
    $resultado = mysqli_query($db, "SELECT COUNT(*) AS total FROM postulaciones $condicionPostulaciones");
    if ($resultado) {
        $totalPostulaciones = mysqli_fetch_assoc($resultado)['total'];
    }
    
    $totalContactos = 0;
    $resultado = mysqli_query($db, "SELECT COUNT(*) AS total FROM contactos $condicionContactos");
    if ($resultado) {
        $totalContactos = mysqli_fetch_assoc($resultado)['total'];
    }
    
    $etiquetasPeriodo = [
        'hoy' => 'Hoy',
        'semana' => 'Esta semana',
        'mes' => 'Este mes',
        'todo' => 'Todo el tiempo'
    ];

    $resultado_vacantes = mysqli_query($db, "SELECT * FROM postulaciones $condicionPostulaciones ORDER BY fecha DESC LIMIT 5");
    $hayPostulaciones = $resultado_vacantes && mysqli_num_rows($resultado_vacantes) > 0;

    $resultado_contactos = mysqli_query($db, "SELECT * FROM contactos $condicionContactos ORDER BY fecha DESC LIMIT 5");
    $hayContactos = $resultado_contactos && mysqli_num_rows($resultado_contactos) > 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/Styles/dashboard.CSS">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/Js/btn-tables.js"></script>
    <title> DITHSA - Panel Administrador </title>
</head>
<body>
    <header class="header-container-admin">
        <div class="header-admin">
            <div class="logo-mark"> 
                <img src="assets/Images/ei--user.png" alt="Logo empresa">
                <span>DITHSA</span>
            </div>

            <a href="Logout.php" class="btn-cerrar-sesion"> Cerrar sesión </a>
        </div>
    </header>

    <main class="dashboard-container">
        <div class="dashboard-top">
            <h1>Panel de solicitudes</h1>
 
            <form method="GET" class="periodo-form">
                <input type="hidden" name="vista" id="vistaOculta" value="<?= htmlspecialchars($vista) ?>">
                <label for="periodo">Periodo:</label>
                <select name="periodo" id="periodo" onchange="this.form.submit()">
                    <?php foreach ($etiquetasPeriodo as $valor => $etiqueta): ?>
                        <option value="<?= $valor ?>" <?= $periodo === $valor ? 'selected' : '' ?>>
                            <?= $etiqueta ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
 
        <div class="cards-container">
            <div class="stat-card">
                <div class="stat-icon icon-vacantes">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?= $totalPostulaciones ?></span>
                    <span class="stat-label">Postulaciones a vacantes</span>
                    <span class="stat-periodo"><?= $etiquetasPeriodo[$periodo] ?></span>
                </div>
            </div>
 
            <div class="stat-card">
                <div class="stat-icon icon-contactos">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z" fill="none" stroke="none"/><path d="M22 6 12 13 2 6"/><path d="M2 6h20v12H2z"/></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?= $totalContactos ?></span>
                    <span class="stat-label">Mensajes de contacto</span>
                    <span class="stat-periodo"><?= $etiquetasPeriodo[$periodo] ?></span>
                </div>
            </div>
        </div>

        <div class="container-btn-tables">

            <div class="btn-tables">
                <button type="button" class="btn-toggle <?= $vista === 'vacantes' ? 'active' : '' ?>">
                    Vacantes
                </button>
                
                <button type="button" class="btn-toggle <?= $vista === 'contactos' ? 'active' : '' ?>">
                    Contactos
                </button>

            </div>
            
            <h2 class="tittle-tables"> Últimas postulaciones </h2>
            
            <div class="table-container">
                <div class="container-table <?= $vista === 'vacantes' ? 'active' : '' ?>" id="table-vacantes">
                    <?php if (!$hayPostulaciones): ?>
                        <p class="no-data-message">No hay postulaciones en el periodo seleccionado.</p>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Puesto</th>
                                <th>Nombre</th>
                                <th>Edad</th>
                                <th>Sexo</th>
                                <th>Telefono</th>
                                <th>Correo</th>
                                <th>Ciudad</th>
                                <th>Experiencia</th>
                                <th>Curriculum</th>
                                <th>Fecha</th>
                                <th>Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if ($resultado_vacantes) {
                                    while ($fila = mysqli_fetch_assoc($resultado_vacantes)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($fila['id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($fila['puesto']) . "</td>";
                                        echo "<td>" . htmlspecialchars($fila['nombre']) . "</td>";
                                        echo "<td>" . htmlspecialchars($fila['edad']) . "</td>";
                                        echo "<td>" . htmlspecialchars($fila['sexo']) . "</td>";
                                        echo "<td>" . htmlspecialchars($fila['telefono']) . "</td>";
                                        echo "<td>" . htmlspecialchars($fila['correo']) . "</td>";
                                        echo "<td>" . htmlspecialchars($fila['ciudad']) . "</td>";
                                        echo "<td>" . htmlspecialchars($fila['experiencia']) . "</td>";
                                        
                                        $rutapdf = "../Backend/curriculums/" . htmlspecialchars($fila['curriculum']);
                                        echo "<td> <a href='{$rutapdf}' target='_blank'> Ver PDF </a> </td>";
                                        
                                        echo "<td>" . htmlspecialchars($fila['fecha']) . "</td>";
                                        echo "<td> <button> Detalles </button> </td>";
                                        echo "</tr>";
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
                
                <div class="container-table <?= $vista === 'contactos' ? 'active' : '' ?>" id="table-contactos">
                    <?php if (!$hayContactos): ?>
                        <p class="no-data-message">No hay mensajes de contacto en el periodo seleccionado.</p>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th> ID </th>
                                <th> Nombre </th>
                                <th> Correo </th>
                                <th> Telefono </th>
                                <th> Asunto </th>
                                <th> Fecha </th>
                                <th> Accion </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                if ($resultado_contactos) {
                                    while ($fila =mysqli_fetch_assoc($resultado_contactos)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($fila['id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($fila['nombre']) . "</td>";
                                        echo "<td>" . htmlspecialchars($fila['correo']) . "</td>";
                                        echo "<td>" . htmlspecialchars($fila['telefono']) . "</td>";
                                        echo "<td>" . htmlspecialchars($fila['asunto']) . "</td>";
                                        echo "<td>" . htmlspecialchars($fila['fecha']) . "</td>";

                                        echo "<td> <button> Detalles </button> </td>";
                                        echo "</tr>";
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        
    </main>
</body>
</html>