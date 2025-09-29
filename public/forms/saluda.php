<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejercicio 7 bucles. Caja fuerte</title>
</head>
<body>
    <?php 
    /*
    Realiza el control de aceso a una caja fuerte. La combinación será un número de 4 cifras. 
    El programa nos pedirá la combinación para abrirla. Si no acertamos, se nos mostrará el mensaje 
    "Lo siento,esa no es la combinación" 
    y si acertamos se nos dirá.
    "La caja fuerte se ha abierto satisfactoriamente". Tendremos cuatro oportunidades para abrir la caja fuerte 
    */
    $MAX_INTENTOS=4;
    $CODE=$_GET['password'];

    do{
        if($code=="1234"){
            echo "<b>La caja fuerte se ha abierto satisfactoriamente</b>";
        }else{
          echo "<i>Lo siento,esa no es la combinación</i> <br> Le quedan $cont intentos";
          $cont--; 
        }

    }while($cont!=0 && $code !="1234");


    ?>
</body>
</html>