<?php
// terminal.php

header("Content-Type: text/html; charset=utf-8");
?>
<form method="post">
  <label>Introduce un comando:</label><br>
  <input type="text" name="cmd" style="width: 400px;">
  <button type="submit">Ejecutar</button>
</form>

<pre>
<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cmd = trim($_POST["cmd"]);

    // Evita comandos peligrosos
    $prohibidos = ['rm', 'shutdown', 'reboot', 'passwd', 'userdel', 'del'];
    foreach ($prohibidos as $peligro) {
        if (stripos($cmd, $peligro) !== false) {
            echo "⚠️ Comando no permitido por seguridad.";
            exit;
        }
    }

    // Escapa y ejecuta el comando
    $seguro = escapeshellcmd($cmd);
    $salida = shell_exec($seguro . " 2>&1"); // incluye errores también
    echo htmlspecialchars($salida ?: "(sin salida)");
}
?>
</pre>
