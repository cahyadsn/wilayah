<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_js.php
purpose  : JavaScript for AJAX cascade, map, and counters
create   : 170912
last edit: 2026-11-12 09:00:00
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
require_once __DIR__ . '/session.php';
header("Content-type: text/javascript");
header('Cache-Control: public, max-age=31536000');
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
header('Pragma: cache');
?>

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/* ============================================================
   GEOSCRIPT — Data Wilayah v2.8
   Pure JS, no GSAP dependency
   ============================================================ */

var ids;
var wil = new Array('prov', 'kota', 'kec', 'kel');
var activeIslands = [];
var islandMarker = null;
var isHydrating = false;
var toastTimer = null;
var thematicMode = 'none';
var thematicGeoJsonLayer = null;
var thematicLegendControl = null;
var thematicMenuControl = null;
var currentThematicProv = '0';
var thematicData = null;

function resetIslandPanel() {
    activeIslands = [];
    var countEl = document.getElementById('islandCount');
    var locNameEl = document.getElementById('islandLocationName');
    var searchWrap = document.getElementById('islandSearchWrapper');
    var emptyEl = document.getElementById('islandEmptyState');
    var listEl = document.getElementById('islandList');
    var searchInput = document.getElementById('islandSearch');

    if (countEl) countEl.textContent = '0 pulau';
    if (locNameEl) locNameEl.textContent = 'Pilih Provinsi / Kab / Kota';
    if (searchWrap) searchWrap.style.display = 'none';
    if (searchInput) searchInput.value = '';
    if (listEl) {
        listEl.innerHTML = '';
        listEl.style.display = 'none';
    }
    if (emptyEl) {
        emptyEl.textContent = 'Pilih provinsi atau kabupaten/kota untuk melihat daftar pulau';
        emptyEl.style.display = 'block';
    }
    if (islandMarker && map) {
        map.removeLayer(islandMarker);
        islandMarker = null;
    }
}

function updateIslandPanel(pulauList, locationName) {
    activeIslands = Array.isArray(pulauList) ? pulauList : [];
    var countEl = document.getElementById('islandCount');
    var locNameEl = document.getElementById('islandLocationName');
    var searchWrap = document.getElementById('islandSearchWrapper');
    var emptyEl = document.getElementById('islandEmptyState');
    var listEl = document.getElementById('islandList');
    var searchInput = document.getElementById('islandSearch');

    var count = activeIslands.length;
    if (countEl) countEl.textContent = count + ' pulau';
    if (locNameEl) locNameEl.textContent = locationName || (count > 0 ? 'Wilayah Aktif' : 'Pilih Provinsi / Kab / Kota');
    if (searchInput) searchInput.value = '';

    if (islandMarker && map) {
        map.removeLayer(islandMarker);
        islandMarker = null;
    }

    if (count === 0) {
        if (searchWrap) searchWrap.style.display = 'none';
        if (listEl) {
            listEl.innerHTML = '';
            listEl.style.display = 'none';
        }
        if (emptyEl) {
            emptyEl.textContent = 'Tidak ada data pulau untuk wilayah ini';
            emptyEl.style.display = 'block';
        }
        return;
    }

    if (searchWrap) searchWrap.style.display = 'block';
    if (emptyEl) emptyEl.style.display = 'none';
    if (listEl) {
        listEl.style.display = 'flex';
        renderIslandItems(activeIslands);
    }
}

function renderIslandItems(items) {
    var listEl = document.getElementById('islandList');
    var emptyEl = document.getElementById('islandEmptyState');
    if (!listEl) return;

    listEl.innerHTML = '';

    if (!items || items.length === 0) {
        if (emptyEl) {
            emptyEl.textContent = 'Tidak ada pulau yang cocok dengan pencarian';
            emptyEl.style.display = 'block';
        }
        listEl.style.display = 'none';
        return;
    }

    if (emptyEl) emptyEl.style.display = 'none';
    listEl.style.display = 'flex';

    var fragment = document.createDocumentFragment();
    items.forEach(function(item) {
        var div = document.createElement('div');
        div.className = 'island-item';
        div.setAttribute('role', 'button');
        div.setAttribute('tabindex', '0');

        var header = document.createElement('div');
        header.className = 'island-item-header';

        var codeSpan = document.createElement('span');
        codeSpan.className = 'island-code';
        codeSpan.textContent = item.kode || '';
        header.appendChild(codeSpan);

        if (item.status) {
            var statusSpan = document.createElement('span');
            statusSpan.className = 'island-status-tag';
            statusSpan.textContent = item.status;
            header.appendChild(statusSpan);
        }

        var nameDiv = document.createElement('div');
        nameDiv.className = 'island-name';
        nameDiv.textContent = item.nama || '';

        div.appendChild(header);
        div.appendChild(nameDiv);

        if (item.luas !== null && item.luas !== undefined) {
            var metaDiv = document.createElement('div');
            metaDiv.className = 'island-meta';
            metaDiv.textContent = 'Luas: ' + item.luas + ' km²';
            div.appendChild(metaDiv);
        }

        div.addEventListener('click', function() {
            onSelectIsland(item, div);
        });

        div.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                onSelectIsland(item, div);
            }
        });

        fragment.appendChild(div);
    });

    listEl.appendChild(fragment);
}

