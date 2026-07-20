<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : db.php
purpose  : configuration of database connection
create   : 170912
last edit: 2026-07-15 00:25:29
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
$dbhost = getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'localhost';
$dbuser = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
$dbpass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$dbname = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'wilayah';
$db_dsn = "mysql:dbname=$dbname;host=$dbhost";
$tbl_wilayah="wilayah_level_1_2";
try {
  $db = new PDO($db_dsn, $dbuser, $dbpass);
} catch (PDOException $e) {
  error_log('Connection failed: ' . $e->getMessage());
  echo 'Connection failed: Database error occurred.';
}
