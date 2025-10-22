<?php
/**
* Funciones de plantilla simples
*/

function generarPaginaHTML(string $titulo, string $contenido): string {
$res = "<!DOCTYPE html>\n"
. "<html lang=\"es\">\n"
. "<head>\n"
. " <meta charset=\"UTF-8\">\n"
. " <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n"
. " <title>" . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . "</title>\n"
. " <style>\n"
. " body{font-family: system-ui, Segoe UI, Roboto, Arial; max-width:720px; margin:2rem auto; padding:0 1rem;}\n"
. " form{margin-top:1rem; padding:1rem; border:1px solid #ddd; border-radius:12px;}\n"
. " label{display:block; margin:.5rem 0 .25rem;}\n"
. " input[type=text], input[type=password]{width:100%; padding:.5rem;}\n"
. " input[type=submit]{margin-top:1rem; padding:.5rem 1rem; cursor:pointer;}\n"
. " </style>\n"
. "</head>\n"
. "<body>\n"
. $contenido
. "<hr>"
. "</body>\n"
. "</html>";
return $res;
}
/**
 * # FORM LOGIN
 * acción --> procesar_acceso.php
 * input:hidden --> value='login' - name='accion'
 * input:text      --> name='usuario'
 * input:password  --> name='credencial'
 */

function generarFormularioLogin(): string {
return "\n <h1>Iniciar sesión</h1>\n <form method=\"POST\" action=\"procesar_acceso.php\">\n <input type=\"hidden\" name=\"accion\" value=\"login\">\n <label for=\"usuario\">Usuario</label>\n <input id=\"usuario\" type=\"text\" name=\"usuario\" required>\n <label for=\"credencial\">Contraseña</label>\n <input id=\"credencial\" type=\"password\" name=\"credencial\" required>\n <input type=\"submit\" value=\"Iniciar sesión\">\n </form>\n <details style=\"margin-top:1rem;\"><summary>Credenciales de ejemplo</summary>\n <p>admin / admin &nbsp;·&nbsp; test / test</p>\n </details>";
}

/**
 * # LOGOUT
 * acción --> procesar_acceso.php
 * input:hidden --> value='logout' - name='accion'
 */
function generarLogout(): string {
return "\n <form method=\"POST\" action=\"procesar_acceso.php\">\n <input type=\"hidden\" name=\"accion\" value=\"logout\">\n <input type=\"submit\" value=\"Cerrar sesión\">\n </form>";
}
?>