function onSelectIsland(item, element) {
    var activeItems = document.querySelectorAll('.island-item.is-active');
    activeItems.forEach(function(el) { el.classList.remove('is-active'); });
    if (element) element.classList.add('is-active');

    if (item.kode) {
        var nKode = document.getElementById('n_kode');
        if (nKode) nKode.textContent = item.kode;
    }

    if (item.lat !== null && item.lat !== undefined && item.lng !== null && item.lng !== undefined) {
        var lat = parseFloat(item.lat);
        var lng = parseFloat(item.lng);
        if (!isNaN(lat) && !isNaN(lng)) {
            var nLat = document.getElementById('n_lat');
            var nLng = document.getElementById('n_lng');
            if (nLat) nLat.textContent = lat.toFixed(5);
            if (nLng) nLng.textContent = lng.toFixed(5);

            if (map) {
                if (islandMarker) {
                    map.removeLayer(islandMarker);
                }
                var popupHtml = '<b>' + escapeHtml(item.nama) + '</b><br>' +
                    'Kode: <b>' + escapeHtml(item.kode) + '</b>' +
                    (item.status ? '<br>Status: <b>' + escapeHtml(item.status) + '</b>' : '') +
                    (item.luas !== null && item.luas !== undefined ? '<br>Luas: <b>' + escapeHtml(item.luas) + '</b> km<sup>2</sup>' : '');

                islandMarker = L.marker([lat, lng]).bindPopup(popupHtml).addTo(map);
                islandMarker.openPopup();
                map.flyTo([lat, lng], 13);
            }
        }
    }
}

function filterIslands(query) {
    if (!query) {
        renderIslandItems(activeIslands);
        return;
    }
    var q = query.toLowerCase().trim();
    var filtered = activeIslands.filter(function(item) {
        return (item.nama && item.nama.toLowerCase().indexOf(q) !== -1) ||
               (item.kode && item.kode.toLowerCase().indexOf(q) !== -1);
    });
    renderIslandItems(filtered);
}

function setSafeSelectOptions(selectElement, optionsHtml) {
    selectElement.innerHTML = '';
    var parser = new DOMParser();
    var doc = parser.parseFromString('<select>' + (optionsHtml || '') + '</select>', 'text/html');
    var options = doc.querySelectorAll('option');
    var fragment = document.createDocumentFragment();
    for (var i = 0; i < options.length; i++) {
        var opt = document.createElement('option');
        opt.value = options[i].getAttribute('value') || '';
        opt.textContent = options[i].textContent;
        if (options[i].hasAttribute('selected')) {
            opt.setAttribute('selected', 'selected');
        }
        fragment.appendChild(opt);
    }
    selectElement.appendChild(fragment);
}

// --- AJAX helpers ---

var my_ajax = do_ajax();

function ajax(id) {
    if (!id) {
        document.getElementById("kab_box").style.display = 'none';
        document.getElementById("kec_box").style.display = 'none';
        document.getElementById("kel_box").style.display = 'none';
        document.getElementById("kota").innerHTML = "<option value=''>Pilih Kota</option>";
        document.getElementById("kec").innerHTML = "<option value=''>Pilih Kecamatan</option>";
        document.getElementById("kel").innerHTML = "<option value=''>Pilih Desa</option>";
        document.getElementById('n_kode').textContent = '—';
        document.getElementById('n_lat').textContent = '—';
        document.getElementById('n_lng').textContent = '—';
        if (polyLayer && map) {
            map.removeLayer(polyLayer);
            polyLayer = null;
        }
        resetIslandPanel();
        return;
    }
    var preloadEl = document.getElementById('preload');
    if (preloadEl) preloadEl.classList.add('is-visible');
    ids = id;
    var url = "inc/geo_ajax.php?id=" + id + "&sid=" + Math.random();
    my_ajax.onreadystatechange = stateChanged;
    my_ajax.open("GET", url, true);
    my_ajax.send(null);
}

