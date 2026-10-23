<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : session.php
purpose  : secure and centralized session configuration
create   : 2026-08-19
last edit: 2026-10-23 15:00:00
author   : cahya dsn
================================================================================
This program is free software; you can redistribute it and/or modify it under the
terms of the MIT License.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

See the MIT License for more details

copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/

// PHP2Shell (CVE-2026-109618 & CVE-2026-109619) Defense-in-depth:
// 1. Disable session upload progress and file uploads at runtime
@ini_set('session.upload_progress.enabled', '0');
@ini_set('file_uploads', '0');

// 2. Reject anomalous requests containing PHP_SESSION_UPLOAD_PROGRESS or unexpected file uploads
if (!function_exists('is_php2shell_request')) {
    /**
     * Detect anomalous upload progress or multipart payloads to mitigate
     * PHP2Shell vulnerability vectors (CVE-2026-109618 & CVE-2026-109619).
     */
    function is_php2shell_request(): bool {
        if (!empty($_FILES)) {
            return true;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if (is_string($contentType) && stripos($contentType, 'multipart/form-data') !== false) {
            return true;
        }

        foreach ([$_POST, $_GET, $_COOKIE, $_REQUEST] as $bag) {
            if (!is_array($bag)) {
                continue;
            }
            if (isset($bag['PHP_SESSION_UPLOAD_PROGRESS'])) {
                return true;
            }
            foreach (array_keys($bag) as $key) {
                if (is_string($key) && stripos($key, 'upload_progress') !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (is_php2shell_request()) {
    if (!headers_sent()) {
        http_response_code(400);
    }
    exit('Bad Request');
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

if (!function_exists('get_csrf_token_html')) {
    function get_csrf_token_html() {
        return htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
    }
}
