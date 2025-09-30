<?php
// control_caja_hidden_simple_lineal.php
// Caja fuerte con combinación de 4 cifras usando campos hidden (INSEGURO).
// Versión didáctica para principiantes: todo en un archivo.

// ----------------------
// 0) CONFIGURACIÓN
// ----------------------
$COMBINACION  = '1234';
$MAX_INTENTOS = 4;

// Estado inicial
$intentos = isset($_POST['intentos']) ? (int)$_POST['intentos'] : 0;
$abierta  = isset($_POST['abierta']) && $_POST['abierta'] === '1';

$mensaje = '';
$error   = '';

// ----------------------
// 1) REINICIO
// ----------------------
if (isset($_POST['reiniciar'])) {
    $intentos = 0;
    $abierta  = false;
    $mensaje  = 'Caja restablecida.';
}

// ----------------------
// 2) PROCESAR FORMULARIO
// ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['reiniciar'])) {

    if ($abierta) {
        $mensaje = 'La caja fuerte ya estaba abierta.';
    } elseif ($intentos >= $MAX_INTENTOS) {
        $error = 'Has agotado todas las oportunidades.';
    } else {
        $entrada = trim($_POST['combinacion'] ?? '');

        if ($entrada === '' || !preg_match('/^\d{4}$/', $entrada)) {
            $error = 'Formato inválido. Introduce exactamente 4 cifras.';
        } else {
            $intentos++;

            if ($entrada === $COMBINACION) {
                $abierta = true;
                $mensaje = 'La caja fuerte se ha abierto correctamente.';
            } else {
                $restantes = $MAX_INTENTOS - $intentos;
                if ($restantes > 0) {
                    $error = "No es la combinación. Te quedan {$restantes} intento(s).";
                } else {
                    $error = "No es la combinación. Has agotado los {$MAX_INTENTOS} intentos.";
                }
            }
        }
    }
}

// ----------------------
// 3) BANDERAS PARA LA VISTA
// ----------------------
$mostrarFormulario = (!$abierta && $intentos < $MAX_INTENTOS);
$mostrarExito      = $abierta;
$mostrarBloqueo    = (!$abierta && $intentos >= $MAX_INTENTOS);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Caja fuerte (hidden simple)</title>
  <style>
    body { font-family: sans-serif; max-width: 600px; margin: 20px auto; line-height: 1.4; }
    .ok { color: green; }
    .error { color: red; }
    .panel { border: 1px solid #ccc; padding: 10px; margin: 10px 0; border-radius: 6px; }
    button { padding: 6px 10px; cursor: pointer; }
  </style>
</head>
<body>
  <h2>Control de acceso - Caja fuerte</h2>

  <?php
  if ($mensaje !== '') {
      echo '<div class="panel ok">'.htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8').'</div>';
  }

  if ($error !== '') {
      echo '<div class="panel error">'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</div>';
  }

  if ($mostrarExito) {
      echo '<p><strong>Acceso concedido.</strong></p>';
  }

  if ($mostrarBloqueo) {
      echo '<p><strong>Has agotado las oportunidades.</strong></p>';
  }

  if ($mostrarFormulario) {
      echo '
      <form method="post" class="panel" autocomplete="off">
        <label>Combinación (4 cifras):
          <input name="combinacion" type="text" maxlength="4" pattern="\d{4}" required>
        </label>
        <input type="hidden" name="intentos" value="'.(int)$intentos.'">
        <input type="hidden" name="abierta" value="'.($abierta ? '1' : '0').'">
        <button type="submit">Abrir</button>
      </form>';
  }
  ?>

  <form method="post" style="margin-top:10px;">
    <input type="hidden" name="intentos" value="0">
    <input type="hidden" name="abierta" value="0">
    <button type="submit" name="reiniciar">Reiniciar</button>
  </form>

  <p>Intentos usados: <?= (int)$intentos ?> / <?= (int)$MAX_INTENTOS ?></p>

  <p class="panel">
    <strong>Nota didáctica:</strong> Esta versión es <em>insegura</em> porque los campos hidden 
    pueden ser manipulados por el usuario.  
    En la práctica, se debería guardar el estado en el servidor.
  </p>
</body>
</html>
