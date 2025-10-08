# PHP – Chuleta Básica (PHP 8+)

> Resumen rápido de sintaxis, operadores, arrays, funciones, POO, ficheros, HTTP, MySQL (mysqli/PDO), fechas, regex y filtros. Pensada para UD1 y UD2.
> Manual oficial de PHP: https://www.php.net/manual/es/

## Índice
- [Incluir PHP](#incluir-php)
- [Comentarios](#comentarios)
- [Convenciones de nombres](#convenciones-de-nombres)
- [Salida básica](#salida-básica)
- [Variables](#variables)
  - [Superglobales](#superglobales)
  - [Funciones útiles](#funciones-útiles)
  - [Constantes](#constantes)
- [Operadores](#operadores)
- [Cadenas (strings)](#cadenas-strings)
- [Números](#números)
- [Condicionales](#condicionales)
- [Bucles](#bucles)
- [Arrays](#arrays)
- [Funciones](#funciones)
- [Ficheros](#ficheros)
- [Errores y excepciones](#errores-y-excepciones)
- [POO](#poo)
- [HTTP y cookies](#http-y-cookies)
- [MySQL](#mysql)
- [Fecha y hora](#fecha-y-hora)
- [Expresiones regulares](#expresiones-regulares)
- [Filtros](#filtros)
- [Buenas prácticas](#buenas-prácticas)
- [Formularios](#formularios)
---

## Incluir PHP
```php
<?php
  // tu código PHP aquí
?>
```

## Comentarios
```php
// una línea
# otra forma de una línea
/* varias
   líneas */
```

## Convenciones de nombres
```php
$firstName = 'Mike';        // variables (camelCase)
function updateProduct() {}  // funciones (camelCase)
class ProductItem {}         // clases (PascalCase)
const ACCESS_KEY = '123abc'; // constantes (MAYÚSCULAS_CON_GUIONES_BAJOS)
```

## Salida básica
```php
echo 'Hola PHP';
var_dump($variable); // debug
print_r($productos); // debug
```

## Variables
```php
$name = 'Joe';      // string
$isActive = false;  // boolean
$number = 32;       // int
$amount = 91.90;    // float
```
### Superglobales
- `$GLOBALS`, `$_SERVER`, `$_GET`, `$_POST`, `$_REQUEST`

### Funciones útiles
`boolval`, `intval`, `floatval`, `strval`, `gettype`, `is_*`, `isset`, `empty`, `unset`,  
`print_r`, `var_dump`, `var_export`, `serialize`/`unserialize`

### Constantes
```php
define('APP_ENV','prod');
const VERSION = '1.0.0';
```

---

## Operadores
**Aritméticos**: `+ - * / % **`  
**Asignación**: `+= -= *= /= %=`  
**Comparación**: `== === != <> !== < > <= >= <=>`  
**Lógicos**: `and or xor ! && ||`  
**Bit a bit**: `& | ^ ~ << >>`  
**Strings**: `.` y `.=`  
**Incremento/Decremento**: `++$v $v++ --$v $v--`  
**Supresión de errores**: `@expr` (evitar)  
**Ejecución shell**: `` `cmd` `` (peligroso)

---

## Cadenas (strings)
- Simples `'...'` (sin interpolación)
- Dobles `"..."` (interpolación y escapes)
- heredoc `<<<ID ... ID;`
- nowdoc `<<<'ID' ... ID;`

```php
echo 'Hola ' . $name;
echo "Hola $name\n";
echo strlen($name);
echo trim($text);
echo strtolower($email);
echo strtoupper($name);
echo ucfirst($name);
echo str_replace('c', 'd', $text);
echo str_contains($name,'oe'); // PHP 8
```

Escapes: `\\ \n \r \t \$ \" \' \xHH \u{HHHH}`

Funciones comunes: `explode`, `implode`, `printf/sprintf`, `strlen`, `substr`, `strpos/stripos`,  
`str_replace/str_ireplace`, `trim/ltrim/rtrim`, `strtolower/strtoupper/ucfirst/ucwords`,  
`str_contains`, `nl2br`, `htmlspecialchars/htmlentities`, `strip_tags`

---

## Números
```php
is_numeric('12.99'); // true
round(0.75);         // 1
rand(10,100);        // ej. 32 (mejor random_int() si necesitas seguridad)
```

---

## Condicionales
```php
echo $isValid ? 'ok' : 'ko';
echo $name ?? 'Anónimo'; // fusión nula
$name ??= 'Anónimo';     // asignación por fusión nula

echo $user?->profile?->activate() ?? 'N/A'; // null-safe

if ($n === 10) { ... } elseif ($n === 20) { ... } else { ... }

switch ($c) {
  case 1: ...; break;
  default: ...;
}

$type = match ($color) {
  'red' => 'danger',
  'yellow','orange' => 'warning',
  'green' => 'success',
  default => 'unknown'
};
```

---

## Bucles
```php
for ($i=0; $i<10; $i++) { echo $i; }

$n=1;
while ($n<3) { echo $n++; }

$n=1;
do { echo $n++; } while ($n<3);

foreach (['a','b','c'] as $v) {
  if ($v==='b') continue;
  echo $v;
}
```

---

## Arrays
```php
$names = ['Joe','James','Peter'];
$array = array('Joe','James','Peter');

$names[] = 'Jessie';
echo $names[1]; // James
echo implode(', ', $names);

$person = ['age'=>32, 'gender'=>'female'];
$person['name'] = 'Amanda';

foreach ($person as $k=>$v) { echo "$k: $v"; }

$items = [
  ['id'=>'100','name'=>'product 1'],
  ['id'=>'200','name'=>'product 2'],
];
$idx = array_search('product 2', array_column($items,'name'));
```
**Funciones**: `array_key_exists`, `in_array`, `array_keys`, `array_values`,  
`array_merge`, `array_diff`, `array_intersect`, `array_filter`, `array_map`, `array_reduce`,  
`push/pop`, `shift/unshift`, `slice`, `splice`, `sum`, `sort/rsort`, `asort/arsort`, `ksort/krsort`, `usort`, `shuffle`, `range`

---
# ⚙️ Funciones útiles de Arrays en PHP

A continuación, se muestran algunas funciones frecuentes con ejemplos prácticos.

---

## 🔍 Búsqueda y claves

```php
$person = ['name' => 'Ana', 'age' => 25];

// Verificar si una clave existe
if (array_key_exists('age', $person)) {
    echo "La clave 'age' existe.<br>";
}

// Verificar si un valor existe
if (in_array('Ana', $person)) {
    echo "El valor 'Ana' está en el array.<br>";
}

// Obtener todas las claves y valores
print_r(array_keys($person));   // ['name', 'age']
print_r(array_values($person)); // ['Ana', 25]
```

---

## 🔗 Combinación y diferencias

```php
$a = ['red', 'green', 'blue'];
$b = ['green', 'yellow'];

print_r(array_merge($a, $b));      // une arrays
print_r(array_diff($a, $b));       // ['red', 'blue']
print_r(array_intersect($a, $b));  // ['green']
```

---

## 🧠 Transformación de datos

```php
$nums = [1, 2, 3, 4, 5];

$dobles = array_map(fn($n) => $n * 2, $nums);   // [2, 4, 6, 8, 10]
$pares = array_filter($nums, fn($n) => $n % 2 === 0); // [2, 4]
$suma = array_reduce($nums, fn($carry, $n) => $carry + $n, 0); // 15
```

---

## 📦 Modificación del array

```php
$stack = [1, 2, 3];

array_push($stack, 4); // añade al final
array_pop($stack);     // elimina el último
array_unshift($stack, 0); // añade al principio
array_shift($stack);      // elimina el primero

print_r($stack);
```

---

## ✂️ Cortes, sumas y ordenación

```php
$nums = [10, 20, 30, 40, 50];

print_r(array_slice($nums, 1, 3)); // [20, 30, 40]
array_splice($nums, 2, 1, [99]);   // reemplaza posición 2 por 99

echo array_sum($nums); // suma total

sort($nums);  // orden ascendente
rsort($nums); // descendente
```

---

## 🔠 Ordenación avanzada

```php
$people = ['John' => 30, 'Ana' => 25, 'Mark' => 35];

asort($people); // por valor ascendente
ksort($people); // por clave ascendente
arsort($people); // por valor descendente
krsort($people); // por clave descendente
```

---

## 🧩 Ordenación personalizada y aleatoria

```php
$numbers = [3, 1, 4, 2];

usort($numbers, fn($a, $b) => $a <=> $b); // orden numérico
shuffle($numbers); // orden aleatorio

print_r($numbers);
```

---

## 📈 Crear rangos

```php
$range = range(1, 5); // [1, 2, 3, 4, 5]
print_r($range);
```

## Funciones
```php
function fullName(string $first='John', string $last='Doe'): string {
  return "$first $last";
}
fullName('Ada','Lovelace');

// con nombre de parámetros (PHP 8)
fullName(last:'Doe', first:'John');

// variádicas
function joinWords(string ...$parts): string { return implode(' ', $parts); }

// flecha
$double = fn (int $n) => $n*2;
```

---

## Ficheros
```php
// lectura
$f = fopen("foo.txt","r");
while (!feof($f)) echo fgets($f);
fclose($f);

// escritura CSV
$f = fopen('test.csv','a');
$rows = [['name'=>'Mike','age'=>45],['name'=>'Ana','age'=>30]];
fputcsv($f, array_keys($rows[0]));
for each ($rows as $r) fputcsv($f, $r); // <-- reemplaza por foreach en tu editor
fclose($f);
```

---

## Errores y excepciones
```php
try {
  if ($invalid) throw new Exception('Error');
} catch (Exception $e) {
  error_log($e->getMessage());
}
```
**Funciones**: `error_reporting`, `error_log`, `error_get_last`, `set_error_handler`, `set_exception_handler`, `debug_backtrace`  
**Constantes**: `E_ERROR`, `E_WARNING`, `E_PARSE`, `E_NOTICE`, `E_DEPRECATED`, `E_USER_*`, `E_ALL`

---

## POO
```php
class User {
  public function __construct(
    protected string $userName,
    protected int $userId
  ) {}
  public static function create(...$p): self { return new self(...$p); }
}

class Admin extends User {
  public function greet(): void { echo "Hola, admin {$this->userName}"; }
}

trait HelloWorld { public function sayHello(){ echo 'Hello'; } }
interface Animal { public function makeSound(); }
class Dog implements Animal { public function makeSound(){ echo 'guau'; } }
```

---

## HTTP y cookies
```php
header('Location: /home'); exit;               // redirección
setcookie('token','abc', ['httponly'=>true,'secure'=>true,'samesite'=>'Lax']);
```

---

## MySQL
> Para proyectos nuevos, valora **PDO** por su soporte multibase y prepared statements.

**mysqli (prep. statements)**:
```php
$mysqli = new mysqli('localhost','user','pass','db');
if ($mysqli->connect_errno) die($mysqli->connect_error);

$stmt = $mysqli->prepare('SELECT id,name FROM products WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();# Formularios en PHP 
while ($row = $res->fetch_assoc()) print_r($row);
```

**PDO (recomendado)**:
```php
$pdo = new PDO('mysql:host=localhost;dbname=db;charset=utf8mb4','user','pass', [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
$stmt = $pdo->prepare('SELECT id,name FROM products WHERE id = :id');
$stmt->execute([':id' => $id]);
foreach ($stmt as $row) print_r($row);
```

---

## Fecha y hora
```php
date_default_timezone_set('Europe/Madrid');
echo date('Y-m-d H:i:s');

$dt = new DateTime('now');
$dt->modify('+2 days');
echo $dt->format(DateTime::ATOM);
```
Formato `date`: `d m Y H i s`, etc.

---

## Expresiones regulares
```php
$expr = "/pattern/i";
preg_match($expr, $str);
preg_match_all($expr, $str);
preg_replace($expr, 'X', $str);
```
Modificadores: `i`, `m`, `u` — Metas: `| . ^ $ \d \s \b` — Cuant.: `+ * ? {x} {x,y} {x,}`

---

## Filtros
Funciones: `filter_var`, `filter_input`, `filter_var_array`, `filter_input_array`  
Validación: `FILTER_VALIDATE_EMAIL`, `*_INT`, `*_FLOAT`, `*_IP`, `*_URL`, `*_REGEXP`  
Sanitización: `FILTER_SANITIZE_EMAIL`, `*_NUMBER_INT`, `*_NUMBER_FLOAT`, `*_SPECIAL_CHARS`, `*_FULL_SPECIAL_CHARS`

---

## Formularios
### Lectura rápida y nulos
```php
// Llega o no llega
$nombre = $_POST['nombre'] ?? null;      // null si no existe
$nombre = trim($nombre ?? '');           // '' si era null

// Comprobaciones típicas
if ($nombre === '') { /* obligatorio */ }
if (!isset($_POST['email'])) { /* no enviado */ }
if (empty($_POST['comentario'])) { /* vacío o no enviado */ }
if (is_null($_POST['edad'] ?? null)) { /* faltó edad */ }

// Diferencias
// isset: false si no existe o es null
// empty: true si falsy ('', 0, '0', [], false, null) o no existe
// is_null: solo null estricto
```

## Validación con filtros
```php
// Unitario con filter_var
$email = $_POST['email'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  /* email no válido */
}

// Directo desde POST con filter_input
$edad = filter_input(INPUT_POST, 'edad', FILTER_VALIDATE_INT, [
  'options' => ['min_range'=>1, 'max_range'=>120]
]);

// Masivo con filter_input_array
$datos = filter_input_array(INPUT_POST, [
  'nombre' => ['filter'=>FILTER_SANITIZE_SPECIAL_CHARS],
  'email'  => ['filter'=>FILTER_VALIDATE_EMAIL],
  'edad'   => ['filter'=>FILTER_VALIDATE_INT, 'options'=>['min_range'=>1,'max_range'=>120]],
  'web'    => ['filter'=>FILTER_VALIDATE_URL, 'flags'=>FILTER_NULL_ON_FAILURE],
]);
if ($datos['email'] === false) { /* email inválido */ }
```

## Patrón básico de validación
```php
$errores = [];

$nombre = trim($_POST['nombre'] ?? '');
if ($nombre === '') $errores['nombre'] = 'El nombre es obligatorio';

$email = $_POST['email'] ?? null;
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errores['email'] = 'Email no válido';
}

$pais = $_POST['pais'] ?? '';
$permitidos = ['ES','FR','IT'];
if (!in_array($pais, $permitidos, true)) {
  $errores['pais'] = 'País no válido';
}

$acepto = isset($_POST['acepto']); // checkbox -> bool

if ($errores) {
  // devolver a la vista con errores y old()
} else {
  // procesar
}
```

## Helpers útiles (copiar/pegar)
```php
// input(): get con filtro y por defecto
function input(string $key, $default=null, int $filter=FILTER_UNSAFE_RAW, int $type=INPUT_POST) {
  $v = filter_input($type, $key, $filter);
  return $v !== null ? $v : $default;
}

// old(): repoblar campos tras error (escapado seguro)
function old(string $key, $default=''): string {
  return htmlspecialchars((string)($_POST[$key] ?? $default), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// has_error(): marcar campo con error
function has_error(array $errors, string $key): bool {
  return array_key_exists($key, $errors);
}

// e(): alias de escape
function e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
```

## Subida de archivos (mínimo seguro)
```php
if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
  $tmp  = $_FILES['archivo']['tmp_name'];
  $size = $_FILES['archivo']['size'];

  if ($size > 2 * 1024 * 1024) die('Archivo demasiado grande'); // 2 MB

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime  = $finfo->file($tmp);
  if (!in_array($mime, ['image/jpeg','image/png','application/pdf'], true)) {
    die('Tipo no permitido');
  }

  $ext = match ($mime) {
    'image/jpeg'=>'jpg','image/png'=>'png','application/pdf'=>'pdf', default=>'bin'
  };
  $dest = __DIR__.'/uploads/'.bin2hex(random_bytes(16)).'.'.$ext;
  if (!move_uploaded_file($tmp, $dest)) die('Error guardando archivo');
}
```

## Mini recordatorio (relacionado con *Filtros*)
- **Validación**: `FILTER_VALIDATE_EMAIL`, `*_INT`, `*_FLOAT`, `*_IP`, `*_URL`, `*_REGEXP`  
- **Sanitización**: `FILTER_SANITIZE_EMAIL`, `*_NUMBER_INT`, `*_NUMBER_FLOAT`, `*_SPECIAL_CHARS`, `*_FULL_SPECIAL_CHARS`  

> Tip: usa `??` y `??=` para manejar nulos cómodamente, y `?->` (null-safe) en encadenados.

## Buenas prácticas
- `error_reporting(E_ALL)` en desarrollo; no mostrar errores en producción.
- Valida/escapa **todo** input; usa **prepared statements**.
- Evita `@` y funciones obsoletas.
- Usa **Composer** para dependencias y **Git** para versionado.
