<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_js.php
purpose  : JavaScript for AJAX cascade, map, and counters
create   : 170912
last edit: 2026-06-11 15:52:12
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
session_start();
header("Content-type: text/javascript");
header('Cache-Control: public, max-age=31536000');
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
header('Pragma: cache');
if(isset($_SESSION['author']) && $_SESSION['author']=='cahyadsn'){
    $v=$_GET['v'];
} else {
    die('illegal call');
}
?>

/* ============================================================
   GEOSCRIPT — Data Wilayah v2.8
   Pure JS, no GSAP dependency
   ============================================================ */

var ids, my_id, my_z;
var wil = new Array('prov', 'kota', 'kec', 'kel');

// --- AJAX helpers ---

var my_ajax = do_ajax();

function ajax(id) {
    document.getElementById('preload').classList.add('is-visible');
    ids = id;
    var url = "inc/geo_ajax.php?id=" + id + "&sid=" + Math.random();
    my_ajax.onreadystatechange = stateChanged;
    my_ajax.open("GET", url, true);
    my_ajax.send(null);
}

function geo(id) {
    document.getElementById('preload').classList.add('is-visible');
    ids = id;
    var geo_url = "inc/geo_ajax.php?id=" + id + "&geo=1&sid=" + Math.random();
    var req = do_ajax();
    return new Promise(function(resolve) {
        req.onreadystatechange = function() {
            if (req.readyState == 4) {
                try {
                    var d = JSON.parse(req.responseText);
                    if (d.status && d.data) {
                        document.getElementById('n_kode').textContent = d.data.kode || '—';
                        document.getElementById('n_lat').textContent = d.data.lat ? parseFloat(d.data.lat).toFixed(5) : '—';
                        document.getElementById('n_lng').textContent = d.data.lng ? parseFloat(d.data.lng).toFixed(5) : '—';
                        if (d.data.lat) updateMap(d.data);
                    }
                    resolve(d || null);
                } catch(e) {
                    resolve(null);
                }
                document.getElementById('preload').classList.remove('is-visible');
            }
        };
        req.open("GET", geo_url, true);
        req.send(null);
    });
}

function fetchWilayah(url) {
    return fetch(url, { cache: 'no-store' }).then(function(response) {
        if (!response.ok) throw new Error('Network error');
        return response.json();
    });
}

function setSelectValue(id, value) {
    var select = document.getElementById(id);
    if (select && value) select.value = value;
}

function loadChildren(parentKode, targetSelectId, targetBoxId) {
    return fetchWilayah('inc/geo_ajax.php?id=' + encodeURIComponent(parentKode) + '&sid=' + Math.random())
        .then(function(result) {
            var select = document.getElementById(targetSelectId);
            var box = document.getElementById(targetBoxId);
            if (select && result.opt) select.innerHTML = result.opt;
            if (box) box.style.display = 'block';
            return result;
        });
}

function applyReverseSelection(chain, nearest) {
    if (!chain || !chain.prov) return Promise.resolve();

    setSelectValue('prop', chain.prov.kode);

    return loadChildren(chain.prov.kode, 'kota', 'kab_box')
        .then(function() {
            if (!chain.kab) return null;
            setSelectValue('kota', chain.kab.kode);
            return loadChildren(chain.kab.kode, 'kec', 'kec_box');
        })
        .then(function() {
            if (!chain.kec) return null;
            setSelectValue('kec', chain.kec.kode);
            return loadChildren(chain.kec.kode, 'kel', 'kel_box');
        })
        .then(function() {
            if (chain.kel) {
                setSelectValue('kel', chain.kel.kode);
                return geo(chain.kel.kode);
            }
            if (chain.kec) {
                return geo(chain.kec.kode);
            }
            if (chain.kab) {
                return geo(chain.kab.kode);
            }
            if (nearest && nearest.kode) {
                return geo(nearest.kode);
            }
            return null;
        });
}

function reverseLookupFromMapClick(latlng) {
    var preload = document.getElementById('preload');
    var msg = document.getElementById('msg_box');
    var hint = document.getElementById('mapClickHint');

    if (preload) preload.classList.add('is-visible');
    if (msg) {
        msg.textContent = 'Mencari wilayah terdekat dari titik klik...';
        msg.classList.remove('has-error');
    }
    if (hint) hint.textContent = 'Mencari wilayah terdekat...';

    return fetchWilayah('inc/reverse_lookup.php?lat=' + encodeURIComponent(latlng.lat) + '&lng=' + encodeURIComponent(latlng.lng) + '&sid=' + Math.random())
        .then(function(result) {
            if (!result.status || !result.data) throw new Error(result.error || 'Wilayah tidak ditemukan');
            return applyReverseSelection(result.data.chain, result.data.nearest).then(function() {
                if (msg) {
                    msg.textContent = 'Dipilih dari klik peta: ' + (result.data.nearest.nama || result.data.nearest.kode) + ' (' + result.data.nearest.distance_km + ' km dari titik klik)';
                    msg.classList.remove('has-error');
                }
                if (hint) hint.textContent = 'Klik peta untuk pilih wilayah terdekat';
                return result;
            });
        })
        .catch(function(error) {
            if (msg) {
                msg.textContent = error.message || 'Gagal membaca titik klik';
                msg.classList.add('has-error');
            }
            if (hint) hint.textContent = 'Klik peta untuk pilih wilayah terdekat';
        })
        .finally(function() {
            if (preload) preload.classList.remove('is-visible');
        });
}

