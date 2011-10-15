/* Comun.js
 *
 * Funciones comunes para eliminar y preguntar cosas
 *
 */

function confirmarDrop(enlace, mensaje) {
    // Confirmation is not required in the configuration file
    // or browser is Opera (crappy js implementation)
    if (mensaje == '' || typeof(window.opera) != 'undefined') {
        return true;
    }

    var is_confirmed = confirm(mensaje + '\n' + 'Esta acción no se puede deshacer');
    if (is_confirmed) {
        enlace.href += '&confirmado_js=1';
    }

    return is_confirmed;
}
