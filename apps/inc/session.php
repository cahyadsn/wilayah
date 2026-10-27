<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : session.php
purpose  : secure and centralized session configuration
create   : 2026-08-19
last edit: 2026-10-27 17:40:00
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

// Defense-in-depth against PHP2Shell (CVE-2026-109618 & CVE-2026-109619) &
// Brotli Decoder Buffer Overflow / Node2Shell (CVE-2026-111497):
// 1. Disable session upload progress and file uploads at runtime
@ini_set('session.upload_progress.enabled', '0');
@ini_set('file_uploads', '0');

// 2. Reject anomalous requests containing PHP_SESSION_UPLOAD_PROGRESS, unexpected file uploads, or compressed request bodies
if (!function_exists('is_php2shell_request')) {
    /**
     * Detect anomalous upload progress, multipart payloads, or compressed bodies
     * to mitigate PHP2Shell (CVE-2026-109618 & CVE-2026-109619) and
     * Brotli decoder buffer overflow (CVE-2026-111497) vulnerability vectors.
     */
    function is_php2shell_request(): bool {
        if (!empty($_FILES)) {
            return true;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if (is_string($contentType) && stripos($contentType, 'multipart/form-data') !== false) {
            return true;
        }

        // Mitigate Brotli / compressed payload buffer overflow (CVE-2026-111497 / Node2Shell)
        $contentEncoding = $_SERVER['HTTP_CONTENT_ENCODING'] ?? $_SERVER['CONTENT_ENCODING'] ?? '';
        if (is_string($contentEncoding) && preg_match('/(?:br|brotli)/i', $contentEncoding)) {
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
