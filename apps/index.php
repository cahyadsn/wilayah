<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename    : index.php
purpose     : main application page
create      : 150702
last edit   : 2024-02-24 09:44:14
author   	: cahya dsn
demo site 	: https://wilayah.cahyadsn.com/v2
soure code 	: https://github.com/cahyadsn/wilayah/apps
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

copyright (c) 2015-2024 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
session_start();
$c=isset($_SESSION['c'])?$_SESSION['c']:(isset($_GET['c'])?$_GET['c']:'indigo');
define("_AUTHOR","cahyadsn");
$_SESSION['author']='cahyadsn';
$_SESSION['ver']=sha1(rand());
include 'inc/db.php';
$version='2.5';
/*header('Expires: '.date('r'));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');*/
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <title>Data Wilayah v <?php echo $version;?></title>
    <meta charset="utf-8" />
    <meta http-equiv="expires" content="<?php echo date('r');?>" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta http-equiv="content-language" content="en" />
    <meta name="author" content="Cahya DSN" />
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
    <meta name="keywords" content="php, mysql, data, administrasi, wilayah, indonesia, permendagri, 58, 2021, cahyadsn" />
    <meta name="description" content="Wilayah ver <?php echo $version;?> created by cahya dsn, Data wilayah administrasi Indonesia sesuai permendagri No 72 tahun 2019, dalam bahasa pemrograman PHP dan database MySQL" />
    <meta name="robots" content="index, follow" />
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/w3.css">
    <link rel="stylesheet" href="css/w3-theme-<?php echo $c;?>.css" media="all" id="wil_css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
    <link rel="stylesheet" href="css/wilayah.php?v=<?php echo md5(filemtime('css/wilayah.php'));?>">
	<script src='https://code.jquery.com/jquery-3.6.4.min.js'></script>
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
		integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
		crossorigin=""/>
	<link type="text/css" rel="stylesheet" href="https://unpkg.com/leaflet-draw@0.4.13/dist/leaflet.draw.css"/>
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
		integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
		crossorigin=""></script>
	<script src="https://unpkg.com/leaflet-draw@0.4.13/dist/leaflet.draw.js"></script>
</head>
<body>
    <div class="w3-top">
        <div class="w3-bar w3-theme-d5">
            <span class="w3-bar-item"># Wilayah v<?php echo $version;?></span>
            <div class="w3-dropdown-hover">
                <button class="w3-button">Themes</button>
                <div class="w3-dropdown-content w3-white w3-card-4" id="theme">
                    <?php
                    $color=array("black","brown","pink","orange","amber","lime","green","teal","purple","indigo","blue","cyan");
                    foreach($color as $c){
                        echo "<a href='#' class='w3-bar-item w3-button w3-{$c} color' data-value='{$c}'> </a>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="w3-container">
        <div class="w3-card-4">
            <h2>&nbsp;</h2>
            <div class="w3-panel w3-bar w3-theme-d1">
                <h3 class="w3-theme-d1">Data Wilayah Administrasi Indonesia</h3>
                <h4 class="w3-theme-d1"> Sesuai Permendagri No 58 th 2021</h4>
            </div>
            <div class="w3-container">
              <div class="w3-row">
                <div class="w3-col m4 w3-padding">
                  <div id="msg_box"></div>
                  <div id="preload" class="w3-bar w3-center"><img src="img/preload.svg"></div>
                </div>
              </div>
              <div class="w3-row">
                <div class="w3-col m6 w3-padding">
                  <label class="w3-col s6 m3">Pilih Provinsi</label>
                  <div class="w3-col s6 m3">
                    <select name="prop" id="prop" class="w3-select w3-hover-theme" onchange="ajax(this.value)" readonly>
                      <option value="">Pilih Provinsi</option>
                      <?php
                      $query=$db->prepare("SELECT kode,nama FROM wilayah_1_2 WHERE CHAR_LENGTH(kode)=2 ORDER BY nama");
                      $query->execute();
                      while ($data=$query->fetchObject()){
                        echo '<option value="'.$data->kode.'">'.$data->nama.'</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="w3-col m6 w3-padding" id="kab_box">
                  <label class="w3-col s6 m3">Pilih Kota/Kab</label>
                  <div class="w3-col s6 m3">
                    <select name="kota" id="kota" class="w3-select w3-hover-theme" onchange="ajax(this.value)" readonly>
                      <option value="">Pilih Kota</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
        </div>
        <div id="map-canvas"></div>
        <div class="w3-theme-d5 w3-padding">source code : <a href='https://github.com/cahyadsn/wilayah'>https://github.com/cahyadsn/wilayah</a></div>
    </div>
    <div class="w3-bottom">
        <div class="w3-bar w3-theme-d4 w3-center w3-padding">
            Wilayah v<?php echo $version;?> copyright &copy; 2017<?php echo (date('Y')>2017?date('-Y'):'');?> by <a href='mailto:cahyadsn@gmail.com'>cahya dsn</a><br />
        </div>
    </div>
    <script src="inc/geo_js.php?v=<?php echo $_SESSION['ver'];?>"></script>
    <script src="js/wilayah.js?id=<?php echo MD5(date('YmdHis'))?>"></script>
</body>
</html>