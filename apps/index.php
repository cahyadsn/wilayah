<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename    : index.php
purpose     : main application page
create      : 150702
last edit   : 2026-06-11 15:52:12
author  	: cahya dsn
demo site 	: https://wilayah.cahyadsn.com/apps
source code : https://github.com/cahyadsn/wilayah/apps
================================================================================
MIT License
copyright (c) 2015-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
session_start();
$theme=isset($_SESSION['theme'])?$_SESSION['theme']:(isset($_GET['theme'])?$_GET['theme']:'light');
define("_AUTHOR","cahyadsn");
$_SESSION['author']='cahyadsn';
$_SESSION['ver']=sha1(rand());
require_once 'inc/db.php';
$version='3.0.1';
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
header('Cache-Control: public, max-age=86400');
header('Pragma: cache');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
  <title>Data Wilayah v<?php echo $version;?></title>
  <meta name="description" content="Data wilayah administrasi Indonesia sesuai Kepmendagri No 300.2.2-2430 Tahun 2025" />
  <meta name="author" content="Cahya DSN" />
  <meta name="keywords" content="php, mysql, data, administrasi, wilayah, indonesia, kepmendagri,300.2.2-2430,2025, cahyadsn" />

  <!-- Preconnect -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://unpkg.com">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@0.4.13/dist/leaflet.draw.css" />

  <link rel="stylesheet" href="css/styles.min.css?v=<?php echo $version;?>" />
</head>
<body class="theme-<?php echo $theme==='light'?'light':'dark'; ?>">

  <!-- ======== NAVBAR ======== -->
  <nav class="navbar">
    <div class="navbar-brand">Wilayah</div>
    <div class="navbar-meta">
      <span class="navbar-version">v<?php echo $version;?></span>
      <span class="navbar-separator">|</span>
      <span class="navbar-copyright">
        copyright &copy; 2017<?php echo (date('Y')>2017?date('-Y'):'');?>
        by <a href="mailto:cahyadsn@gmail.com">cahya dsn</a>
        <span class="navbar-separator">|</span>
        <a href="https://github.com/cahyadsn/wilayah">source</a>
      </span>
    </div>
    <div class="navbar-actions" id="themeBar">
      <button
        class="theme-toggle"
        id="themeToggle"
        type="button"
        aria-label="Toggle dark/light theme"
        title="Toggle dark/light theme"
      >
        <svg class="theme-icon-sun" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="4"></circle>
          <path d="M12 2v2"></path>
          <path d="M12 20v2"></path>
          <path d="M4.93 4.93l1.41 1.41"></path>
          <path d="M17.66 17.66l1.41 1.41"></path>
          <path d="M2 12h2"></path>
          <path d="M20 12h2"></path>
          <path d="M4.93 19.07l1.41-1.41"></path>
          <path d="M17.66 6.34l1.41-1.41"></path>
        </svg>
        <svg class="theme-icon-moon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M21 12.79A9 9 0 1 1 11.21 3c0.5 0 0.79 0.57 0.47 0.96A7 7 0 0 0 20.04 12.3c0.39-0.32 0.96-0.03 0.96 0.49z"></path>
        </svg>
      </button>
    </div>
  </nav>

  <!-- ======== MAIN ======== -->
  <div class="app-wrapper">
    <div class="app-main">

      <div class="layout-grid">
        <div class="sidebar-panel">
          <div class="card">
            <div class="card-header">
              <div class="sidebar-header-row">
                <div>
                  <div class="card-title">
                    Data Wilayah Administrasi Indonesia
                    <small>Sesuai Kepmendagri No 300.2.2-2430 Tahun 2025</small>
                  </div>
                </div>
                <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Tutup panel selector" title="Tutup panel selector">
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M14.5 6.5L9 12l5.5 5.5"></path>
                  </svg>
                </button>
              </div>
            </div>

            <div class="card-body">
              <!-- Preload indicator -->
              <div id="preload" role="status" aria-live="polite">
                <div class="spinner"></div>
                <span>Loading...</span>
              </div>

              <!-- Province -->
              <div class="form-row">
                <div class="form-group">
                  <label for="prop">Provinsi</label>
                  <select name="prop" id="prop" class="form-select" onchange="ajax(this.value)">
                    <option value="">Pilih Provinsi</option>
                    <?php
                    $cache_file = __DIR__ . '/cache/provinsi_cache.html';
                    $cache_ttl = 86400;

                    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_ttl)) {
                      echo file_get_contents($cache_file);
                    } else {
                      $query=$db->prepare("SELECT kode,nama FROM {$tbl_wilayah} WHERE CHAR_LENGTH(kode)=2 ORDER BY nama");
                      $query->execute();
                      $html = '';
                      while ($data=$query->fetchObject()){
                        $html .= '<option value="'.$data->kode.'">'.$data->nama.'</option>';
                      }
                      file_put_contents($cache_file, $html, LOCK_EX);
                      echo $html;
                    }
                    ?>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group" id="kab_box" style="display:none;">
                  <label for="kota">Kota / Kabupaten</label>
                  <select name="kota" id="kota" class="form-select" onchange="ajax(this.value)">
                    <option value="">Pilih Kota</option>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group" id="kec_box" style="display:none;">
                  <label for="kec">Kecamatan</label>
                  <select name="kec" id="kec" class="form-select" onchange="ajax(this.value)">
                    <option value="">Pilih Kecamatan</option>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group" id="kel_box" style="display:none;">
                  <label for="kel">Desa / Kelurahan</label>
                  <select name="kel" id="kel" class="form-select" onchange="geo(this.value)">
                    <option value="">Pilih Desa</option>
                  </select>
                </div>
              </div>


            </div>
          </div>
        </div>

        <div class="map-panel">
          <div id="map-canvas"></div>
          <div class="map-overlay-bar" id="infoStrip">
            <div class="map-overlay-item"><span class="map-overlay-label">Kode</span><span class="map-overlay-value" id="n_kode">—</span></div>
            <div class="map-overlay-item"><span class="map-overlay-label">Lat</span><span class="map-overlay-value" id="n_lat">—</span></div>
            <div class="map-overlay-item"><span class="map-overlay-label">Lng</span><span class="map-overlay-value" id="n_lng">—</span></div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="app-footer">
        Wilayah v<?php echo $version;?> copyright &copy; 2017<?php echo (date('Y')>2017?date('-Y'):'');?>
        by <a href="mailto:cahyadsn@gmail.com">cahya dsn</a> &mdash;
        source: <a href="https://github.com/cahyadsn/wilayah">github.com/cahyadsn/wilayah</a>
      </div>

    </div>
  </div>

  <!-- ======== SCRIPTS ======== -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="https://unpkg.com/leaflet-draw@0.4.13/dist/leaflet.draw.js" defer></script>
  <script src="inc/geo_js.php?v=<?php echo $version;?>" defer></script>
  <script src="js/wilayah.min.js?v=<?php echo $version;?>" defer></script>

</body>
</html>
