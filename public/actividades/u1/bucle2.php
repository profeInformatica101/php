<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejercicio bucles 2</title>
</head>
<body>

<?php 
/*
Mostrar múltiplos de 5 (0–100) con un bucle while.
*/
echo "Los múltiplos de 5 son: ";
    while($i<100){
        if($i%5 == 0){
           
            echo "<mark>$i</mark> <br>";
        }
        $i++;
    }
?>
    
</body>
</html>