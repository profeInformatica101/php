<?php
require_once 'src/funciones.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba</title>
</head>

<body>
    <h2><?= saludar("Pablo"); ?></h2>

    <?php
    $color = "rojo";
    $nombre = "maría";

    swap($color, $nombre);

    echo "Color $color";
    echo "Nombre $nombre";

    ?>


</body>

</html>