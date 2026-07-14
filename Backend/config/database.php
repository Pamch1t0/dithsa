<?php 

    $host = "localhost";
    $dbname = "dithsa";
    $user = "root";
    $password = "";

    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $conexion = new PDO($dsn, $user, $password);

        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e) {
        echo "Error de conexion: " . $e->getMessage();
    }

?>