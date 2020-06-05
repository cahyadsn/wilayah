<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_wilayah.php
purpose  :
create   : 2017/09/12
last edit: 200605,190830
author   : cahya dsn
================================================================================
This program is free software; you can redistribute it and/or modify it under the
terms of the GNU General Public License as published by the Free Software
Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

copyright (c) 2015-2020 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
$dbhost='localhost';
$dbuser='root';
$dbpass='';
$dbname='wilayah';
$db_dsn = "mysql:dbname=$dbname;host=$dbhost";
$tbl_wilayah="wilayah_level_1_2";
try {
  $db = new PDO($db_dsn, $dbuser, $dbpass);
} catch (PDOException $e) {
  echo 'Connection failed: '.$e->getMessage();
}