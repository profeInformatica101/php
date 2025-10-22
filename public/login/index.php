<?php

session_start();
require_once __DIR__ . '/plantillas.php';

$auth = $_SESSION['auth'] ?? null;

if ($auth) {
    // Usuario autenticado
    $contenido  = '<h1>Hola, ' . htmlspecialchars($auth['nombre'], ENT_QUOTES, 'UTF-8') . ' 👋</h1>';
    $contenido .= '<p>Estás dentro de la sesión. Puedes cerrarla cuando quieras.</p>';
    $contenido .= generarLogout();

    echo generarPaginaHTML('Ejemplo Inicio Sesión', $contenido);
} else {
    // Usuario no autenticado
    echo generarPaginaHTML('Ejemplo Mostrar Formulario', generarFormularioLogin());
}
