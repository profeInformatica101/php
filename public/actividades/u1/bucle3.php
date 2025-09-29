<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejercicio bucles 3</title>
</head>
<body>
    <?php
    /*
    Mostrar múltiplos de 5 (0–100) con un bucle do-while.
    */
    $i=1;
    do{
        if($i%5 == 0){
            echo "<hr>$i</hr> <br>";
        }
        $i++;
    }while($i<100);
    ?>

</body>
</html>