// --- Map ---
var polyLatLngs, polyLayer, map, marker, baseLayers, labelOverlay, currentBaseLayer, baseMapMenuControl;
var myPoint = [-6.17501, 106.820497];

function isLightThemeActive() {
    return document.body && document.body.classList.contains('theme-light');
}

function getThemeAwareMapTileLayer() {
    if (isLightThemeActive()) {
        return L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>'
        });
    }

    return L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 20,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>, &copy; <a href="https://carto.com/">CARTO</a>'
    });
}

function getThemeAwareLabelOverlay() {
    var url = isLightThemeActive()
        ? 'https://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}{r}.png'
        : 'https://{s}.basemaps.cartocdn.com/dark_only_labels/{z}/{x}/{y}{r}.png';

    return L.tileLayer(url, {
        maxZoom: 20,
        pane: 'overlayPane',
        attribution: '&copy; <a href="https://carto.com/">CARTO</a>'
    });
}

function setBaseLayer(name) {
    if (!map || !baseLayers[name]) return;

    Object.keys(baseLayers).forEach(function(layerName) {
        if (map.hasLayer(baseLayers[layerName])) {
            map.removeLayer(baseLayers[layerName]);
        }
    });

    baseLayers[name].addTo(map);
    currentBaseLayer = name;

    if (window.localStorage) {
        localStorage.setItem('wilayahBaseLayer', currentBaseLayer);
    }

    document.querySelectorAll('[data-basemap-option]').forEach(function(option) {
        var isActive = option.getAttribute('data-layer-name') === currentBaseLayer;
        option.classList.toggle('is-active', isActive);
        option.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });

    var label = document.getElementById('basemapCurrentLabel');
    if (label) {
        var layerLabelMap = {
            'Map view': 'Peta',
            'Satellite view': 'Satelit',
            'Terrain view': 'Terrain'
        };
        label.textContent = layerLabelMap[currentBaseLayer] || 'Peta';
    }

    var labelsEnabled = window.localStorage ? localStorage.getItem('wilayahSatelliteLabels') === '1' : false;
    if (labelOverlay) {
        if (currentBaseLayer === 'Satellite view' && labelsEnabled) {
            if (!map.hasLayer(labelOverlay)) labelOverlay.addTo(map);
        } else if (map.hasLayer(labelOverlay)) {
            map.removeLayer(labelOverlay);
        }
    }

    var labelsToggle = document.getElementById('basemapLabelsToggle');
    if (labelsToggle) {
        labelsToggle.checked = currentBaseLayer === 'Satellite view' && labelsEnabled;
    }

    updateLabelToggleVisibility();
}

function updateLabelToggleVisibility() {
    var row = document.getElementById('basemapLabelsRow');
    if (!row) return;
    row.classList.toggle('is-visible', currentBaseLayer === 'Satellite view');
}

function rebuildThemeAwareLayers() {
    if (!map) return;

    if (baseLayers && baseLayers['Map view'] && map.hasLayer(baseLayers['Map view'])) {
        map.removeLayer(baseLayers['Map view']);
    }
    if (labelOverlay && map.hasLayer(labelOverlay)) {
        map.removeLayer(labelOverlay);
    }

    baseLayers['Map view'] = getThemeAwareMapTileLayer();
    labelOverlay = getThemeAwareLabelOverlay();
}

function refreshMapThemeAwareness() {
    if (!map || !baseLayers) return;
    rebuildThemeAwareLayers();
    setBaseLayer(currentBaseLayer || 'Map view');
}

function setSatelliteLabels(enabled) {
    if (!map || !labelOverlay) return;

    if (enabled) {
        if (!map.hasLayer(labelOverlay)) labelOverlay.addTo(map);
    } else if (map.hasLayer(labelOverlay)) {
        map.removeLayer(labelOverlay);
    }

    if (window.localStorage) {
        localStorage.setItem('wilayahSatelliteLabels', enabled ? '1' : '0');
    }

    var toggle = document.getElementById('basemapLabelsToggle');
    if (toggle) {
        toggle.checked = enabled;
    }
}

