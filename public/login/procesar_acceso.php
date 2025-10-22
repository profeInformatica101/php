<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

/**
 * Procesa login/logout (demo educativa SIN CSRF)
 * ----------------------------------------------
 * - Código simple para clase. No apto para producción.
 * - Estructura de datos clara y sesión unificada.
 */

session_start();

// --------- ESTRUCTURA DE DATOS ----------
// Las constantes no llevan $
const USUARIOS = [
    //user    
    'admin' => ['password' => 'admin', 'nombre' => 'Administrador', 'rol' => 'admin'],
    'test'  => ['password' => 'test',  'nombre' => 'María',         'rol' => 'user'],
 'test2'  => ['password' => 'test2',  'nombre' => 'Juan',         'rol' => 'user'],
];
// Al autenticarnos guardaremos:
// $_SESSION['auth'] = ['usuario'=>'admin','nombre'=>'Administrador','rol'=>'admin'];

// ------------------------- HELPERS ---------------------------
function redirect(string $to = 'index.php'): void {
    header("Location: {$to}"); // pide al navegador que cargue la página pasada por parámetros
    exit; //garantiza que el script termine inmediatamente en el Servidor
}

function login(array $usuarios): void {
    $user = trim((string)($_POST['usuario'] ?? ''));
    $pass = (string)($_POST['credencial'] ?? '');
                                                //bool hash_equals(string $cadena1, string $cadena2)
    if ($user !== '' && isset($usuarios[$user]) && hash_equals($usuarios[$user]['password'], $pass)) {
        session_regenerate_id(true); // se crea un nuevo ID de sesión y se invalida el antiguo. Eso evita que un atacante que conociera (o fijara) previamente el ID de sesión del cliente pueda reutilizarlo después del login.
        $_SESSION['auth'] = [
            'usuario' => $user,
            'nombre'  => $usuarios[$user]['nombre'],
            'rol'     => $usuarios[$user]['rol'],
        ];
    }
    redirect();
}

function logout(): void {
    session_destroy();
    redirect();
}

// --------------------- CONTROLADOR BÁSICO --------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect();
}

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'login':
        login(USUARIOS);
        break;

    case 'logout':
        logout();
        break;

    default:
        redirect();
}
