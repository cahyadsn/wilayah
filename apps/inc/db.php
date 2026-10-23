<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : db.php
purpose  : configuration of database connection
create   : 170912
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

copyright (c) 2015-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
// PHP2Shell (CVE-2026-109618 & CVE-2026-109619) Defense-in-depth:
@ini_set('session.upload_progress.enabled', '0');
@ini_set('file_uploads', '0');

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
$env_file = file_exists(__DIR__ . '/../.env') ? __DIR__ . '/../.env' : (file_exists(__DIR__ . '/../../.env') ? __DIR__ . '/../../.env' : null);
if ($env_file && is_readable($env_file)) {
    $env_lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            $len = strlen($val);
            if ($len >= 2 && (($val[0] === '"' && $val[$len - 1] === '"') || ($val[0] === "'" && $val[$len - 1] === "'"))) {
                $val = substr($val, 1, -1);
            }
            if (getenv($key) === false && !isset($_SERVER[$key]) && !isset($_ENV[$key])) {
                putenv("$key=$val");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }
}
$dbhost = getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'localhost';
$dbuser = getenv('DB_USER') !== false ? getenv('DB_USER') : '';
$dbpass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$dbname = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'wilayah';
$db_dsn = "mysql:dbname=$dbname;host=$dbhost";
$tbl_wilayah="wilayah_level_1_2";
$tbl_pulau="wilayah_pulau";
try {
  $db = new PDO($db_dsn, $dbuser, $dbpass);
} catch (PDOException $e) {
  error_log('Connection failed: ' . $e->getMessage());
  echo 'Connection failed: Database error occurred.';
}