function initBaseMapMenu() {
    baseMapMenuControl = L.control({ position: 'topright' });

    baseMapMenuControl.onAdd = function() {
        var container = L.DomUtil.create('div', 'leaflet-bar basemap-menu');
        container.innerHTML = [
            '<button type="button" class="basemap-menu-toggle" id="basemapMenuToggle" aria-label="Buka pilihan peta dasar" title="Peta dasar">',
            '  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7.5l5-2.5 6 2.5 5-2.5v11.5l-5 2.5-6-2.5-5 2.5V7.5z"></path><path d="M9 5v11.5"></path><path d="M15 7.5V19"></path><path d="M6.2 8.5l2.8-1.4"></path><path d="M16.8 8.5l2.2-1.1"></path></svg>',
            '  <span id="basemapCurrentLabel">Peta</span>',
            '</button>',
            '<div class="basemap-menu-panel" id="basemapMenuPanel">',
            '  <button type="button" class="basemap-option" data-basemap-option="1" data-layer-name="Map view">',
            '    <span class="basemap-thumb basemap-thumb-map"><span></span></span>',
            '    <span class="basemap-option-text"><strong>Peta</strong><small>Jalan dan batas wilayah</small></span>',
            '    <span class="basemap-check" aria-hidden="true">✓</span>',
            '  </button>',
            '  <button type="button" class="basemap-option" data-basemap-option="1" data-layer-name="Satellite view">',
            '    <span class="basemap-thumb basemap-thumb-satellite"><span></span></span>',
            '    <span class="basemap-option-text"><strong>Satelit</strong><small>Citra permukaan bumi</small></span>',
            '    <span class="basemap-check" aria-hidden="true">✓</span>',
            '  </button>',
            '  <button type="button" class="basemap-option" data-basemap-option="1" data-layer-name="Terrain view">',
            '    <span class="basemap-thumb basemap-thumb-terrain"><span></span></span>',
            '    <span class="basemap-option-text"><strong>Terrain</strong><small>Kontur dan topografi</small></span>',
            '    <span class="basemap-check" aria-hidden="true">✓</span>',
            '  </button>',
            '  <label class="basemap-labels-row" id="basemapLabelsRow">',
            '    <span class="basemap-labels-copy"><strong>Label lokasi</strong><small>Tampilkan nama tempat di satelit</small></span>',
            '    <span class="basemap-switch">',
            '      <input type="checkbox" id="basemapLabelsToggle" />',
            '      <span class="basemap-switch-ui"></span>',
            '    </span>',
            '  </label>',
            '</div>'
        ].join('');

        L.DomEvent.disableClickPropagation(container);
        L.DomEvent.disableScrollPropagation(container);
        return container;
    };

    baseMapMenuControl.addTo(map);

    var container = document.querySelector('.basemap-menu');
    var toggle = document.getElementById('basemapMenuToggle');

    if (toggle && container) {
        toggle.addEventListener('click', function() {
            container.classList.toggle('is-open');
        });
    }

    document.querySelectorAll('[data-basemap-option]').forEach(function(option) {
        option.addEventListener('click', function() {
            var name = this.getAttribute('data-layer-name');
            setBaseLayer(name);
            if (container) container.classList.remove('is-open');
        });
    });

    var labelsToggle = document.getElementById('basemapLabelsToggle');
    if (labelsToggle) {
        labelsToggle.addEventListener('change', function() {
            setSatelliteLabels(this.checked);
        });
    }

    map.on('click', function(event) {
        if (container) container.classList.remove('is-open');
        if (event && event.latlng) {
            reverseLookupFromMapClick(event.latlng);
        }
    });
}

function initMap() {
    map = L.map('map-canvas', {
        center: myPoint,
        zoom: 5,
        zoomControl: false
    });

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    baseLayers = {
        'Map view': getThemeAwareMapTileLayer(),
        'Satellite view': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: 'Tiles &copy; Esri'
        }),
        'Terrain view': L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 17,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>, <a href="https://opentopomap.org">OpenTopoMap</a>'
        })
    };

    labelOverlay = getThemeAwareLabelOverlay();

    var savedBaseLayer = window.localStorage ? localStorage.getItem('wilayahBaseLayer') : null;
    var labelsEnabled = window.localStorage ? localStorage.getItem('wilayahSatelliteLabels') === '1' : false;
    var prefersLightTheme = document.body && document.body.classList.contains('theme-light');
    var defaultBaseLayer = prefersLightTheme ? 'Map view' : 'Satellite view';
    currentBaseLayer = baseLayers[savedBaseLayer] ? savedBaseLayer : defaultBaseLayer;
    baseLayers[currentBaseLayer].addTo(map);
    initBaseMapMenu();
    setBaseLayer(currentBaseLayer);
    setSatelliteLabels(currentBaseLayer === 'Satellite view' && labelsEnabled);

    marker = L.marker(myPoint).bindPopup('DKI Jakarta').addTo(map);
    map.setView(myPoint, 5);
}