function geo(id) {
    var preloadEl = document.getElementById('preload');
    if (preloadEl) preloadEl.classList.add('is-visible');
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
            if (select && result.opt) setSafeSelectOptions(select, result.opt);
            if (box) box.style.display = 'block';
            if (result.pulau !== undefined) {
                updateIslandPanel(result.pulau, result.data ? result.data.nama : '');
            }
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
var polyLatLngs, polyLayer, map, marker, baseLayers, labelOverlay, currentBaseLayer, baseMapMenuControl, cachedBasemapOptions;
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

    if (currentBaseLayer && baseLayers[currentBaseLayer]) {
        if (map.hasLayer(baseLayers[currentBaseLayer])) {
            map.removeLayer(baseLayers[currentBaseLayer]);
        }
    } else {
        Object.keys(baseLayers).forEach(function(layerName) {
            if (map.hasLayer(baseLayers[layerName])) {
                map.removeLayer(baseLayers[layerName]);
            }
        });
    }

    baseLayers[name].addTo(map);
    currentBaseLayer = name;

    if (window.localStorage) {
        localStorage.setItem('wilayahBaseLayer', currentBaseLayer);
    }

    cachedBasemapOptions = cachedBasemapOptions || document.querySelectorAll('[data-basemap-option]');
    cachedBasemapOptions.forEach(function(option) {
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
            '<button type="button" class="basemap-menu-toggle" id="basemapMenuToggle" aria-label="Buka pilihan peta dasar" title="Peta dasar" aria-expanded="false">',
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
            var isOpen = container.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    cachedBasemapOptions = cachedBasemapOptions || document.querySelectorAll('[data-basemap-option]');
    cachedBasemapOptions.forEach(function(option) {
        option.addEventListener('click', function() {
            var name = this.getAttribute('data-layer-name');
            setBaseLayer(name);
            if (container) {
                container.classList.remove('is-open');
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });

    var labelsToggle = document.getElementById('basemapLabelsToggle');
    if (labelsToggle) {
        labelsToggle.addEventListener('change', function() {
            setSatelliteLabels(this.checked);
        });
    }

    map.on('click', function(event) {
        if (container) {
            container.classList.remove('is-open');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
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
                setSafeSelectOptions(sel, d.opt);
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

        // Update island panel
        if (d.pulau !== undefined) {
            updateIslandPanel(d.pulau, d.data ? d.data.nama : '');
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
        '<b>' + escapeHtml(data.nama) + '</b><br>' +
        'Kode <b>' + escapeHtml(data.kode) + '</b><br>' +
        'Luas: <b>' + escapeHtml(data.luas || 0) + '</b> km<sup>2</sup><br>' +
        'Penduduk: <b>' + escapeHtml(data.penduduk || 0) + '</b>'
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

// ============================================================
// GLOBAL SEARCH AUTOCOMPLETE (P1)
// ============================================================
var searchDebounceTimer = null;
var activeSearchResultIndex = -1;
var currentSearchResults = [];

function initGlobalSearch() {
    var searchInput = document.getElementById('globalSearchInput');
    var searchClear = document.getElementById('globalSearchClear');
    var searchSpinner = document.getElementById('globalSearchSpinner');
    var searchResults = document.getElementById('globalSearchResults');

    if (!searchInput || !searchResults) return;

    searchInput.addEventListener('input', function() {
        var val = this.value.trim();
        if (searchClear) {
            searchClear.style.display = val.length > 0 ? 'block' : 'none';
        }
        if (searchDebounceTimer) {
            clearTimeout(searchDebounceTimer);
        }

        if (val.length < 2) {
            closeSearchResults();
            if (searchSpinner) searchSpinner.style.display = 'none';
            return;
        }

        if (searchSpinner) searchSpinner.style.display = 'block';

        searchDebounceTimer = setTimeout(function() {
            executeGlobalSearch(val);
        }, 250);
    });

    searchInput.addEventListener('keydown', function(e) {
        if (!searchResults || searchResults.style.display === 'none') {
            if (e.key === 'ArrowDown' && this.value.trim().length >= 2) {
                executeGlobalSearch(this.value.trim());
            }
            return;
        }

        var items = searchResults.querySelectorAll('.global-search-item');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeSearchResultIndex = (activeSearchResultIndex + 1) % items.length;
            updateActiveSearchItem(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeSearchResultIndex = (activeSearchResultIndex - 1 + items.length) % items.length;
            updateActiveSearchItem(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeSearchResultIndex >= 0 && activeSearchResultIndex < currentSearchResults.length) {
                selectSearchResult(currentSearchResults[activeSearchResultIndex]);
            }
        } else if (e.key === 'Escape') {
            closeSearchResults();
        }
    });

    if (searchClear) {
        searchClear.addEventListener('click', function() {
            searchInput.value = '';
            searchClear.style.display = 'none';
            closeSearchResults();
            searchInput.focus();
        });
    }

    document.addEventListener('click', function(e) {
        var wrapper = document.getElementById('globalSearchWrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            closeSearchResults();
        }
    });
}

function updateActiveSearchItem(items) {
    items.forEach(function(el, idx) {
        if (idx === activeSearchResultIndex) {
            el.classList.add('is-active');
            el.scrollIntoView({ block: 'nearest' });
        } else {
            el.classList.remove('is-active');
        }
    });
}

function closeSearchResults() {
    var searchResults = document.getElementById('globalSearchResults');
    var searchInput = document.getElementById('globalSearchInput');
    if (searchResults) {
        searchResults.style.display = 'none';
        searchResults.innerHTML = '';
    }
    if (searchInput) {
        searchInput.setAttribute('aria-expanded', 'false');
    }
    activeSearchResultIndex = -1;
    currentSearchResults = [];
}

function executeGlobalSearch(query) {
    var searchSpinner = document.getElementById('globalSearchSpinner');
    var searchInput = document.getElementById('globalSearchInput');
    var url = 'inc/geo_search.php?q=' + encodeURIComponent(query) + '&limit=8&sid=' + Math.random();

    fetchWilayah(url)
        .then(function(res) {
            if (searchSpinner) searchSpinner.style.display = 'none';
            if (!res || !res.status) {
                renderSearchEmpty('Terjadi kesalahan pencarian');
                return;
            }
            renderSearchResults(res.results || [], query);
        })
        .catch(function() {
            if (searchSpinner) searchSpinner.style.display = 'none';
            renderSearchEmpty('Gagal memuat hasil pencarian');
        });
}

function renderSearchEmpty(message) {
    var searchResults = document.getElementById('globalSearchResults');
    var searchInput = document.getElementById('globalSearchInput');
    if (!searchResults) return;

    searchResults.innerHTML = '<div class="global-search-empty">' + escapeHtml(message) + '</div>';
    searchResults.style.display = 'block';
    if (searchInput) searchInput.setAttribute('aria-expanded', 'true');
    currentSearchResults = [];
    activeSearchResultIndex = -1;
}

function renderSearchResults(results, query) {
    var searchResults = document.getElementById('globalSearchResults');
    var searchInput = document.getElementById('globalSearchInput');
    if (!searchResults) return;

    searchResults.innerHTML = '';
    currentSearchResults = results;
    activeSearchResultIndex = -1;

    if (!results || results.length === 0) {
        renderSearchEmpty('Tidak ada wilayah atau pulau yang cocok');
        return;
    }

    var fragment = document.createDocumentFragment();
    results.forEach(function(item, idx) {
        var el = document.createElement('div');
        el.className = 'global-search-item';
        el.setAttribute('role', 'option');
        el.setAttribute('data-index', idx);

        var header = document.createElement('div');
        header.className = 'search-item-header';

        var badgeClass = 'badge-' + (item.category === 'pulau' ? 'pulau' : (item.tipe || '').toLowerCase().replace(/[^a-z]/g, ''));
        var badge = document.createElement('span');
        badge.className = 'search-item-badge ' + badgeClass;
        badge.textContent = item.tipe || 'Wilayah';

        var code = document.createElement('span');
        code.className = 'search-item-code';
        code.textContent = item.kode || '';

        header.appendChild(badge);
        header.appendChild(code);

        var nameDiv = document.createElement('div');
        nameDiv.className = 'search-item-name';
        nameDiv.textContent = item.nama || '';

        var parentDiv = document.createElement('div');
        parentDiv.className = 'search-item-parent';
        parentDiv.textContent = item.parent || '';

        el.appendChild(header);
        el.appendChild(nameDiv);
        if (item.parent) {
            el.appendChild(parentDiv);
        }

        el.addEventListener('click', function() {
            selectSearchResult(item);
        });

        fragment.appendChild(el);
    });

    searchResults.appendChild(fragment);
    searchResults.style.display = 'block';
    if (searchInput) searchInput.setAttribute('aria-expanded', 'true');
}

function selectSearchResult(item) {
    var searchInput = document.getElementById('globalSearchInput');
    if (searchInput) {
        searchInput.value = item.nama + ' (' + item.kode + ')';
    }
    closeSearchResults();

    if (item.category === 'pulau') {
        if (item.chain) {
            applyReverseSelection(item.chain)
                .then(function() {
                    onSelectIsland(item);
                });
        } else {
            onSelectIsland(item);
        }
    } else {
        applyReverseSelection(item.chain, { kode: item.kode, nama: item.nama });
    }
}


// ============================================================
// SHAREABLE URL & DEEP LINKING (P2)
// ============================================================

function getUrlParams(search) {
    var query = (typeof search === 'string') ? search : (window.location ? window.location.search : '');
    var searchParams = new URLSearchParams(query);
    var kode = searchParams.get('kode');
    var pulau = searchParams.get('pulau');
    var theme = searchParams.get('theme');
    var zoom = searchParams.get('zoom') || searchParams.get('z');
    var lat = searchParams.get('lat');
    var lng = searchParams.get('lng');

    var validKode = (kode && /^[0-9]+(\.[0-9]+)*$/.test(kode) && kode.length <= 13) ? kode : null;
    var validPulau = (pulau && /^[0-9]{2}\.[0-9]{2}\.[0-9]{5}$/.test(pulau)) ? pulau : null;
    var validTheme = (theme === 'light' || theme === 'dark') ? theme : null;
    var thematic = searchParams.get('thematic');
    var validThematic = (thematic === 'density' || thematic === 'sex_ratio') ? thematic : null;

    var validZoom = (zoom && /^[0-9]{1,2}$/.test(zoom)) ? parseInt(zoom, 10) : null;
    if (validZoom !== null && (validZoom < 1 || validZoom > 20)) validZoom = null;

    var validLat = (lat && /^-?[0-9]+(\.[0-9]+)?$/.test(lat)) ? parseFloat(lat) : null;
    if (validLat !== null && (validLat < -90 || validLat > 90)) validLat = null;

    var validLng = (lng && /^-?[0-9]+(\.[0-9]+)?$/.test(lng)) ? parseFloat(lng) : null;
    if (validLng !== null && (validLng < -180 || validLng > 180)) validLng = null;

    return {
        kode: validKode,
        pulau: validPulau,
        theme: validTheme,
        zoom: validZoom,
        lat: validLat,
        lng: validLng,
        thematic: validThematic
    };
}

function updateUrlState(params, push) {
    if (!window.location || !window.history || isHydrating) return;
    try {
        var url = new URL(window.location.href);
        if (params) {
            if (params.kode !== undefined) {
                if (params.kode && /^[0-9]+(\.[0-9]+)*$/.test(params.kode) && params.kode.length <= 13) {
                    url.searchParams.set('kode', params.kode);
                } else {
                    url.searchParams.delete('kode');
                }
            }
            if (params.pulau !== undefined) {
                if (params.pulau && /^[0-9]{2}\.[0-9]{2}\.[0-9]{5}$/.test(params.pulau)) {
                    url.searchParams.set('pulau', params.pulau);
                } else {
                    url.searchParams.delete('pulau');
                }
            }
            if (params.theme !== undefined) {
                if (params.theme === 'light' || params.theme === 'dark') {
                    url.searchParams.set('theme', params.theme);
                } else {
                    url.searchParams.delete('theme');
                }
            }
            if (params.thematic !== undefined) {
                if (params.thematic === 'density' || params.thematic === 'sex_ratio') {
                    url.searchParams.set('thematic', params.thematic);
                } else {
                    url.searchParams.delete('thematic');
                }
            }
            if (params.zoom !== undefined) {
                if (params.zoom !== null && !isNaN(params.zoom)) {
                    url.searchParams.set('zoom', String(params.zoom));
                } else {
                    url.searchParams.delete('zoom');
                }
            }
            if (params.lat !== undefined) {
                if (params.lat !== null && !isNaN(params.lat)) {
                    url.searchParams.set('lat', parseFloat(params.lat).toFixed(5));
                } else {
                    url.searchParams.delete('lat');
                }
            }
            if (params.lng !== undefined) {
                if (params.lng !== null && !isNaN(params.lng)) {
                    url.searchParams.set('lng', parseFloat(params.lng).toFixed(5));
                } else {
                    url.searchParams.delete('lng');
                }
            }
        }
        var searchStr = url.searchParams.toString();
        var newUrl = url.pathname + (searchStr ? '?' + searchStr : '') + url.hash;
        if (push) {
            window.history.pushState({}, '', newUrl);
        } else {
            window.history.replaceState({}, '', newUrl);
        }
    } catch (e) {
        // Safe fallback in non-browser or test environments
    }
}

function buildChainFromKode(kode) {
    if (!kode || typeof kode !== 'string' || !/^[0-9]+(\.[0-9]+)*$/.test(kode)) return null;
    var parts = kode.split('.');
    var chain = {};
    if (parts.length >= 1 && parts[0].length === 2) {
        chain.prov = { kode: parts[0] };
    }
    if (parts.length >= 2) {
        chain.kab = { kode: parts[0] + '.' + parts[1] };
    }
    if (parts.length >= 3) {
        chain.kec = { kode: parts[0] + '.' + parts[1] + '.' + parts[2] };
    }
    if (parts.length >= 4) {
        chain.kel = { kode: kode };
    }
    return chain;
}

function showToast(message, duration) {
    var toast = document.getElementById('toastNotification');
    var toastMsg = document.getElementById('toastMessage');
    if (!toast) return;
    if (toastMsg) {
        toastMsg.textContent = message || 'Tautan berhasil disalin ke clipboard!';
    }
    toast.classList.add('is-visible');
    if (toastTimer) {
        clearTimeout(toastTimer);
    }
    toastTimer = setTimeout(function() {
        toast.classList.remove('is-visible');
    }, duration || 2500);
}

function copyOrShareUrl() {
    var shareUrl = window.location ? window.location.href : '';
    if (!shareUrl) return;

    if (navigator.share && /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent || '')) {
        navigator.share({
            title: document.title || 'Data Wilayah Indonesia',
            text: 'Data Wilayah & Pulau Indonesia: ' + shareUrl,
            url: shareUrl
        }).catch(function(err) {
            if (err && err.name !== 'AbortError') {
                copyUrlToClipboard(shareUrl);
            }
        });
    } else {
        copyUrlToClipboard(shareUrl);
    }
}

function copyUrlToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showToast('Tautan berhasil disalin ke clipboard!');
        }).catch(function() {
            fallbackCopyUrl(text);
        });
    } else {
        fallbackCopyUrl(text);
    }
}

function fallbackCopyUrl(text) {
    try {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.top = '-9999px';
        textArea.style.left = '-9999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        var success = document.execCommand('copy');
        document.body.removeChild(textArea);
        if (success) {
            showToast('Tautan berhasil disalin ke clipboard!');
        } else {
            showToast('Gagal menyalin tautan');
        }
    } catch (e) {
        showToast('Gagal menyalin tautan');
    }
}

function initShareButtons() {
    var btnShare = document.getElementById('btnShareLink');
    if (btnShare) {
        btnShare.addEventListener('click', function(e) {
            e.preventDefault();
            copyOrShareUrl();
        });
    }

    var btnCopy = document.getElementById('btnCopyLocation');
    if (btnCopy) {
        btnCopy.addEventListener('click', function(e) {
            e.preventDefault();
            copyOrShareUrl();
        });
    }
}

function hydrateFromUrl() {
    var params = getUrlParams();
    if (!params) return Promise.resolve();

    if (params.theme && typeof window.applyTheme === 'function') {
        window.applyTheme(params.theme);
    }
    if (params.thematic) {
        setThematicMode(params.thematic);
    }

    if (params.pulau) {
        isHydrating = true;
        return fetchWilayah('inc/geo_search.php?q=' + encodeURIComponent(params.pulau) + '&limit=1&sid=' + Math.random())
            .then(function(res) {
                if (res && res.status && res.results && res.results.length > 0) {
                    var item = res.results[0];
                    if (item.category === 'pulau' && item.kode === params.pulau) {
                        if (item.chain) {
                            return applyReverseSelection(item.chain).then(function() {
                                onSelectIsland(item);
                            });
                        } else {
                            onSelectIsland(item);
                            return null;
                        }
                    }
                }
                return null;
            })
            .catch(function(err) {
                console.error('Failed to hydrate island:', err);
            })
            .finally(function() {
                isHydrating = false;
            });
    }

    if (params.kode) {
        isHydrating = true;
        var chain = buildChainFromKode(params.kode);
        return applyReverseSelection(chain, { kode: params.kode })
            .catch(function(err) {
                console.error('Failed to hydrate region:', err);
            })
            .finally(function() {
                isHydrating = false;
            });
    }

    if (params.lat !== null && params.lng !== null && map) {
        map.setView([params.lat, params.lng], params.zoom || 13);
    }

    return Promise.resolve();
}


// ============================================================
// CHOROPLETH MAP & DEMOGRAPHICS (P3)
// ============================================================

function getThematicColorDensity(d) {
    return d > 5000 ? '#bd0026' :
           d > 1500 ? '#f03b20' :
           d > 750  ? '#fd8d3c' :
           d > 250  ? '#fecc5c' :
                      '#ffffb2';
}

function getThematicColorSexRatio(r) {
    return r > 105 ? '#d73027' :
           r > 102 ? '#fc8d59' :
           r > 98  ? '#fee08b' :
           r > 95  ? '#91bfdb' :
                     '#4575b4';
}

function getThematicColor(val, metric) {
    if (metric === 'sex_ratio') {
        return getThematicColorSexRatio(val);
    }
    return getThematicColorDensity(val);
}

function formatThematicNumber(num) {
    if (num === null || num === undefined || isNaN(num)) return '0';
    var parts = String(num).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return parts.join(',');
}

function thematicFeatureStyle(feature) {
    var val = feature && feature.properties ? feature.properties.value : 0;
    var color = getThematicColor(val, thematicMode);
    return {
        fillColor: color,
        weight: 1.5,
        opacity: 1,
        color: '#ffffff',
        dashArray: '',
        fillOpacity: 0.72
    };
}

function onEachThematicFeature(feature, layer) {
    var p = feature.properties || {};
    var isSexRatio = (thematicMode === 'sex_ratio');
    var unit = isSexRatio ? '' : ' jiwa/km²';
    var metricLabel = isSexRatio ? 'Sex Ratio' : 'Kepadatan';
    var metricVal = isSexRatio ? (p.sex_ratio !== null ? p.sex_ratio : '—') : formatThematicNumber(p.kepadatan);

    var tooltipContent = 
        '<div class="thematic-tooltip">' +
          '<div class="thematic-tooltip-header">' +
            '<span class="thematic-tooltip-code">' + escapeHtml(p.kode) + '</span>' +
            '<strong class="thematic-tooltip-title">' + escapeHtml(p.nama) + '</strong>' +
          '</div>' +
          '<div class="thematic-tooltip-body">' +
            '<div class="thematic-tooltip-row"><span>' + metricLabel + ':</span> <strong>' + metricVal + unit + '</strong></div>' +
            '<div class="thematic-tooltip-row"><span>Penduduk:</span> <span>' + formatThematicNumber(p.penduduk) + ' jiwa</span></div>' +
            '<div class="thematic-tooltip-row"><span>Luas Wilayah:</span> <span>' + formatThematicNumber(p.luas) + ' km²</span></div>' +
            (p.pria !== null && p.wanita !== null ? 
              '<div class="thematic-tooltip-sub">L: ' + formatThematicNumber(p.pria) + ' | P: ' + formatThematicNumber(p.wanita) + '</div>' : '') +
          '</div>' +
          '<div class="thematic-tooltip-footer">Klik untuk pilih wilayah ini</div>' +
        '</div>';

    layer.bindTooltip(tooltipContent, {
        sticky: true,
        direction: 'auto',
        className: 'thematic-custom-tooltip'
    });

    layer.on({
        mouseover: function(e) {
            var target = e.target;
            target.setStyle({
                weight: 3.5,
                color: '#38bdf8',
                fillOpacity: 0.88
            });
            if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                target.bringToFront();
            }
        },
        mouseout: function(e) {
            if (thematicGeoJsonLayer) {
                thematicGeoJsonLayer.resetStyle(e.target);
            }
        },
        click: function(e) {
            if (e && e.originalEvent) {
                L.DomEvent.stopPropagation(e.originalEvent);
            }
            if (p.kode) {
                if (p.kode.length === 2) {
                    setSelectValue('prop', p.kode);
                    ajax(p.kode);
                } else if (p.kode.length === 5) {
                    var provCode = p.kode.substring(0, 2);
                    setSelectValue('prop', provCode);
                    loadChildren(provCode, 'kota', 'kab_box').then(function() {
                        setSelectValue('kota', p.kode);
                        loadChildren(p.kode, 'kec', 'kec_box');
                        geo(p.kode);
                    });
                }
            }
        }
    });
}

function loadThematicData(provCode, metric) {
    if (!map) return Promise.resolve(null);
    currentThematicProv = provCode || '0';
    var preloadEl = document.getElementById('preload');
    if (preloadEl) preloadEl.classList.add('is-visible');

    var url = 'inc/geo_thematic.php?prov=' + encodeURIComponent(currentThematicProv) + '&metric=' + encodeURIComponent(metric) + '&sid=' + Math.random();
    return fetchWilayah(url)
        .then(function(data) {
            if (!data || !data.features) return null;
            thematicData = data;

            if (thematicGeoJsonLayer && map.hasLayer(thematicGeoJsonLayer)) {
                map.removeLayer(thematicGeoJsonLayer);
            }

            if (thematicMode !== 'none') {
                thematicGeoJsonLayer = L.geoJSON(data, {
                    style: thematicFeatureStyle,
                    onEachFeature: onEachThematicFeature
                }).addTo(map);

                if (polyLayer && map.hasLayer(polyLayer)) {
                    map.removeLayer(polyLayer);
                }

                updateThematicLegend(data);
            }
            return data;
        })
        .catch(function(err) {
            console.error('Failed to load thematic data:', err);
            return null;
        })
        .finally(function() {
            if (preloadEl) preloadEl.classList.remove('is-visible');
        });
}

function setThematicMode(mode) {
    thematicMode = (mode === 'density' || mode === 'sex_ratio') ? mode : 'none';

    var labelEl = document.getElementById('thematicCurrentLabel');
    if (labelEl) {
        var labels = {
            'none': 'Tematik: Standar',
            'density': 'Kepadatan Penduduk',
            'sex_ratio': 'Rasio Gender'
        };
        labelEl.textContent = labels[thematicMode] || 'Tematik: Standar';
    }

    var options = document.querySelectorAll('[data-thematic-option]');
    options.forEach(function(opt) {
        var active = opt.getAttribute('data-thematic-name') === thematicMode;
        opt.classList.toggle('is-active', active);
        opt.setAttribute('aria-pressed', active ? 'true' : 'false');
    });

    if (thematicMode === 'none') {
        if (thematicGeoJsonLayer && map && map.hasLayer(thematicGeoJsonLayer)) {
            map.removeLayer(thematicGeoJsonLayer);
            thematicGeoJsonLayer = null;
        }
        if (thematicLegendControl && thematicLegendControl._container) {
            thematicLegendControl._container.style.display = 'none';
        }
        if (polyLayer && map && !map.hasLayer(polyLayer)) {
            map.addLayer(polyLayer);
        }
        if (!isHydrating) {
            updateUrlState({ thematic: null });
        }
        return;
    }

    var propEl = document.getElementById('prop');
    var targetProv = (propEl && propEl.value) ? propEl.value : '0';
    loadThematicData(targetProv, thematicMode);

    if (!isHydrating) {
        updateUrlState({ thematic: thematicMode });
    }
}

function initThematicLegend() {
    if (!map || thematicLegendControl) return;
    thematicLegendControl = L.control({ position: 'bottomleft' });
    thematicLegendControl.onAdd = function() {
        var div = L.DomUtil.create('div', 'thematic-legend');
        div.style.display = 'none';
        L.DomEvent.disableClickPropagation(div);
        L.DomEvent.disableScrollPropagation(div);
        return div;
    };
    thematicLegendControl.addTo(map);
}

function updateThematicLegend(data) {
    if (!thematicLegendControl) {
        initThematicLegend();
    }
    var container = thematicLegendControl ? thematicLegendControl._container : null;
    if (!container) return;

    if (thematicMode === 'none') {
        container.style.display = 'none';
        return;
    }

    var provName = (data && data.properties) ? data.properties.prov_nama : 'Wilayah';
    var html = '';
    if (thematicMode === 'density') {
        html = [
            '<div class="thematic-legend-title">Kepadatan Penduduk</div>',
            '<div class="thematic-legend-subtitle">' + escapeHtml(provName) + ' (jiwa/km²)</div>',
            '<div class="thematic-legend-item"><span class="thematic-legend-swatch" style="background:#bd0026"></span><span class="thematic-legend-label">&gt; 5.000</span></div>',
            '<div class="thematic-legend-item"><span class="thematic-legend-swatch" style="background:#f03b20"></span><span class="thematic-legend-label">1.500 – 5.000</span></div>',
            '<div class="thematic-legend-item"><span class="thematic-legend-swatch" style="background:#fd8d3c"></span><span class="thematic-legend-label">750 – 1.500</span></div>',
            '<div class="thematic-legend-item"><span class="thematic-legend-swatch" style="background:#fecc5c"></span><span class="thematic-legend-label">250 – 750</span></div>',
            '<div class="thematic-legend-item"><span class="thematic-legend-swatch" style="background:#ffffb2"></span><span class="thematic-legend-label">&lt; 250</span></div>'
        ].join('');
    } else if (thematicMode === 'sex_ratio') {
        html = [
            '<div class="thematic-legend-title">Rasio Jenis Kelamin</div>',
            '<div class="thematic-legend-subtitle">' + escapeHtml(provName) + ' (pria/100 wanita)</div>',
            '<div class="thematic-legend-item"><span class="thematic-legend-swatch" style="background:#d73027"></span><span class="thematic-legend-label">&gt; 105 (Pria &gt;&gt;)</span></div>',
            '<div class="thematic-legend-item"><span class="thematic-legend-swatch" style="background:#fc8d59"></span><span class="thematic-legend-label">102 – 105 (Pria &gt;)</span></div>',
            '<div class="thematic-legend-item"><span class="thematic-legend-swatch" style="background:#fee08b"></span><span class="thematic-legend-label">98 – 102 (Seimbang)</span></div>',
            '<div class="thematic-legend-item"><span class="thematic-legend-swatch" style="background:#91bfdb"></span><span class="thematic-legend-label">95 – 98 (Wanita &gt;)</span></div>',
            '<div class="thematic-legend-item"><span class="thematic-legend-swatch" style="background:#4575b4"></span><span class="thematic-legend-label">&lt; 95 (Wanita &gt;&gt;)</span></div>'
        ].join('');
    }

    container.innerHTML = html;
    container.style.display = 'block';
}

function initThematicMenu() {
    if (!map) return;
    thematicMenuControl = L.control({ position: 'topright' });
    thematicMenuControl.onAdd = function() {
        var container = L.DomUtil.create('div', 'leaflet-bar thematic-menu');
        container.innerHTML = [
            '<button type="button" class="thematic-menu-toggle" id="thematicMenuToggle" aria-label="Buka pilihan peta tematik" title="Peta Tematik" aria-expanded="false">',
            '  <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
            '  <span id="thematicCurrentLabel">Tematik: Standar</span>',
            '</button>',
            '<div class="thematic-menu-panel" id="thematicMenuPanel">',
            '  <button type="button" class="thematic-option is-active" data-thematic-option="1" data-thematic-name="none">',
            '    <span class="thematic-option-icon thematic-icon-none">⛶</span>',
            '    <span class="thematic-option-text"><strong>Standar</strong><small>Batas wilayah reguler</small></span>',
            '    <span class="thematic-check" aria-hidden="true">✓</span>',
            '  </button>',
            '  <button type="button" class="thematic-option" data-thematic-option="1" data-thematic-name="density">',
            '    <span class="thematic-option-icon thematic-icon-density">📊</span>',
            '    <span class="thematic-option-text"><strong>Kepadatan Penduduk</strong><small>Sebaran jiwa per km²</small></span>',
            '    <span class="thematic-check" aria-hidden="true">✓</span>',
            '  </button>',
            '  <button type="button" class="thematic-option" data-thematic-option="1" data-thematic-name="sex_ratio">',
            '    <span class="thematic-option-icon thematic-icon-gender">⚖</span>',
            '    <span class="thematic-option-text"><strong>Rasio Gender</strong><small>Perbandingan pria / wanita</small></span>',
            '    <span class="thematic-check" aria-hidden="true">✓</span>',
            '  </button>',
            '</div>'
        ].join('');

        L.DomEvent.disableClickPropagation(container);
        L.DomEvent.disableScrollPropagation(container);
        return container;
    };
    thematicMenuControl.addTo(map);

    var container = document.querySelector('.thematic-menu');
    var toggle = document.getElementById('thematicMenuToggle');

    if (toggle && container) {
        toggle.addEventListener('click', function() {
            var isOpen = container.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    var options = document.querySelectorAll('[data-thematic-option]');
    options.forEach(function(opt) {
        opt.addEventListener('click', function() {
            var name = this.getAttribute('data-thematic-name');
            setThematicMode(name);
            if (container) {
                container.classList.remove('is-open');
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });

    map.on('click', function() {
        if (container) {
            container.classList.remove('is-open');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
    });

    initThematicLegend();
}

// --- Init on load ---
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    initGlobalSearch();
    initShareButtons();
    hydrateFromUrl();

    window.refreshMapThemeAwareness = refreshMapThemeAwareness;
    window.filterIslands = filterIslands;
    window.updateIslandPanel = updateIslandPanel;
    window.resetIslandPanel = resetIslandPanel;
    window.initGlobalSearch = initGlobalSearch;
    window.renderSearchResults = renderSearchResults;
    window.selectSearchResult = selectSearchResult;
    window.closeSearchResults = closeSearchResults;

    // P2 exports
    window.getUrlParams = getUrlParams;
    window.updateUrlState = updateUrlState;
    window.hydrateFromUrl = hydrateFromUrl;
    window.showToast = showToast;
    window.copyOrShareUrl = copyOrShareUrl;
    window.buildChainFromKode = buildChainFromKode;
    window.initShareButtons = initShareButtons;

    // P3 exports
    window.getThematicColorDensity = getThematicColorDensity;
    window.getThematicColorSexRatio = getThematicColorSexRatio;
    window.getThematicColor = getThematicColor;
    window.setThematicMode = setThematicMode;
    window.loadThematicData = loadThematicData;
    window.initThematicMenu = initThematicMenu;
    window.thematicFeatureStyle = thematicFeatureStyle;
});

window.addEventListener('popstate', function() {
    hydrateFromUrl();
});
