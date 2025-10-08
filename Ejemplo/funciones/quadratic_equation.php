<?php

//Realiza un programa que resuelva una ecuación de segundo grado (del tipo ax^2 + bx + c = 0).

function solveQuadraticEquation($a, $b, $c) {
    $delta = $b * $b - 4 * $a * $c;

    if ($delta < 0) {
        return "No hay soluciones reales";
    } elseif ($delta == 0) {
        $x = -$b / (2 * $a);
        return "Hay una solución real: x = " . $x;
    } else {
        $x1 = (-$b + sqrt($delta)) / (2 * $a);
        $x2 = (-$b - sqrt($delta)) / (2 * $a);
        return "Hay dos soluciones reales: x1 = " . $x1 . ", x2 = " . $x2;
    }
}

$a = 1;
$b = -3;
$c = 2;

$result = solveQuadraticEquation($a, $b, $c);
echo $result;

?>
