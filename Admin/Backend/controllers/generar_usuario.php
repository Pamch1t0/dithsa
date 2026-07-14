<?php

    function generarUsuario($prefijo = 'Ang') {
        return $prefijo . '_' . bin2hex(random_bytes(4));
    }

    $nuevoUsuario = generarUsuario();

    echo "Usuario: " . $nuevoUsuario;

?>