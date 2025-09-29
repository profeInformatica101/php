<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejercicio bucles 1</title>
</head>
<body>
    <?php 
    /*
    Mostrar múltiplos de 5 (0–100) con un bucle for.
    */
    echo "Los números múltiplos de 5 del 1 al 100 son: <br>";
        for($i=1; $i<100;$i++){
            if($i%5 == 0){
                
                echo "<b>$i</b> <br>"  ;
            }
        }   
         ?>

    
</body>
</html>