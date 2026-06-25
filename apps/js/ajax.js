/* ============================================================
   Shared AJAX helper
   ============================================================ */
function do_ajax() {
    if (window.XMLHttpRequest) return new XMLHttpRequest();
    if (window.ActiveXObject) return new ActiveXObject("Microsoft.XMLHTTP");
    return null;
}
