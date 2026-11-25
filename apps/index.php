<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename    : index.php
purpose     : main application page
create      : 150702
last edit: 2026-11-25 07:00:00
author  	: cahya dsn
demo site 	: https://wilayah.cahyadsn.com/apps
source code : https://github.com/cahyadsn/wilayah/apps
================================================================================
MIT License
copyright (c) 2015-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
require_once __DIR__ . '/inc/session.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$raw_theme = $_SESSION['theme'] ?? $_GET['theme'] ?? 'light';
$theme = ($raw_theme === 'dark' || $raw_theme === 'light') ? $raw_theme : 'light';
$initial_kode = null;
if (!empty($_GET['kode']) && is_string($_GET['kode']) && preg_match('/^[0-9]+(\.[0-9]+)*$/', $_GET['kode']) && strlen($_GET['kode']) <= 13) {
    $initial_kode = $_GET['kode'];
}
$initial_pulau = null;
if (!empty($_GET['pulau']) && is_string($_GET['pulau']) && preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{5}$/', $_GET['pulau'])) {
    $initial_pulau = $_GET['pulau'];
}
$initial_compare = null;
if (!empty($_GET['compare']) && is_string($_GET['compare']) && preg_match('/^[0-9]{2}(\.[0-9]{2})?,[0-9]{2}(\.[0-9]{2})?$/', $_GET['compare'])) {
    $initial_compare = $_GET['compare'];
}
define("_AUTHOR","cahyadsn");
require_once 'inc/db.php';
require_once 'inc/geo_helpers.php';
$version=getenv('APP_VER') !== false ? getenv('APP_VER') : '3.2.0';
$safe_version = htmlspecialchars($version, ENT_QUOTES, 'UTF-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() - 3600));
header('Cache-Control: private, no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
  <title>Data Wilayah v<?php echo $safe_version;?></title>
  <meta name="description" content="Data wilayah administrasi Indonesia sesuai Kepmendagri No 300.2.2-2430 Tahun 2025" />
  <meta name="author" content="Cahya DSN" />
  <meta name="keywords" content="php, mysql, data, administrasi, wilayah, indonesia, kepmendagri,300.2.2-2430,2025, cahyadsn" />

  <meta name="csrf-token" content="<?php echo get_csrf_token_html(); ?>"><?php if ($initial_kode): ?>
  <meta name="geo-kode" content="<?php echo htmlspecialchars($initial_kode, ENT_QUOTES, 'UTF-8'); ?>"><?php endif; ?><?php if ($initial_pulau): ?>
  <meta name="geo-pulau" content="<?php echo htmlspecialchars($initial_pulau, ENT_QUOTES, 'UTF-8'); ?>"><?php endif; ?><?php if ($initial_compare): ?>
  <meta name="geo-compare" content="<?php echo htmlspecialchars($initial_compare, ENT_QUOTES, 'UTF-8'); ?>"><?php endif; ?>

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

  <!-- Leaflet MarkerCluster (P5) -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

  <link rel="stylesheet" href="css/styles.min.css?v=<?php echo $safe_version;?>" />
</head>
<body class="theme-<?php echo $theme==='light'?'light':'dark'; ?>">

  <!-- ======== NAVBAR ======== -->
  <nav class="navbar">
    <div class="navbar-brand">Wilayah</div>
    <div class="navbar-meta">
      <span class="navbar-version">v<?php echo $safe_version;?></span>
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
        class="share-btn"
        id="btnShareLink"
        type="button"
        aria-label="Bagikan tautan wilayah"
        title="Bagikan tautan wilayah"
      >
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <circle cx="18" cy="5" r="3"></circle>
          <circle cx="6" cy="12" r="3"></circle>
          <circle cx="18" cy="19" r="3"></circle>
          <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
          <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
        </svg>
        <span class="share-btn-text">Bagikan</span>
      </button>
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
                <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Tutup panel selector" title="Tutup panel selector" aria-expanded="true">
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

              <!-- Global Search Autocomplete (P1) -->
              <div class="global-search-wrapper" id="globalSearchWrapper">
                <label for="globalSearchInput" class="global-search-label">Pencarian Cepat</label>
                <div class="global-search-input-group">
                  <span class="global-search-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="11" cy="11" r="8"></circle>
                      <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                  </span>
                  <input
                    type="text"
                    id="globalSearchInput"
                    class="global-search-input"
                    placeholder="Cari wilayah, kode, atau pulau..."
                    autocomplete="off"
                    spellcheck="false"
                    aria-label="Cari wilayah atau pulau"
                    aria-autocomplete="list"
                    aria-controls="globalSearchResults"
                    aria-expanded="false"
                  />
                  <button type="button" id="globalSearchClear" class="global-search-clear" aria-label="Hapus pencarian" title="Hapus pencarian" style="display:none;">&times;</button>
                  <span id="globalSearchSpinner" class="global-search-spinner" style="display:none;" aria-hidden="true"></span>
                </div>
                <div id="globalSearchResults" class="global-search-results" style="display:none;" role="listbox"></div>
              </div>

              <div class="global-search-divider"><span>atau pilih hierarki</span></div>

              <!-- Province -->
              <div class="form-row">
                <div class="form-group">
                  <label for="prop">Provinsi</label>
                  <select name="prop" id="prop" class="form-select" onchange="ajax(this.value)">
                    <option value="">Pilih Provinsi</option>
                    <?php echo getProvinceOptionsHTML($db, $tbl_wilayah, __DIR__ . '/cache/provinsi_cache.html'); ?>
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
            <button type="button" class="map-overlay-copy" id="btnCopyLocation" aria-label="Salin tautan wilayah ini" title="Salin tautan wilayah">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
              </svg>
            </button>
            <div class="map-overlay-export" id="exportDropdownWrapper">
              <button type="button" class="map-overlay-export-btn" id="btnExportMenu" aria-label="Unduh data wilayah" title="Unduh data spasial & profil" aria-haspopup="true" aria-expanded="false">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="7 10 12 15 17 10"></polyline>
                  <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span class="export-btn-text">Unduh</span>
              </button>
              <div class="export-dropdown-menu" id="exportDropdownMenu" role="menu" style="display:none;">
                <button type="button" class="export-dropdown-item" id="exportGeoJsonBtn" role="menuitem" data-format="geojson" data-target="boundary">
                  <span class="export-badge badge-geojson">GeoJSON</span>
                  <span class="export-item-title">Batas Poligon (.geojson)</span>
                </button>
                <button type="button" class="export-dropdown-item" id="exportKmlBtn" role="menuitem" data-format="kml" data-target="boundary">
                  <span class="export-badge badge-kml">KML</span>
                  <span class="export-item-title">Batas Wilayah (.kml)</span>
                </button>
                <button type="button" class="export-dropdown-item" id="exportCsvStatsBtn" role="menuitem" data-format="csv" data-target="stats">
                  <span class="export-badge badge-csv">CSV</span>
                  <span class="export-item-title">Profil Wilayah (.csv)</span>
                </button>
              </div>
            </div>
            <button type="button" class="map-overlay-compare-btn" id="btnOpenCompare" aria-label="Bandingkan wilayah" title="Bandingkan dua wilayah (P6)" aria-haspopup="dialog">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <rect x="2" y="3" width="8" height="18" rx="1"></rect>
                <rect x="14" y="3" width="8" height="18" rx="1"></rect>
                <line x1="6" y1="8" x2="6" y2="8.01"></line>
                <line x1="18" y1="8" x2="18" y2="8.01"></line>
              </svg>
              <span class="compare-btn-text">Bandingkan</span>
            </button>
            <button type="button" class="map-overlay-timeline-btn" id="btnToggleTimeline" aria-label="Linimasa Historis Pemekaran Wilayah" title="Linimasa Historis Pemekaran Wilayah (P7)" aria-expanded="false">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
              <span class="timeline-btn-text">Linimasa</span>
            </button>
            <button type="button" class="map-overlay-measure-btn" id="btnToggleMeasure" aria-label="Ukur Jarak & Radius Spasial" title="Ukur Jarak & Radius Spasial (P8)" aria-expanded="false">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M2 12h20M7 8v8M12 10v4M17 8v8"></path>
              </svg>
              <span class="measure-btn-text">Ukur</span>
            </button>
          </div>

          <!-- Floating Timeline Control Bar (P7) -->
          <div class="timeline-floating-bar" id="timelineFloatingBar" role="region" aria-label="Linimasa Regulasi Wilayah Indonesia" style="display:none;">
            <div class="timeline-inner">
              <div class="timeline-header-bar">
                <div class="timeline-title-wrap">
                  <span class="timeline-badge">P7</span>
                  <span class="timeline-title">Linimasa Regulasi Wilayah</span>
                  <span class="timeline-year-tag" id="timelineCurrentYearTag">2025</span>
                </div>
                <div class="timeline-action-btns">
                  <button type="button" class="timeline-control-btn timeline-minimize-btn" id="btnCloseTimeline" aria-label="Tutup linimasa" title="Tutup linimasa">&times;</button>
                </div>
              </div>

              <!-- Controls row: Play/Pause, Slider with Ticks, Step prev/next -->
              <div class="timeline-controls-row">
                <button type="button" class="timeline-play-btn" id="btnTimelinePlay" aria-label="Putar linimasa" title="Mulai / Jeda Animasi Linimasa">
                  <svg class="icon-play" viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                  </svg>
                  <svg class="icon-pause" viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true" style="display:none;">
                    <rect x="6" y="4" width="4" height="16"></rect>
                    <rect x="14" y="4" width="4" height="16"></rect>
                  </svg>
                </button>

                <div class="timeline-slider-track-wrap">
                  <input type="range" class="timeline-slider-input" id="timelineSlider" min="0" max="5" step="1" value="5" aria-label="Pilih tahun linimasa" />
                  <div class="timeline-ticks" id="timelineTicks">
                    <div class="timeline-tick" data-index="0" data-year="2016"><span class="tick-dot"></span><span class="tick-label">2016</span></div>
                    <div class="timeline-tick" data-index="1" data-year="2018"><span class="tick-dot"></span><span class="tick-label">2018</span></div>
                    <div class="timeline-tick" data-index="2" data-year="2020"><span class="tick-dot"></span><span class="tick-label">2020</span></div>
                    <div class="timeline-tick" data-index="3" data-year="2022"><span class="tick-dot"></span><span class="tick-label">2022</span></div>
                    <div class="timeline-tick" data-index="4" data-year="2023"><span class="tick-dot"></span><span class="tick-label">2023</span></div>
                    <div class="timeline-tick is-active" data-index="5" data-year="2025"><span class="tick-dot"></span><span class="tick-label">2025</span></div>
                  </div>
                </div>

                <div class="timeline-nav-btns">
                  <button type="button" class="timeline-nav-btn" id="btnTimelinePrev" aria-label="Tahun sebelumnya" title="Tahun sebelumnya">◀</button>
                  <button type="button" class="timeline-nav-btn" id="btnTimelineNext" aria-label="Tahun berikutnya" title="Tahun berikutnya">▶</button>
                </div>
              </div>

              <!-- Milestone Details Card -->
              <div class="timeline-milestone-box" id="timelineMilestoneBox">
                <div class="milestone-headline-row">
                  <div class="milestone-reg-title" id="timelineRegTitle">Kepmendagri No 300.2.2-2430 Tahun 2025</div>
                  <span class="milestone-status-pill" id="timelineStatusPill">Terkini</span>
                </div>
                <div class="milestone-desc-text" id="timelineDescText">
                  Pemutakhiran pulau rujukan BIG Gazeter 2024 dan penyesuaian batas administrasi terkini.
                </div>
                <div class="milestone-chips-row" id="timelineChipsRow">
                  <span class="milestone-chip" id="chipTimelineProv"><strong>38</strong> Prov</span>
                  <span class="milestone-chip" id="chipTimelineKab"><strong>514</strong> Kab/Kota</span>
                  <span class="milestone-chip" id="chipTimelineKec"><strong>7.285</strong> Kec</span>
                  <span class="milestone-chip" id="chipTimelineDesa"><strong>83.762</strong> Desa</span>
                  <span class="milestone-chip" id="chipTimelinePulau"><strong>17.380</strong> Pulau</span>
                </div>
                <!-- Papua DOB Highlight Callout (shown on year 2023) -->
                <div class="timeline-dob-callout" id="timelineDobCallout" style="display:none;">
                  <div class="dob-callout-header">
                    <span class="dob-sparkle">✨</span>
                    <strong>Pemekaran 4 Daerah Otonomi Baru (DOB) Tanah Papua</strong>
                  </div>
                  <div class="dob-callout-body">
                    Terbentuk 4 provinsi baru hasil pemekaran Papua &amp; Papua Barat:
                    <div class="dob-badges-list">
                      <span class="dob-province-badge" title="Ibukota: Merauke">Papua Selatan (93)</span>
                      <span class="dob-province-badge" title="Ibukota: Nabire">Papua Tengah (94)</span>
                      <span class="dob-province-badge" title="Ibukota: Wamena">Papua Pegunungan (95)</span>
                      <span class="dob-province-badge" title="Ibukota: Sorong">Papua Barat Daya (96)</span>
                    </div>
                  </div>
                  <div class="dob-callout-footer">
                    <button type="button" class="dob-zoom-action-btn" id="btnFocusDobPapua">
                      <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                      </svg>
                      Fokus Peta ke Papua
                    </button>
                    <a href="https://github.com/cahyadsn/wilayah_ref" target="_blank" rel="noopener noreferrer" class="dob-ref-link" id="timelineRefLink">
                      Rujukan Regulasi &rarr;
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Floating Measurement & Buffer Tool Bar (P8) -->
          <div class="measure-floating-bar" id="measureFloatingBar" role="region" aria-label="Pengukuran Jarak & Radius Maritim" style="display:none;">
            <div class="measure-inner">
              <div class="measure-header-bar">
                <div class="measure-title-wrap">
                  <span class="measure-badge">P8</span>
                  <span class="measure-title">Pengukuran &amp; Radius Spasial</span>
                </div>
                <div class="measure-action-btns">
                  <button type="button" class="measure-control-btn" id="btnCloseMeasure" aria-label="Tutup menu pengukuran" title="Tutup menu pengukuran">&times;</button>
                </div>
              </div>

              <!-- Mode Selection Row -->
              <div class="measure-mode-row">
                <button type="button" class="measure-mode-btn is-active" id="btnMeasureModeRuler" data-mode="ruler" title="Ukur jarak garis lurus / polyline antarpulau atau wilayah">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="2" y1="21" x2="22" y2="3"></line>
                    <line x1="5" y1="18" x2="8" y2="15"></line>
                    <line x1="9" y1="14" x2="12" y2="11"></line>
                    <line x1="13" y1="10" x2="16" y2="7"></line>
                    <line x1="17" y1="6" x2="20" y2="3"></line>
                  </svg>
                  <span>Garis / Jarak</span>
                </button>
                <button type="button" class="measure-mode-btn" id="btnMeasureModeBuffer" data-mode="buffer" title="Analisis radius lingkaran / deteksi pulau terdekat">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"></circle>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                  <span>Radius / Buffer</span>
                </button>
                <button type="button" class="measure-mode-btn measure-reset-btn" id="btnMeasureClear" title="Hapus seluruh pengukuran dan reset peta">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                  </svg>
                  <span>Reset</span>
                </button>
              </div>

              <!-- Mode Ruler Controls -->
              <div class="measure-controls-ruler" id="measureControlsRuler">
                <div class="measure-unit-row">
                  <span class="measure-sublabel">Satuan:</span>
                  <div class="measure-unit-toggle" id="measureUnitToggle">
                    <button type="button" class="measure-unit-btn is-active" data-unit="km" id="btnUnitKm">Kilometer (km)</button>
                    <button type="button" class="measure-unit-btn" data-unit="nm" id="btnUnitNm">Mil Laut (NM)</button>
                  </div>
                </div>
                <div class="measure-hint-row" id="measureRulerHint">
                  Klik titik awal di peta, lalu klik titik berikutnya untuk mengukur jarak.
                </div>
                <div class="measure-stats-row" id="measureRulerStats" style="display:none;">
                  <div class="measure-stat-item">
                    <span class="measure-stat-label">Total Jarak:</span>
                    <span class="measure-stat-val" id="measureTotalDistance">0.00 km</span>
                  </div>
                  <div class="measure-stat-item">
                    <span class="measure-stat-label">Titik:</span>
                    <span class="measure-stat-val" id="measurePointsCount">0</span>
                  </div>
                  <div class="measure-stat-item" id="measureAzimuthItem" style="display:none;">
                    <span class="measure-stat-label">Arah/Azimuth:</span>
                    <span class="measure-stat-val" id="measureAzimuthVal">-</span>
                  </div>
                </div>
              </div>

              <!-- Mode Buffer Controls -->
              <div class="measure-controls-buffer" id="measureControlsBuffer" style="display:none;">
                <div class="measure-buffer-config-row">
                  <label for="measureRadiusSlider" class="measure-sublabel">Radius:</label>
                  <input type="range" class="measure-slider-input" id="measureRadiusSlider" min="1" max="200" step="1" value="25" aria-label="Radius analisis" />
                  <div class="measure-radius-display">
                    <input type="number" class="measure-number-input" id="measureRadiusInput" min="0.1" max="500" step="1" value="25" aria-label="Input radius km" />
                    <span class="measure-unit-label">km</span>
                  </div>
                </div>
                <div class="measure-hint-row" id="measureBufferHint">
                  Klik sembarang titik di perairan/daratan peta untuk memindai pulau dan wilayah di sekitarnya.
                </div>
              </div>

              <!-- Buffer Results Panel -->
              <div class="measure-results-panel" id="measureResultsPanel" style="display:none;">
                <div class="measure-results-header">
                  <div class="measure-results-title">
                    <span id="measureResultsCount">0</span> entitas terdeteksi dalam radius <span id="measureResultsRadiusTag">25 km</span>
                  </div>
                  <button type="button" class="measure-export-csv-btn" id="btnExportMeasureCsv" title="Unduh CSV hasil analisis buffer">
                    <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                      <polyline points="7 10 12 15 17 10"></polyline>
                      <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    CSV
                  </button>
                </div>
                <div class="measure-results-list" id="measureResultsList"></div>
              </div>
            </div>
          </div>
          </div>
        </div>

        <div class="island-panel" id="islandPanel">
          <div class="card">
            <div class="card-header">
              <div class="island-header-row">
                <div>
                  <div class="card-title">
                    Daftar Pulau
                    <small id="islandCount">0 pulau</small>
                  </div>
                </div>
                                <button type="button" class="island-export-btn" id="btnExportIslands" aria-label="Unduh daftar pulau CSV" title="Unduh daftar pulau CSV">
                  <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                  </svg>
                  <span>CSV Pulau</span>
                </button>
                <button type="button" class="island-toggle" id="islandToggle" aria-label="Tutup panel pulau" title="Tutup panel pulau" aria-expanded="true">
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M9.5 6.5L15 12l-5.5 5.5"></path>
                  </svg>
                </button>
              </div>
            </div>

            <div class="card-body">
              <div class="island-location-badge" id="islandLocationBadge">
                <span class="island-loc-icon" aria-hidden="true">🏝️</span>
                <span class="island-loc-text" id="islandLocationName">Pilih Provinsi / Kab / Kota</span>
              </div>

              <!-- Cluster Toggle (P5) -->
              <div class="island-cluster-toggle" id="islandClusterToggleWrapper" style="display:none;">
                <label class="switch" for="toggleShowAllIslands">
                  <input type="checkbox" id="toggleShowAllIslands" aria-label="Sebarkan seluruh pulau di peta" />
                  <span class="slider round"></span>
                </label>
                <span class="toggle-label" id="islandClusterToggleLabel">Sebarkan di peta</span>
              </div>

              <div class="island-search-wrapper" id="islandSearchWrapper" style="display:none;">
                <input type="text" id="islandSearch" class="island-search-input" placeholder="Cari nama atau kode pulau..." aria-label="Cari pulau" oninput="filterIslands(this.value)" />
              </div>

              <div class="island-list-wrapper">
                <div class="island-empty-state" id="islandEmptyState">
                  Pilih provinsi atau kabupaten/kota untuk melihat daftar pulau
                </div>
                <div class="island-list" id="islandList" style="display:none;" role="list"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="app-footer">
        Wilayah v<?php echo $safe_version;?> copyright &copy; 2017<?php echo (date('Y')>2017?date('-Y'):'');?>
        by <a href="mailto:cahyadsn@gmail.com">cahya dsn</a> &mdash;
        source: <a href="https://github.com/cahyadsn/wilayah">github.com/cahyadsn/wilayah</a>
      </div>

    </div>
  </div>

  <!-- ======== SCRIPTS ======== -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="https://unpkg.com/leaflet-draw@0.4.13/dist/leaflet.draw.js" defer></script>
  <!-- Leaflet MarkerCluster (P5) -->
  <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js" defer></script>
  <script src="js/ajax.js" defer></script>
  <script src="inc/geo_js.php?v=<?php echo $safe_version;?>" defer></script>
  <script src="js/wilayah.min.js?v=<?php echo $safe_version;?>" defer></script>

  <!-- ======== COMPARE MODAL (P6) ======== -->
  <div class="compare-modal-backdrop" id="compareModal" role="dialog" aria-modal="true" aria-labelledby="compareModalTitle" style="display:none;">
    <div class="compare-modal-dialog">
      <div class="compare-modal-header">
        <div class="compare-modal-title-group">
          <span class="compare-modal-badge">P6</span>
          <h2 class="compare-modal-title" id="compareModalTitle">Perbandingan Wilayah Multidimensi</h2>
        </div>
        <button type="button" class="compare-modal-close" id="btnCompareClose" aria-label="Tutup dialog perbandingan">&times;</button>
      </div>

      <div class="compare-modal-body">
        <!-- Level Tabs -->
        <div class="compare-level-tabs" role="tablist">
          <button type="button" class="compare-level-tab is-active" id="tabCompareProv" data-level="provinsi" role="tab" aria-selected="true">
            Antar Provinsi
          </button>
          <button type="button" class="compare-level-tab" id="tabCompareKab" data-level="kabkota" role="tab" aria-selected="false">
            Antar Kabupaten / Kota
          </button>
        </div>

        <!-- Selector Row -->
        <div class="compare-selector-bar">
          <div class="compare-select-col">
            <label for="compareSelect1" class="compare-select-label">
              <span class="compare-legend-dot dot-entity1" aria-hidden="true"></span>
              Wilayah 1 (Biru)
            </label>
            <select id="compareSelect1" class="compare-select">
              <option value="">Pilih Wilayah 1...</option>
            </select>
          </div>

          <div class="compare-vs-badge" aria-hidden="true">VS</div>

          <div class="compare-select-col">
            <label for="compareSelect2" class="compare-select-label">
              <span class="compare-legend-dot dot-entity2" aria-hidden="true"></span>
              Wilayah 2 (Jingga)
            </label>
            <select id="compareSelect2" class="compare-select">
              <option value="">Pilih Wilayah 2...</option>
            </select>
          </div>

          <div class="compare-action-col">
            <button type="button" id="btnRunCompare" class="compare-run-btn" disabled>
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
              <span>Bandingkan</span>
            </button>
          </div>
        </div>

        <!-- Spinner & Loading State -->
        <div id="compareSpinner" class="compare-spinner-wrapper" style="display:none;" role="status" aria-live="polite">
          <div class="spinner"></div>
          <span>Memuat data perbandingan wilayah...</span>
        </div>

        <!-- Error State -->
        <div id="compareError" class="compare-error-alert" style="display:none;" role="alert"></div>

        <!-- Comparison Results Area -->
        <div id="compareResults" class="compare-results" style="display:none;"></div>

        <!-- Empty State -->
        <div id="compareEmptyState" class="compare-empty-state">
          <div class="compare-empty-icon">📊</div>
          <h3>Pilih Dua Wilayah untuk Memulai Komparasi</h3>
          <p>Bandingkan profil demografi, luas wilayah, kepadatan penduduk, rasio jenis kelamin, dan beban administratif secara komparatif berdampingan.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast Notification (P2) -->
  <div id="toastNotification" class="toast-notification" role="status" aria-live="polite">
    <span class="toast-icon">✓</span>
    <span id="toastMessage" class="toast-message">Tautan berhasil disalin ke clipboard!</span>
  </div>

</body>
</html>
