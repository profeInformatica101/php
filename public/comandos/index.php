<?php
// Configura el encabezado para mostrar texto plano en el navegador
header("Content-Type: text/plain; charset=utf-8");

// Comando del sistema (usa escapeshellcmd para mayor seguridad)
$cmd = escapeshellcmd('ls -la');

// Ejecuta el comando y captura la salida
$output = shell_exec($cmd);


// Muestra la salida en el navegador
echo $output;
?>