// --- AJAX response handler ---
function stateChanged() {
    var n = ids.length;
    // Map ID length to wil index: 2→1(kota), 5→2(kec), 8→3(kel)
    var idx = (n == 2 ? 1 : (n == 5 ? 2 : (n == 8 ? 3 : -1)));
    var prop = document.getElementById("prop");

    if (my_ajax.readyState == 4) {
        var data = my_ajax.responseText;
        var d;
        try {
            d = JSON.parse(data);
        } catch(e) {
            document.getElementById('preload').classList.remove('is-visible');
            return;
        }

        // Cascade visibility: show target, hide deeper levels, reset them
        if (n == 2) {
            document.getElementById("kab_box").style.display = 'block';
            document.getElementById("kec_box").style.display = 'none';
            document.getElementById("kel_box").style.display = 'none';
            document.getElementById("kec").innerHTML = "<option value=''>Pilih Kecamatan</option>";
            document.getElementById("kel").innerHTML = "<option value=''>Pilih Desa</option>";
        } else if (n == 5) {
            document.getElementById("kec_box").style.display = 'block';
            document.getElementById("kel_box").style.display = 'none';
            document.getElementById("kel").innerHTML = "<option value=''>Pilih Desa</option>";
        } else if (n == 8) {
            document.getElementById("kel_box").style.display = 'block';
        }

        // Update the appropriate dropdown and focus it
        if (idx > 0) {
            var sel = document.getElementById(wil[idx]);
            if (sel && d.opt) {
                sel.innerHTML = d.opt;
                sel.focus();
            }
        }

        // Update info strip
        if (d.data) {
            document.getElementById('n_kode').textContent = d.data.kode || '—';
            document.getElementById('n_lat').textContent = d.data.lat ? parseFloat(d.data.lat).toFixed(5) : '—';
            document.getElementById('n_lng').textContent = d.data.lng ? parseFloat(d.data.lng).toFixed(5) : '—';
        }

        // Update map with location data
        if (d.data && d.data.lat) {
            updateMap(d.data);
        }

        document.getElementById('preload').classList.remove('is-visible');
    }
}

function updateMap(data) {
    if (!map) return;

    var lat = parseFloat(data.lat);
    var lng = parseFloat(data.lng);
    var point = [lat, lng];

    // Remove old layers
    if (polyLayer) {
        map.removeLayer(polyLayer);
        polyLayer = null;
    }
    if (marker) {
        map.removeLayer(marker);
    }

    // Add marker
    marker = L.marker(point).bindPopup(
        '<b>' + (data.nama || '') + '</b><br>' +
        'Kode <b>' + (data.kode || '') + '</b><br>' +
        'Luas: <b>' + (data.luas || 0) + '</b> km<sup>2</sup><br>' +
        'Penduduk: <b>' + (data.penduduk || 0) + '</b>'
    ).addTo(map);

    // Add polygon if path exists
    if (data.path && data.path.length > 0) {
        try {
            polyLatLngs = JSON.parse(data.path);
            var polygonColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#4AB9FF';
            var isLightTheme = document.body && document.body.classList.contains('theme-light');
            var haloColor = isLightTheme ? '#0F172A' : '#FFFFFF';
            var haloOpacity = isLightTheme ? 0.18 : 0.42;
            var haloWeight = isLightTheme ? 6 : 7;
            var mainWeight = isLightTheme ? 4.5 : 4;
            var mainOpacity = isLightTheme ? 1 : 0.95;
            var mainFillOpacity = isLightTheme ? 0.1 : 0.14;

            var polygonHalo = L.polygon(polyLatLngs, {
                color: haloColor,
                fill: false,
                weight: haloWeight,
                opacity: haloOpacity,
                lineJoin: 'round'
            });
            var polygonMain = L.polygon(polyLatLngs, {
                color: polygonColor,
                fillColor: polygonColor,
                fillOpacity: mainFillOpacity,
                weight: mainWeight,
                opacity: mainOpacity,
                lineJoin: 'round'
            });
            polyLayer = L.layerGroup([polygonHalo, polygonMain]).addTo(map);
        } catch(e) {}
    }

    // Fly to location
    map.flyTo(point, data.path ? 9 : 5);
}

// --- Init on load ---
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    window.refreshMapThemeAwareness = refreshMapThemeAwareness;
});
