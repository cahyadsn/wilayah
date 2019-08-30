<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : index.php
purpose  :
create   : 2015/07/02
last edit: 190830,170928,170912,170503,170426,170419,170328,150702
author   : cahya dsn
================================================================================
This program is free software; you can redistribute it and/or modify it under the
terms of the GNU General Public License as published by the Free Software
Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

copyright (c) 2015-2019 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
session_start();
$c=isset($_SESSION['c'])?$_SESSION['c']:(isset($_GET['c'])?$_GET['c']:'indigo');
define("_AUTHOR","cahyadsn");
$_SESSION['author']='cahyadsn';
$_SESSION['ver']=sha1(rand());
include 'inc/db.php';
$version='0.1';
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
    <meta name="keywords" content="php, mysql, data, administrasi, wilayah, indonesia, permendagri, 56, cahyadsn" />
    <meta name="description" content="Wilayah ver <?php echo $version;?> created by cahya dsn, Data wilayah administrasi Indonesia sesuai permendagri No 56 tahun 2015, dalam bahasa pemrograman PHP dan database MySQL" />
    <meta name="robots" content="index, follow" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/w3.css">
    <link rel="stylesheet" href="css/w3-theme-<?php echo $c;?>.css" media="all" id="wil_css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
    <link rel="stylesheet" href="css/wilayah.php?v=<?php echo md5(filemtime('css/wilayah.php'));?>">
    <script src="https://maps.googleapis.com/maps/api/js?key=[MASUKKAN_GOOGLE_API_KEY DISINI]"></script>
    <script src="js/jquery.min.js"></script>
    <script src="inc/geo_js.php?v=<?php echo $_SESSION['ver'];?>"></script>
    </head>
    <body>
    <div class="w3-top">
        <div class="w3-bar w3-theme-d5">
            <span class="w3-bar-item"># Wilayah v<?php echo $version;?></span>
            <button onclick="document.getElementById('id01').style.display='block'" class="w3-bar-item w3-button">Login</button>
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
            <h4 class="w3-theme-d1"> Sesuai Permendagri No 56 th 2015</h4>
        </div>
    <div class="w3-container">
      <div class="w3-row">
        <div class="w3-col m4 w3-padding">Mouse Position: <span id="mlat"></span></div>
        <div class="w3-col m8 w3-padding">
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
              $query=$db->prepare("SELECT kode,nama FROM wilayah_level_1_2 WHERE CHAR_LENGTH(kode)=2 ORDER BY nama");
              $query->execute();
              while ($data=$query->fetchObject()){
                echo '<option value="'.$data->kode.'">'.$data->nama.'</option>';
              }
              ?>
            <select>
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
        <div class="w3-col m6 w3-padding" id="kec_box">
          <label class="w3-col s6 m3">Pilih Kecamatan</label>
          <div class="w3-col s6 m3">
            <select name="kec" id="kec" class="w3-select w3-hover-theme" onchange="ajax(this.value)" readonly>
              <option value="">Pilih Kecamatan</option>
            </select>
          </div>
        </div>
        <div class="w3-col m6 w3-padding" id="kel_box">
          <label class="w3-col s6 m3">Pilih Kelurahan/Desa</label>
          <div class="w3-col s6 m3">
            <select name="kel" id="kel" class="w3-select w3-hover-theme" onchange="geo(this.value)" readonly>
              <option value="">Pilih Kelurahan/Desa</option>
            </select>
          </div>
        </div>
      </div>
    </div>
        </div>
        <div id="map-canvas"></div>
        <div class="w3-theme-d5 w3-padding">source code : <a href='https://github.com/cahyadsn/wilayah'>https://github.com/cahyadsn/wilayah</a></div>
        <div id="floating-panel" class='w3-theme-l3'>
      <div class="row">
        <div class="container">
          <header class="w3-container w3-padding w3-theme-d1"><b>Header</b></header>
        </div>
        <div class="w3-col s12 w3-padding w3-theme-l5" id="loc_box" style="border:solid 1px #999;margin: 3px 0px;">
          <label class="w3-col s12" id='l_kel'></label>
          <label class="w3-col s12" id='l_kec'></label>
          <label class="w3-col s12" id='l_kab'></label>
          <label class="w3-col s12" id='l_pro'></label>
        </div>
        <div class="w3-col s12 w3-padding w3-theme-l5 geo_box" style="border:solid 1px #999;margin: 3px 0px;">
          <label class="w3-col s6 w3-text-theme">Kode Wilayah</label>
          <div class="w3-col s6"><input type='text' class='w3-input' id='n_kode'></div>
          <label class="w3-col s6 w3-text-theme">Latitude</label>
          <div class="w3-col s6"><input type='text' class='w3-input' id='n_lat'></div>
          <label class="w3-col s6 w3-text-theme">Longitude</label>
          <div class="w3-col s6"><input type='text' class='w3-input' id='n_lng'></div>
          <label class="w3-col s6 w3-text-theme">Kode Pos</label>
          <div class="w3-col s6"><input type='text' class='w3-input' id='n_kodepos'></div>
          <label class="w3-col 12 w3-text-theme">Polygon</label>
          <p id="poly"></p>
        </div>
        <div class="w3-col w3-padding w3-theme-l2 s12" style="margin: 3px 0px;">
          <input type='submit' class='w3-btn w3-theme-d2 w3-right' value="update" id="btn_update">
        </div>
      </div>
        </div>
    <div id="id01" class="w3-modal">
        <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px">
        <div class="w3-center w3-theme-d1 w3-padding-16"><br>
            <span onclick="document.getElementById('id01').style.display='none'" class="w3-button w3-xlarge w3-hover-red w3-display-topright" title="Close Modal">&times;</span>
        </div>
        <div class="w3-container">
            <div class="w3-section">
            <label><b>Username</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="text" placeholder="Enter Username" name="usrname" required autocomplete="off">
            <label><b>Password</b></label>
            <input class="w3-input w3-border" type="password" placeholder="Enter Password" name="psw" required autocomplete="off">
            </div>
        </div>
        <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
            <button onclick="document.getElementById('id01').style.display='none'" type="button" class="w3-button w3-red">Cancel</button>
            <button class="w3-button w3-theme-d3" type="submit">Login</button>
        </div>

        </div>
    </div>
    </div>
    <div class="w3-bottom">
        <div class="w3-bar w3-theme-d4 w3-center">
            Wilayah v<?php echo $version;?> copyright &copy; 2017<?php echo (date('Y')>2017?date('-Y'):'');?> by <a href='mailto:cahyadsn@gmail.com'>cahya dsn</a><br />
        </div>
    </div>
    <script src="js/wilayah.js"></script>
</html>