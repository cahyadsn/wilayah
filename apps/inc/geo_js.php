<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_js.php
purpose  : JavaScript for AJAX cascade, map, and counters
create   : 170912
last edit: 2026-11-25 07:00:00
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
var islandClusterGroup = null;
var islandMarkerMap = {};
var isHydrating = false;
var toastTimer = null;
var thematicMode = 'none';
var thematicGeoJsonLayer = null;
var thematicLegendControl = null;
var thematicMenuControl = null;
var currentThematicProv = '0';
var thematicData = null;

function formatIslandPopupHtml(item, lat, lng) {
    return '<div class="island-popup">' +
        '<div class="island-popup-title">🏝️ ' + escapeHtml(item.nama || '') + '</div>' +
        '<div class="island-popup-meta">' +
        '<div><strong>Kode:</strong> <code>' + escapeHtml(item.kode || '') + '</code></div>' +
        (item.status ? '<div><strong>Status:</strong> <span class="island-status-tag">' + escapeHtml(item.status) + '</span></div>' : '') +
        (item.luas !== null && item.luas !== undefined ? '<div><strong>Luas:</strong> ' + escapeHtml(item.luas) + ' km²</div>' : '') +
        '<div><strong>Koordinat:</strong> ' + lat.toFixed(5) + '°, ' + lng.toFixed(5) + '°</div>' +
        '</div>' +
        '</div>';
}

function getIslandClusterGroup() {
    if (!islandClusterGroup && typeof L !== 'undefined' && typeof L.markerClusterGroup === 'function') {
        islandClusterGroup = L.markerClusterGroup({
            chunkedLoading: true,
            chunkInterval: 100,
            chunkDelay: 50,
            maxClusterRadius: 50,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true
        });
    }
    return islandClusterGroup;
}

function clearIslandClusters() {
    if (islandClusterGroup) {
        if (typeof islandClusterGroup.clearLayers === 'function') {
            islandClusterGroup.clearLayers();
        }
        if (map && typeof map.hasLayer === 'function' && map.hasLayer(islandClusterGroup)) {
            map.removeLayer(islandClusterGroup);
        }
    }
    islandMarkerMap = {};
}

function highlightIslandInSidebar(kode) {
    if (!kode) return;
    var activeItems = document.querySelectorAll('.island-item.is-active');
    activeItems.forEach(function(el) { el.classList.remove('is-active'); });

    var targetItem = document.querySelector('.island-item[data-kode="' + kode + '"]');
    if (targetItem) {
        targetItem.classList.add('is-active');
        if (typeof targetItem.scrollIntoView === 'function') {
            targetItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    var nKode = document.getElementById('n_kode');
    if (nKode) nKode.textContent = kode;

    for (var i = 0; i < activeIslands.length; i++) {
        if (activeIslands[i].kode === kode) {
            var item = activeIslands[i];
            var lat = parseFloat(item.lat);
            var lng = parseFloat(item.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                var nLat = document.getElementById('n_lat');
                var nLng = document.getElementById('n_lng');
                if (nLat) nLat.textContent = lat.toFixed(5);
                if (nLng) nLng.textContent = lng.toFixed(5);
            }
            break;
        }
    }
}

function populateIslandClusters(islandsData) {
    clearIslandClusters();
    if (!Array.isArray(islandsData) || islandsData.length === 0 || !map) return;

    var clusterGroup = getIslandClusterGroup();
    if (!clusterGroup) return;

    if (islandMarker && map) {
        map.removeLayer(islandMarker);
        islandMarker = null;
    }

    var markersToAdd = [];
    islandsData.forEach(function(item) {
        if (item && item.lat !== null && item.lat !== undefined && item.lng !== null && item.lng !== undefined) {
            var lat = parseFloat(item.lat);
            var lng = parseFloat(item.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                var popupHtml = formatIslandPopupHtml(item, lat, lng);
                var m = L.marker([lat, lng]).bindPopup(popupHtml);
                m.on('click', function() {
                    highlightIslandInSidebar(item.kode);
                });
                if (item.kode) {
                    islandMarkerMap[item.kode] = m;
                }
                markersToAdd.push(m);
            }
        }
    });

    if (markersToAdd.length > 0) {
        if (typeof clusterGroup.addLayers === 'function') {
            clusterGroup.addLayers(markersToAdd);
        } else if (typeof clusterGroup.addLayer === 'function') {
            markersToAdd.forEach(function(m) { clusterGroup.addLayer(m); });
        }
        if (map && typeof map.hasLayer === 'function' && !map.hasLayer(clusterGroup)) {
            map.addLayer(clusterGroup);
        }
    }
}

function initIslandClusterControls() {
    var toggle = document.getElementById('toggleShowAllIslands');
    if (!toggle) return;

    var savedPref = false;
    try {
        savedPref = localStorage.getItem('wilayahShowAllIslands') === '1';
    } catch (e) {}

    toggle.checked = savedPref;

    toggle.addEventListener('change', function() {
        var isChecked = toggle.checked;
        try {
            localStorage.setItem('wilayahShowAllIslands', isChecked ? '1' : '0');
        } catch (e) {}

        if (isChecked) {
            if (activeIslands && activeIslands.length > 0) {
                populateIslandClusters(activeIslands);
            }
        } else {
            clearIslandClusters();
        }
    });
}

function resetIslandPanel() {
    activeIslands = [];
    var countEl = document.getElementById('islandCount');
    var locNameEl = document.getElementById('islandLocationName');
    var toggleWrap = document.getElementById('islandClusterToggleWrapper');
    var searchWrap = document.getElementById('islandSearchWrapper');
    var emptyEl = document.getElementById('islandEmptyState');
    var listEl = document.getElementById('islandList');
    var searchInput = document.getElementById('islandSearch');

    if (countEl) countEl.textContent = '0 pulau';
    if (locNameEl) locNameEl.textContent = 'Pilih Provinsi / Kab / Kota';
    if (toggleWrap) toggleWrap.style.display = 'none';
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
    clearIslandClusters();
}

function updateIslandPanel(pulauList, locationName) {
    activeIslands = Array.isArray(pulauList) ? pulauList : [];
    var countEl = document.getElementById('islandCount');
    var locNameEl = document.getElementById('islandLocationName');
    var toggleWrap = document.getElementById('islandClusterToggleWrapper');
    var toggleInput = document.getElementById('toggleShowAllIslands');
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

    clearIslandClusters();

    if (count === 0) {
        if (toggleWrap) toggleWrap.style.display = 'none';
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

    if (toggleWrap) toggleWrap.style.display = 'flex';
    if (searchWrap) searchWrap.style.display = 'block';
    if (emptyEl) emptyEl.style.display = 'none';
    if (listEl) {
        listEl.style.display = 'flex';
        renderIslandItems(activeIslands);
    }

    if (toggleInput && toggleInput.checked) {
        populateIslandClusters(activeIslands);
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
        div.setAttribute('data-kode', item.kode || '');

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
    if (element) {
        element.classList.add('is-active');
    } else if (item && item.kode) {
        var targetEl = document.querySelector('.island-item[data-kode="' + item.kode + '"]');
        if (targetEl) targetEl.classList.add('is-active');
    }

    if (item && item.kode) {
        var nKode = document.getElementById('n_kode');
        if (nKode) nKode.textContent = item.kode;
    }

    if (item && item.lat !== null && item.lat !== undefined && item.lng !== null && item.lng !== undefined) {
        var lat = parseFloat(item.lat);
        var lng = parseFloat(item.lng);
        if (!isNaN(lat) && !isNaN(lng)) {
            var nLat = document.getElementById('n_lat');
            var nLng = document.getElementById('n_lng');
            if (nLat) nLat.textContent = lat.toFixed(5);
            if (nLng) nLng.textContent = lng.toFixed(5);

            if (map) {
                var clusterGroup = islandClusterGroup;
                var markerInCluster = (clusterGroup && item.kode && islandMarkerMap[item.kode]);
                var isClusterOnMap = (clusterGroup && typeof map.hasLayer === 'function' && map.hasLayer(clusterGroup));

                if (markerInCluster && isClusterOnMap) {
                    var m = islandMarkerMap[item.kode];
                    if (typeof clusterGroup.zoomToShowLayer === 'function') {
                        clusterGroup.zoomToShowLayer(m, function() {
                            m.openPopup();
                        });
                    } else {
                        m.openPopup();
                        map.flyTo([lat, lng], 13);
                    }
                } else {
                    if (islandMarker) {
                        map.removeLayer(islandMarker);
                    }
                    var popupHtml = formatIslandPopupHtml(item, lat, lng);
                    islandMarker = L.marker([lat, lng]).bindPopup(popupHtml).addTo(map);
                    islandMarker.openPopup();
                    map.flyTo([lat, lng], 13);
                }
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
            if (typeof measureActiveMode !== 'undefined' && measureActiveMode !== 'none') {
                handleMeasurementMapClick(event.latlng);
                return;
            }
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

    var compare = searchParams.get('compare');
    var validCompare = (compare && /^[0-9]{2}(\.[0-9]{2})?,[0-9]{2}(\.[0-9]{2})?$/.test(compare)) ? compare : null;

    return {
        kode: validKode,
        pulau: validPulau,
        theme: validTheme,
        zoom: validZoom,
        lat: validLat,
        lng: validLng,
        thematic: validThematic,
        compare: validCompare
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
            if (params.compare !== undefined) {
                if (params.compare && /^[0-9]{2}(\.[0-9]{2})?,[0-9]{2}(\.[0-9]{2})?$/.test(params.compare)) {
                    url.searchParams.set('compare', params.compare);
                } else {
                    url.searchParams.delete('compare');
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

    if (params.compare) {
        var compareCodes = params.compare.split(',');
        if (compareCodes.length === 2) {
            openCompareModal(compareCodes[0]);
            var targetLvl = (compareCodes[0].length === 2) ? 'provinsi' : 'kabkota';
            switchCompareLevel(targetLvl, compareCodes[0]);
            setCompareSelectValues(compareCodes[0], compareCodes[1]);
            return fetchComparisonData(compareCodes[0], compareCodes[1]).catch(function(err) {
                console.warn('Failed to hydrate comparison from URL:', err);
            });
        }
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


// ============================================================
// SPATIAL DATA EXPORTER (P4)
// ============================================================

function getActiveRegionCode() {
    var codeEl = document.getElementById('n_kode');
    if (codeEl && codeEl.textContent && codeEl.textContent !== '—') {
        var raw = codeEl.textContent.trim();
        if (/^[0-9]{2}(\.[0-9]{2}){0,3}$/.test(raw) || /^[0-9]{2}\.[0-9]{2}\.[0-9]{5}$/.test(raw)) {
            return raw;
        }
    }
    // Check visible cascading dropdowns from most specific to province
    var kelBox = document.getElementById('kel_box');
    var kel = document.getElementById('kel');
    if (kelBox && kelBox.style.display !== 'none' && kel && kel.value) return kel.value;

    var kecBox = document.getElementById('kec_box');
    var kec = document.getElementById('kec');
    if (kecBox && kecBox.style.display !== 'none' && kec && kec.value) return kec.value;

    var kabBox = document.getElementById('kab_box');
    var kota = document.getElementById('kota');
    if (kabBox && kabBox.style.display !== 'none' && kota && kota.value) return kota.value;

    var prop = document.getElementById('prop');
    if (prop && prop.value) return prop.value;
    return 'all';
}

function getExportUrl(kode, format, target) {
    var validFormats = ['geojson', 'kml', 'csv'];
    var validTargets = ['boundary', 'islands', 'stats'];

    var f = validFormats.indexOf(format) !== -1 ? format : 'geojson';
    var t = validTargets.indexOf(target) !== -1 ? target : 'boundary';
    var k = (kode && kode !== '—') ? kode : 'all';

    return 'inc/geo_export.php?kode=' + encodeURIComponent(k) + '&format=' + encodeURIComponent(f) + '&target=' + encodeURIComponent(t);
}

function triggerExportDownload(format, target, optionalKode) {
    var kode = optionalKode || getActiveRegionCode();
    var url = getExportUrl(kode, format, target);

    var targetName = (target === 'islands') ? 'data pulau' : ((target === 'stats') ? 'profil wilayah' : 'batas wilayah');
    showToast('Mengunduh ' + targetName + ' (' + format.toUpperCase() + ')...', 2500);

    var link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', '');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function initExportControls() {
    var menuBtn = document.getElementById('btnExportMenu');
    var menu = document.getElementById('exportDropdownMenu');
    var wrapper = document.getElementById('exportDropdownWrapper');

    if (menuBtn && menu) {
        if (menuBtn.getAttribute('data-export-bound')) return;
        menuBtn.setAttribute('data-export-bound', 'true');

        menuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = (menu.style.display === 'flex' || menu.style.display === 'block');
            menu.style.display = isOpen ? 'none' : 'flex';
            menuBtn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        });

        document.addEventListener('click', function(e) {
            if (wrapper && !wrapper.contains(e.target)) {
                menu.style.display = 'none';
                menuBtn.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && menu.style.display !== 'none') {
                menu.style.display = 'none';
                menuBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    var btnGeoJson = document.getElementById('exportGeoJsonBtn');
    if (btnGeoJson) {
        btnGeoJson.addEventListener('click', function(e) {
            e.preventDefault();
            if (menu) menu.style.display = 'none';
            triggerExportDownload('geojson', 'boundary');
        });
    }

    var btnKml = document.getElementById('exportKmlBtn');
    if (btnKml) {
        btnKml.addEventListener('click', function(e) {
            e.preventDefault();
            if (menu) menu.style.display = 'none';
            triggerExportDownload('kml', 'boundary');
        });
    }

    var btnCsv = document.getElementById('exportCsvStatsBtn');
    if (btnCsv) {
        btnCsv.addEventListener('click', function(e) {
            e.preventDefault();
            if (menu) menu.style.display = 'none';
            triggerExportDownload('csv', 'stats');
        });
    }

    var btnIslands = document.getElementById('btnExportIslands');
    if (btnIslands) {
        btnIslands.addEventListener('click', function(e) {
            e.preventDefault();
            triggerExportDownload('csv', 'islands');
        });
    }
}

// ============================================================
// SIDE-BY-SIDE REGION COMPARISON TOOL (P6)
// ============================================================
var compareCurrentLevel = 'provinsi';
var compareData = null;
var compareDualLayerGroup = null;
var compareCachedLists = { provinsi: null, kabkota: null };

function formatNumberId(num) {
    if (num === null || num === undefined) return '—';
    var n = Number(num);
    if (isNaN(n)) return '—';
    return n.toLocaleString('id-ID');
}

function calcRatioPairClient(val1, val2) {
    var v1 = Math.max(0, parseFloat(val1) || 0);
    var v2 = Math.max(0, parseFloat(val2) || 0);
    var sum = v1 + v2;
    if (sum <= 0) return [50.0, 50.0];
    var r1 = Math.round((v1 / sum) * 1000) / 10;
    var r2 = Math.round((100.0 - r1) * 10) / 10;
    return [r1, r2];
}

function openCompareModal(initialKode) {
    var modal = document.getElementById('compareModal');
    if (!modal) return;
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    var kode = initialKode || getActiveRegionCode();
    if (kode && (kode.length === 2 || kode.length === 5)) {
        var targetLevel = (kode.length === 2) ? 'provinsi' : 'kabkota';
        switchCompareLevel(targetLevel, kode);
    } else {
        loadCompareOptions(compareCurrentLevel);
    }

    var closeBtn = document.getElementById('btnCompareClose');
    if (closeBtn && typeof closeBtn.focus === 'function') {
        closeBtn.focus();
    }
}

function closeCompareModal() {
    var modal = document.getElementById('compareModal');
    if (!modal) return;
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

function switchCompareLevel(level, optionalPreselect1) {
    compareCurrentLevel = (level === 'kabkota') ? 'kabkota' : 'provinsi';

    var tabProv = document.getElementById('tabCompareProv');
    var tabKab = document.getElementById('tabCompareKab');
    if (tabProv && tabKab) {
        if (compareCurrentLevel === 'provinsi') {
            tabProv.classList.add('is-active', 'active');
            tabProv.setAttribute('aria-selected', 'true');
            tabKab.classList.remove('is-active', 'active');
            tabKab.setAttribute('aria-selected', 'false');
        } else {
            tabKab.classList.add('is-active', 'active');
            tabKab.setAttribute('aria-selected', 'true');
            tabProv.classList.remove('is-active', 'active');
            tabProv.setAttribute('aria-selected', 'false');
        }
    }

    resetCompareUI();
    loadCompareOptions(compareCurrentLevel, optionalPreselect1);
}

function resetCompareUI() {
    var sel1 = document.getElementById('compareSelect1');
    var sel2 = document.getElementById('compareSelect2');
    var btn = document.getElementById('btnRunCompare');
    var empty = document.getElementById('compareEmptyState');
    var results = document.getElementById('compareResults');
    var err = document.getElementById('compareError');
    var spinner = document.getElementById('compareSpinner');

    if (sel1) sel1.value = '';
    if (sel2) sel2.value = '';
    if (btn) btn.disabled = true;
    if (empty) empty.style.display = 'block';
    if (results) {
        results.style.display = 'none';
        results.innerHTML = '';
    }
    if (err) {
        err.style.display = 'none';
        err.textContent = '';
    }
    if (spinner) spinner.style.display = 'none';
}

function loadCompareOptions(level, optionalPreselect1) {
    var sel1 = document.getElementById('compareSelect1');
    var sel2 = document.getElementById('compareSelect2');
    if (!sel1 || !sel2) return;

    var placeholder = (level === 'provinsi') ? 'Pilih Provinsi...' : 'Pilih Kab / Kota...';
    sel1.innerHTML = '<option value="">' + placeholder + '</option>';
    sel2.innerHTML = '<option value="">' + placeholder + '</option>';

    if (level === 'provinsi') {
        var propSelect = document.getElementById('prop');
        var options = [];
        if (propSelect && propSelect.options && propSelect.options.length > 1) {
            for (var i = 1; i < propSelect.options.length; i++) {
                var opt = propSelect.options[i];
                if (opt.value) {
                    options.push({ kode: opt.value, nama: opt.textContent });
                }
            }
        }

        if (options.length > 0) {
            compareCachedLists.provinsi = options;
            populateCompareSelects(options, optionalPreselect1);
        } else {
            fetchWilayah('inc/geo_compare.php?list=provinsi')
                .then(function(res) {
                    if (res && res.status === 'success' && Array.isArray(res.data)) {
                        compareCachedLists.provinsi = res.data;
                        populateCompareSelects(res.data, optionalPreselect1);
                    }
                })
                .catch(function(e) {
                    console.error('Failed to load compare provinsi options:', e);
                });
        }
    } else {
        if (compareCachedLists.kabkota && compareCachedLists.kabkota.length > 0) {
            populateCompareSelects(compareCachedLists.kabkota, optionalPreselect1);
        } else {
            fetchWilayah('inc/geo_compare.php?list=kabkota')
                .then(function(res) {
                    if (res && res.status === 'success' && Array.isArray(res.data)) {
                        compareCachedLists.kabkota = res.data;
                        populateCompareSelects(res.data, optionalPreselect1);
                    }
                })
                .catch(function(e) {
                    console.error('Failed to load compare kabkota options:', e);
                });
        }
    }
}

function populateCompareSelects(items, optionalPreselect1) {
    var sel1 = document.getElementById('compareSelect1');
    var sel2 = document.getElementById('compareSelect2');
    if (!sel1 || !sel2 || !Array.isArray(items)) return;

    var level = compareCurrentLevel;
    var placeholder = (level === 'provinsi') ? 'Pilih Provinsi...' : 'Pilih Kab / Kota...';
    var html = '<option value="">' + placeholder + '</option>';

    items.forEach(function(item) {
        var k = escapeHtml(item.kode || '');
        var n = escapeHtml(item.nama || '');
        html += '<option value="' + k + '">' + n + ' (' + k + ')</option>';
    });

    sel1.innerHTML = html;
    sel2.innerHTML = html;

    if (optionalPreselect1) {
        sel1.value = optionalPreselect1;
    }
    updateCompareRunButtonState();
}

function updateCompareRunButtonState() {
    var sel1 = document.getElementById('compareSelect1');
    var sel2 = document.getElementById('compareSelect2');
    var btn = document.getElementById('btnRunCompare');
    if (!btn || !sel1 || !sel2) return;

    var k1 = sel1.value;
    var k2 = sel2.value;
    var canRun = Boolean(k1 && k2 && k1.length === k2.length);
    btn.disabled = !canRun;
}

function setCompareSelectValues(kode1, kode2) {
    var sel1 = document.getElementById('compareSelect1');
    var sel2 = document.getElementById('compareSelect2');
    if (sel1 && kode1) sel1.value = kode1;
    if (sel2 && kode2) sel2.value = kode2;
    updateCompareRunButtonState();
}

function fetchComparisonData(kode1, kode2) {
    if (!kode1 || !kode2 || kode1.length !== kode2.length) {
        showCompareError('Pilih dua wilayah yang setara (keduanya provinsi atau keduanya kabupaten/kota).');
        return Promise.reject(new Error('Invalid comparison codes'));
    }

    var spinner = document.getElementById('compareSpinner');
    var empty = document.getElementById('compareEmptyState');
    var results = document.getElementById('compareResults');
    var err = document.getElementById('compareError');

    if (spinner) spinner.style.display = 'flex';
    if (empty) empty.style.display = 'none';
    if (results) results.style.display = 'none';
    if (err) err.style.display = 'none';

    var url = 'inc/geo_compare.php?kode1=' + encodeURIComponent(kode1) + '&kode2=' + encodeURIComponent(kode2);

    return fetchWilayah(url)
        .then(function(json) {
            if (spinner) spinner.style.display = 'none';
            if (json && json.status === 'success' && json.entities) {
                compareData = json;
                renderComparisonView(json);
                updateUrlState({ compare: kode1 + ',' + kode2 });
                return json;
            } else {
                var msg = (json && json.message) ? json.message : 'Gagal memuat data perbandingan wilayah.';
                showCompareError(msg);
                throw new Error(msg);
            }
        })
        .catch(function(e) {
            if (spinner) spinner.style.display = 'none';
            showCompareError(e.message || 'Terjadi kesalahan saat memuat data komparasi.');
            throw e;
        });
}

function showCompareError(msg) {
    var err = document.getElementById('compareError');
    var results = document.getElementById('compareResults');
    var empty = document.getElementById('compareEmptyState');
    if (err) {
        err.textContent = msg;
        err.style.display = 'block';
    }
    if (results) results.style.display = 'none';
    if (empty) empty.style.display = 'none';
}

function renderDeltaBadgeHtml(delta) {
    if (delta === null || delta === undefined || isNaN(delta)) return '';
    var d = parseFloat(delta);
    if (d > 0) {
        return '<span class="metric-delta-badge is-positive">+' + d.toFixed(2) + '%</span>';
    } else if (d < 0) {
        return '<span class="metric-delta-badge is-negative">' + d.toFixed(2) + '%</span>';
    } else {
        return '<span class="metric-delta-badge is-neutral">0%</span>';
    }
}

function renderComparisonView(data) {
    var container = document.getElementById('compareResults');
    var empty = document.getElementById('compareEmptyState');
    if (!container || !data || !data.entities) return;

    var e1 = data.entities.entity1;
    var e2 = data.entities.entity2;
    var comp = data.comparison || {};
    var ratios = comp.ratios || {};

    var html = '';

    // Summary Profiles Grid
    html += '<div class="compare-entity-grid">' +
        '<div class="compare-entity-card entity-1">' +
            '<div class="compare-entity-title-row">' +
                '<h4 class="compare-entity-name">' + escapeHtml(e1.nama) + '</h4>' +
                '<span class="compare-entity-code">' + escapeHtml(e1.kode) + '</span>' +
            '</div>' +
            '<div class="compare-entity-meta">' +
                (e1.ibukota ? '<span class="compare-meta-item">Ibukota: <strong>' + escapeHtml(e1.ibukota) + '</strong></span>' : '') +
                '<span class="compare-meta-item">Zona: <strong>' + escapeHtml(e1.timezone || 'WIB') + '</strong></span>' +
                '<span class="compare-meta-item">Elevasi: <strong>' + formatNumberId(e1.elv) + ' m</strong></span>' +
                '<span class="compare-meta-item">Koordinat: <strong>' + e1.lat.toFixed(4) + '°, ' + e1.lng.toFixed(4) + '°</strong></span>' +
            '</div>' +
        '</div>' +
        '<div class="compare-entity-card entity-2">' +
            '<div class="compare-entity-title-row">' +
                '<h4 class="compare-entity-name">' + escapeHtml(e2.nama) + '</h4>' +
                '<span class="compare-entity-code">' + escapeHtml(e2.kode) + '</span>' +
            '</div>' +
            '<div class="compare-entity-meta">' +
                (e2.ibukota ? '<span class="compare-meta-item">Ibukota: <strong>' + escapeHtml(e2.ibukota) + '</strong></span>' : '') +
                '<span class="compare-meta-item">Zona: <strong>' + escapeHtml(e2.timezone || 'WIB') + '</strong></span>' +
                '<span class="compare-meta-item">Elevasi: <strong>' + formatNumberId(e2.elv) + ' m</strong></span>' +
                '<span class="compare-meta-item">Koordinat: <strong>' + e2.lat.toFixed(4) + '°, ' + e2.lng.toFixed(4) + '°</strong></span>' +
            '</div>' +
        '</div>' +
    '</div>';

    // Actions Toolbar
    html += '<div class="compare-toolbar">' +
        '<button type="button" class="compare-tool-btn" id="btnCompareShowMap">' +
            '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>' +
            '<span>Tampilkan di Peta</span>' +
        '</button>' +
        '<button type="button" class="compare-tool-btn" id="btnCompareCopyLink">' +
            '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>' +
            '<span>Salin Tautan</span>' +
        '</button>' +
        '<button type="button" class="compare-tool-btn" id="btnCompareExportCsv">' +
            '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>' +
            '<span>Unduh CSV</span>' +
        '</button>' +
        '<button type="button" class="compare-tool-btn" id="btnComparePrint">' +
            '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>' +
            '<span>Cetak</span>' +
        '</button>' +
    '</div>';

    // Metrics List Helper
    function renderMetricCard(title, val1, val2, formatted1, formatted2, delta, ratioPair, unit) {
        var rPair = ratioPair || calcRatioPairClient(val1, val2);
        return '<div class="compare-metric-card">' +
            '<div class="metric-header-row">' +
                '<span class="metric-title">' + escapeHtml(title) + '</span>' +
                renderDeltaBadgeHtml(delta) +
            '</div>' +
            '<div class="metric-values-row">' +
                '<span class="val-entity1">' + formatted1 + (unit ? ' <small>' + unit + '</small>' : '') + '</span>' +
                '<span class="val-entity2">' + formatted2 + (unit ? ' <small>' + unit + '</small>' : '') + '</span>' +
            '</div>' +
            '<div class="compare-bar-container">' +
                '<div class="compare-bar-fill fill-entity1" style="width:' + rPair[0] + '%;"></div>' +
                '<div class="compare-bar-fill fill-entity2" style="width:' + rPair[1] + '%;"></div>' +
            '</div>' +
            '<div class="compare-bar-labels">' +
                '<span>' + rPair[0] + '%</span>' +
                '<span>' + rPair[1] + '%</span>' +
            '</div>' +
        '</div>';
    }

    html += '<div class="compare-metrics-section">';
    html += '<h4 class="compare-section-heading">Indikator Geografis & Demografis</h4>';

    // Luas Wilayah
    html += renderMetricCard('Luas Wilayah', e1.luas_km2, e2.luas_km2, formatNumberId(e1.luas_km2), formatNumberId(e2.luas_km2), comp.luas_delta_percent, ratios.luas, 'km²');

    // Penduduk
    html += renderMetricCard('Jumlah Penduduk', e1.penduduk, e2.penduduk, formatNumberId(e1.penduduk), formatNumberId(e2.penduduk), comp.penduduk_delta_percent, ratios.penduduk, 'jiwa');

    // Kepadatan
    html += renderMetricCard('Kepadatan Penduduk', e1.kepadatan, e2.kepadatan, formatNumberId(e1.kepadatan), formatNumberId(e2.kepadatan), comp.kepadatan_delta_percent, ratios.kepadatan, 'jiwa/km²');

    // Sex Ratio
    var sexStr1 = (e1.sex_ratio !== null) ? e1.sex_ratio : '—';
    var sexStr2 = (e2.sex_ratio !== null) ? e2.sex_ratio : '—';
    var sexDelta = (e1.sex_ratio !== null && e2.sex_ratio !== null && e2.sex_ratio > 0) ? (Math.round(((e1.sex_ratio - e2.sex_ratio) / e2.sex_ratio) * 10000) / 100) : null;
    html += renderMetricCard('Rasio Jenis Kelamin (Sex Ratio)', e1.sex_ratio || 0, e2.sex_ratio || 0, sexStr1, sexStr2, sexDelta, null, 'L / 100 P');

    // Pulau
    html += renderMetricCard('Jumlah Pulau', e1.total_pulau, e2.total_pulau, formatNumberId(e1.total_pulau), formatNumberId(e2.total_pulau), comp.pulau_delta_percent, ratios.pulau, 'pulau');

    html += '<h4 class="compare-section-heading" style="margin-top:12px;">Hierarki Administrasi</h4>';

    if (data.level === 'provinsi') {
        html += renderMetricCard('Jumlah Kabupaten', e1.total_kab, e2.total_kab, formatNumberId(e1.total_kab), formatNumberId(e2.total_kab), comp.kab_delta_percent, ratios.kab, 'kab');
        html += renderMetricCard('Jumlah Kota', e1.total_kota, e2.total_kota, formatNumberId(e1.total_kota), formatNumberId(e2.total_kota), comp.kota_delta_percent, ratios.kota, 'kota');
    }

    html += renderMetricCard('Jumlah Kecamatan', e1.total_kec, e2.total_kec, formatNumberId(e1.total_kec), formatNumberId(e2.total_kec), comp.kec_delta_percent, ratios.kec, 'kec');
    html += renderMetricCard('Jumlah Desa', e1.total_desa, e2.total_desa, formatNumberId(e1.total_desa), formatNumberId(e2.total_desa), comp.desa_delta_percent, ratios.desa, 'desa');
    html += renderMetricCard('Jumlah Kelurahan', e1.total_kel, e2.total_kel, formatNumberId(e1.total_kel), formatNumberId(e2.total_kel), comp.kel_delta_percent, ratios.kel, 'kel');

    html += '</div>';

    container.innerHTML = html;
    container.style.display = 'flex';
    if (empty) empty.style.display = 'none';

    // Bind action toolbar buttons
    var btnMap = document.getElementById('btnCompareShowMap');
    if (btnMap) {
        btnMap.addEventListener('click', function(e) {
            e.preventDefault();
            renderDualPolygons(e1, e2);
        });
    }

    var btnLink = document.getElementById('btnCompareCopyLink');
    if (btnLink) {
        btnLink.addEventListener('click', function(e) {
            e.preventDefault();
            copyCompareShareUrl(e1.kode, e2.kode);
        });
    }

    var btnCsv = document.getElementById('btnCompareExportCsv');
    if (btnCsv) {
        btnCsv.addEventListener('click', function(e) {
            e.preventDefault();
            downloadCompareCsv(data);
        });
    }

    var btnPrint = document.getElementById('btnComparePrint');
    if (btnPrint) {
        btnPrint.addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
    }
}

function renderDualPolygons(entity1, entity2) {
    if (!map || typeof L === 'undefined') return;

    if (compareDualLayerGroup) {
        if (typeof compareDualLayerGroup.clearLayers === 'function') {
            compareDualLayerGroup.clearLayers();
        }
        if (map.hasLayer(compareDualLayerGroup)) {
            map.removeLayer(compareDualLayerGroup);
        }
    }

    compareDualLayerGroup = L.featureGroup();

    function addRegionPolygon(entity, color, label) {
        if (!entity) return;
        if (entity.path) {
            try {
                var rawPath = entity.path;
                var coords = (typeof rawPath === 'string') ? JSON.parse(rawPath) : rawPath;
                if (Array.isArray(coords) && coords.length > 0) {
                    var poly = L.polygon(coords, {
                        color: color,
                        weight: 2.5,
                        fillColor: color,
                        fillOpacity: 0.35
                    });
                    var popupHtml = '<div class="compare-map-popup">' +
                        '<strong>' + escapeHtml(entity.nama) + '</strong> (' + escapeHtml(entity.kode) + ')<br>' +
                        '<span>Luas: ' + formatNumberId(entity.luas_km2) + ' km²</span><br>' +
                        '<span>Penduduk: ' + formatNumberId(entity.penduduk) + ' jiwa</span>' +
                        '</div>';
                    poly.bindPopup(popupHtml);
                    poly.bindTooltip(label + ': ' + escapeHtml(entity.nama), { permanent: false, direction: 'center' });
                    compareDualLayerGroup.addLayer(poly);
                }
            } catch (e) {
                console.warn('Failed to parse polygon path for', entity.kode, e);
            }
        }

        if (entity.lat && entity.lng) {
            var marker = L.circleMarker([entity.lat, entity.lng], {
                radius: 7,
                fillColor: color,
                color: '#ffffff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.9
            });
            marker.bindPopup('<strong>' + escapeHtml(entity.nama) + '</strong><br>Ibukota: ' + escapeHtml(entity.ibukota || '—'));
            compareDualLayerGroup.addLayer(marker);
        }
    }

    addRegionPolygon(entity1, '#3b82f6', 'Wilayah 1');
    addRegionPolygon(entity2, '#f59e0b', 'Wilayah 2');

    compareDualLayerGroup.addTo(map);

    try {
        var bounds = compareDualLayerGroup.getBounds();
        if (bounds && bounds.isValid && bounds.isValid()) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    } catch (e) {}

    closeCompareModal();
    showToast('Menampilkan perbandingan poligon ' + entity1.nama + ' & ' + entity2.nama + ' di peta', 3500);
}

function generateCompareCsv(data) {
    if (!data || !data.entities) return '';
    var e1 = data.entities.entity1;
    var e2 = data.entities.entity2;
    var comp = data.comparison || {};

    var rows = [
        ['Indikator / Metrik', e1.nama + ' (' + e1.kode + ')', e2.nama + ' (' + e2.kode + ')', 'Selisih (%)'],
        ['Luas Wilayah (km2)', e1.luas_km2, e2.luas_km2, (comp.luas_delta_percent !== undefined ? comp.luas_delta_percent + '%' : '—')],
        ['Jumlah Penduduk (jiwa)', e1.penduduk, e2.penduduk, (comp.penduduk_delta_percent !== undefined ? comp.penduduk_delta_percent + '%' : '—')],
        ['Kepadatan Penduduk (jiwa/km2)', e1.kepadatan, e2.kepadatan, (comp.kepadatan_delta_percent !== undefined ? comp.kepadatan_delta_percent + '%' : '—')],
        ['Penduduk Pria (jiwa)', e1.penduduk_pria !== null ? e1.penduduk_pria : 'N/A', e2.penduduk_pria !== null ? e2.penduduk_pria : 'N/A', '—'],
        ['Penduduk Wanita (jiwa)', e1.penduduk_wanita !== null ? e1.penduduk_wanita : 'N/A', e2.penduduk_wanita !== null ? e2.penduduk_wanita : 'N/A', '—'],
        ['Sex Ratio (L/100 P)', e1.sex_ratio !== null ? e1.sex_ratio : 'N/A', e2.sex_ratio !== null ? e2.sex_ratio : 'N/A', '—'],
        ['Jumlah Pulau', e1.total_pulau, e2.total_pulau, (comp.pulau_delta_percent !== undefined ? comp.pulau_delta_percent + '%' : '—')],
        ['Total Kecamatan', e1.total_kec, e2.total_kec, (comp.kec_delta_percent !== undefined ? comp.kec_delta_percent + '%' : '—')],
        ['Total Desa', e1.total_desa, e2.total_desa, (comp.desa_delta_percent !== undefined ? comp.desa_delta_percent + '%' : '—')],
        ['Total Kelurahan', e1.total_kel, e2.total_kel, (comp.kel_delta_percent !== undefined ? comp.kel_delta_percent + '%' : '—')]
    ];

    if (data.level === 'provinsi') {
        rows.push(['Total Kabupaten', e1.total_kab, e2.total_kab, (comp.kab_delta_percent !== undefined ? comp.kab_delta_percent + '%' : '—')]);
        rows.push(['Total Kota', e1.total_kota, e2.total_kota, (comp.kota_delta_percent !== undefined ? comp.kota_delta_percent + '%' : '—')]);
    }

    return '\uFEFF' + rows.map(function(r) {
        return r.map(function(field) {
            var val = (field === null || field === undefined) ? '' : String(field);
            if (val.search(/([",\n])/g) >= 0) {
                val = '"' + val.replace(/"/g, '""') + '"';
            }
            return val;
        }).join(',');
    }).join('\r\n');
}

function downloadCompareCsv(data) {
    var d = data || compareData;
    if (!d || !d.entities) {
        showToast('Tidak ada data komparasi untuk diunduh');
        return;
    }
    var csv = generateCompareCsv(d);
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'komparasi_' + d.entities.entity1.kode + '_' + d.entities.entity2.kode + '.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    showToast('Ringkasan komparasi berhasil diunduh (CSV)');
}

function copyCompareShareUrl(kode1, kode2) {
    var k1 = kode1 || (compareData && compareData.entities && compareData.entities.entity1.kode);
    var k2 = kode2 || (compareData && compareData.entities && compareData.entities.entity2.kode);
    if (!k1 || !k2) {
        showToast('Pilih dua wilayah untuk disalin');
        return;
    }
    var loc = window.location || {};
    var url = (loc.origin || '') + (loc.pathname || '') + '?compare=' + encodeURIComponent(k1 + ',' + k2);
    copyUrlToClipboard(url);
}

function initCompareControls() {
    var btnOpen = document.getElementById('btnOpenCompare');
    if (btnOpen) {
        if (btnOpen.getAttribute('data-compare-bound')) return;
        btnOpen.setAttribute('data-compare-bound', 'true');
        btnOpen.addEventListener('click', function(e) {
            e.preventDefault();
            openCompareModal();
        });
    }

    var btnClose = document.getElementById('btnCompareClose');
    if (btnClose) {
        btnClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeCompareModal();
        });
    }

    var modal = document.getElementById('compareModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeCompareModal();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var m = document.getElementById('compareModal');
            if (m && m.style.display !== 'none') {
                closeCompareModal();
            }
        }
    });

    var tabProv = document.getElementById('tabCompareProv');
    if (tabProv) {
        tabProv.addEventListener('click', function(e) {
            e.preventDefault();
            switchCompareLevel('provinsi');
        });
    }

    var tabKab = document.getElementById('tabCompareKab');
    if (tabKab) {
        tabKab.addEventListener('click', function(e) {
            e.preventDefault();
            switchCompareLevel('kabkota');
        });
    }

    var sel1 = document.getElementById('compareSelect1');
    if (sel1) {
        sel1.addEventListener('change', function() {
            updateCompareRunButtonState();
        });
    }

    var sel2 = document.getElementById('compareSelect2');
    if (sel2) {
        sel2.addEventListener('change', function() {
            updateCompareRunButtonState();
        });
    }

    var btnRun = document.getElementById('btnRunCompare');
    if (btnRun) {
        btnRun.addEventListener('click', function(e) {
            e.preventDefault();
            if (sel1 && sel2 && sel1.value && sel2.value) {
                fetchComparisonData(sel1.value, sel2.value);
            }
        });
    }
}

/* ============================================================
   HISTORICAL REGIONAL EVOLUTION TIMELINE (P7)
   ============================================================ */

var timelineSnapshots = [];
var timelineYears = [2016, 2018, 2020, 2022, 2023, 2025];
var timelineCurrentIndex = 5;
var timelineCurrentYear = 2025;
var timelineIsPlaying = false;
var timelinePlayTimer = null;
var timelinePreDobLayers = null;
var timelineDobHighlightLayers = null;
var timelinePathsCache = {};

function fetchTimelineOverview() {
    return fetch('inc/geo_timeline.php?action=overview')
        .then(function(res) {
            if (!res.ok) throw new Error('Failed to load timeline overview');
            return res.json();
        })
        .then(function(res) {
            if (res && res.status === 'success') {
                if (Array.isArray(res.snapshots)) {
                    timelineSnapshots = res.snapshots;
                }
                if (Array.isArray(res.years)) {
                    timelineYears = res.years;
                }
            }
            return res;
        })
        .catch(function(err) {
            console.warn('Timeline overview fetch error:', err);
            return null;
        });
}

function openTimeline() {
    var bar = document.getElementById('timelineFloatingBar');
    var btn = document.getElementById('btnToggleTimeline');
    if (bar) {
        bar.style.display = 'block';
    }
    if (btn) {
        btn.classList.add('is-active');
        btn.setAttribute('aria-expanded', 'true');
    }
    if (!timelineSnapshots || timelineSnapshots.length === 0) {
        fetchTimelineOverview().then(function() {
            changeTimelineYear(timelineCurrentYear);
        });
    } else {
        changeTimelineYear(timelineCurrentYear);
    }
}

function closeTimeline() {
    if (timelineIsPlaying) {
        toggleTimelinePlay();
    }
    var bar = document.getElementById('timelineFloatingBar');
    var btn = document.getElementById('btnToggleTimeline');
    if (bar) {
        bar.style.display = 'none';
    }
    if (btn) {
        btn.classList.remove('is-active');
        btn.setAttribute('aria-expanded', 'false');
    }
    clearTimelineLayers();
}

function toggleTimelineBar() {
    var bar = document.getElementById('timelineFloatingBar');
    if (bar && bar.style.display !== 'none') {
        closeTimeline();
    } else {
        openTimeline();
    }
}

function toggleTimelinePlay() {
    var btn = document.getElementById('btnTimelinePlay');
    var iconPlay = btn ? btn.querySelector('.icon-play') : null;
    var iconPause = btn ? btn.querySelector('.icon-pause') : null;

    if (timelineIsPlaying) {
        if (timelinePlayTimer) {
            clearInterval(timelinePlayTimer);
            timelinePlayTimer = null;
        }
        timelineIsPlaying = false;
        if (iconPlay) iconPlay.style.display = 'block';
        if (iconPause) iconPause.style.display = 'none';
        if (btn) btn.setAttribute('title', 'Mulai Animasi Linimasa');
    } else {
        timelineIsPlaying = true;
        if (iconPlay) iconPlay.style.display = 'none';
        if (iconPause) iconPause.style.display = 'block';
        if (btn) btn.setAttribute('title', 'Jeda Animasi Linimasa');

        timelinePlayTimer = setInterval(function() {
            timelineCurrentIndex = (timelineCurrentIndex + 1) % timelineYears.length;
            var nextYear = timelineYears[timelineCurrentIndex];
            changeTimelineYear(nextYear);
        }, 3000);
    }
}

function clearTimelineLayers() {
    if (timelinePreDobLayers && map && typeof map.removeLayer === 'function') {
        map.removeLayer(timelinePreDobLayers);
        timelinePreDobLayers = null;
    }
    if (timelineDobHighlightLayers && map && typeof map.removeLayer === 'function') {
        map.removeLayer(timelineDobHighlightLayers);
        timelineDobHighlightLayers = null;
    }
}

function renderHistoricalPaths(pathsData) {
    if (!map || typeof L === 'undefined' || !pathsData || !pathsData.is_pre_dob) return;

    if (timelinePreDobLayers && typeof map.removeLayer === 'function') {
        map.removeLayer(timelinePreDobLayers);
    }
    timelinePreDobLayers = L.featureGroup();

    if (pathsData.papua_2022 && typeof L.polygon === 'function') {
        var poly1 = L.polygon(pathsData.papua_2022, {
            color: '#38bdf8',
            weight: 2,
            opacity: 0.85,
            fillColor: '#38bdf8',
            fillOpacity: 0.18,
            dashArray: '4, 4'
        }).bindPopup('<b>91 - PAPUA (Batas Pra-Pemekaran 2022)</b><br><small>Sebelum pemekaran 4 provinsi baru di Tanah Papua</small>');
        timelinePreDobLayers.addLayer(poly1);
    }

    if (pathsData.papua_barat_2022 && typeof L.polygon === 'function') {
        var poly2 = L.polygon(pathsData.papua_barat_2022, {
            color: '#38bdf8',
            weight: 2,
            opacity: 0.85,
            fillColor: '#38bdf8',
            fillOpacity: 0.18,
            dashArray: '4, 4'
        }).bindPopup('<b>92 - PAPUA BARAT (Batas Pra-Pemekaran 2022)</b><br><small>Sebelum pemekaran Papua Barat Daya (UU 29/2022)</small>');
        timelinePreDobLayers.addLayer(poly2);
    }

    timelinePreDobLayers.addTo(map);
}

function loadHistoricalPreDobLayers(year) {
    if (timelinePathsCache[2022]) {
        renderHistoricalPaths(timelinePathsCache[2022]);
        return Promise.resolve(timelinePathsCache[2022]);
    }
    return fetch('inc/geo_timeline.php?action=paths&year=2022')
        .then(function(res) {
            if (!res.ok) throw new Error('Failed to load historical paths');
            return res.json();
        })
        .then(function(json) {
            if (json && json.status === 'success' && json.data) {
                timelinePathsCache[2022] = json.data;
                renderHistoricalPaths(json.data);
            }
            return json;
        })
        .catch(function(err) {
            console.warn('Historical paths fetch error:', err);
            return null;
        });
}

function renderDobHighlights() {
    if (!map || typeof L === 'undefined') return;

    if (timelineDobHighlightLayers && typeof map.removeLayer === 'function') {
        map.removeLayer(timelineDobHighlightLayers);
    }
    timelineDobHighlightLayers = L.featureGroup();

    var dobItems = [
        { kode: '93', nama: 'PAPUA SELATAN', ibukota: 'Merauke', lat: -7.498, lng: 139.400, undang: 'UU No. 14 Tahun 2022' },
        { kode: '94', nama: 'PAPUA TENGAH', ibukota: 'Nabire', lat: -3.548, lng: 136.230, undang: 'UU No. 15 Tahun 2022' },
        { kode: '95', nama: 'PAPUA PEGUNUNGAN', ibukota: 'Wamena', lat: -4.083, lng: 138.944, undang: 'UU No. 16 Tahun 2022' },
        { kode: '96', nama: 'PAPUA BARAT DAYA', ibukota: 'Sorong', lat: -0.876, lng: 131.256, undang: 'UU No. 29 Tahun 2022' }
    ];

    if (typeof L.circleMarker === 'function') {
        dobItems.forEach(function(d) {
            var marker = L.circleMarker([d.lat, d.lng], {
                radius: 12,
                color: '#fbbf24',
                weight: 3,
                fillColor: '#f59e0b',
                fillOpacity: 0.65,
                className: 'dob-polygon-pulse'
            }).bindPopup('<b>DOB ' + d.kode + ' - ' + d.nama + '</b><br>Ibukota: ' + d.ibukota + '<br><small>' + d.undang + '</small>');
            timelineDobHighlightLayers.addLayer(marker);
        });
        timelineDobHighlightLayers.addTo(map);
    }
}

function changeTimelineYear(year, flyToDob) {
    var y = parseInt(year, 10);
    if (isNaN(y)) return;
    timelineCurrentYear = y;

    var idx = timelineYears.indexOf(timelineCurrentYear);
    if (idx !== -1) {
        timelineCurrentIndex = idx;
    }

    var slider = document.getElementById('timelineSlider');
    if (slider) slider.value = timelineCurrentIndex;

    var yearTag = document.getElementById('timelineCurrentYearTag');
    if (yearTag) yearTag.textContent = String(timelineCurrentYear);

    // Update active ticks
    var ticks = document.querySelectorAll('.timeline-tick');
    ticks.forEach(function(tick) {
        var tYear = parseInt(tick.getAttribute('data-year'), 10);
        if (tYear === timelineCurrentYear) {
            tick.classList.add('is-active');
        } else {
            tick.classList.remove('is-active');
        }
    });

    // Update Nav buttons
    var btnPrev = document.getElementById('btnTimelinePrev');
    var btnNext = document.getElementById('btnTimelineNext');
    if (btnPrev) btnPrev.disabled = (timelineCurrentIndex === 0);
    if (btnNext) btnNext.disabled = (timelineCurrentIndex === timelineYears.length - 1);

    // Find snapshot
    var snap = null;
    for (var i = 0; i < timelineSnapshots.length; i++) {
        if (timelineSnapshots[i].year === timelineCurrentYear) {
            snap = timelineSnapshots[i];
            break;
        }
    }

    if (snap) {
        var regTitle = document.getElementById('timelineRegTitle');
        if (regTitle) regTitle.textContent = snap.regulasi || snap.short_title || ('Regulasi Wilayah Tahun ' + timelineCurrentYear);

        var statusPill = document.getElementById('timelineStatusPill');
        if (statusPill) {
            if (timelineCurrentYear === 2025) {
                statusPill.textContent = 'Terkini';
                statusPill.classList.remove('is-historical');
            } else {
                statusPill.textContent = 'Historis';
                statusPill.classList.add('is-historical');
            }
        }

        var descText = document.getElementById('timelineDescText');
        if (descText) descText.textContent = snap.highlights || '';

        var provChip = document.getElementById('chipTimelineProv');
        if (provChip) provChip.innerHTML = '<strong>' + (snap.total_provinsi || 34) + '</strong> Prov';

        var kabChip = document.getElementById('chipTimelineKab');
        if (kabChip) kabChip.innerHTML = '<strong>' + (snap.total_kab_kota || 514) + '</strong> Kab/Kota';

        var kecChip = document.getElementById('chipTimelineKec');
        if (kecChip) kecChip.innerHTML = '<strong>' + (snap.total_kecamatan ? formatNumberId(snap.total_kecamatan) : '-') + '</strong> Kec';

        var desaChip = document.getElementById('chipTimelineDesa');
        if (desaChip) desaChip.innerHTML = '<strong>' + (snap.total_desa_kel ? formatNumberId(snap.total_desa_kel) : '-') + '</strong> Desa';

        var pulauChip = document.getElementById('chipTimelinePulau');
        if (pulauChip) pulauChip.innerHTML = '<strong>' + (snap.total_pulau ? formatNumberId(snap.total_pulau) : '-') + '</strong> Pulau';

        var refLink = document.getElementById('timelineRefLink');
        if (refLink && snap.doc_url) {
            refLink.setAttribute('href', snap.doc_url);
        }
    }

    // Handle Papua DOB display & Map layers
    var dobCallout = document.getElementById('timelineDobCallout');
    if (timelineCurrentYear >= 2023) {
        if (dobCallout) dobCallout.style.display = 'flex';
        // Remove pre-DOB layers
        if (timelinePreDobLayers && map && typeof map.removeLayer === 'function') {
            map.removeLayer(timelinePreDobLayers);
            timelinePreDobLayers = null;
        }
        renderDobHighlights();

        if (flyToDob && map && typeof map.flyTo === 'function') {
            map.flyTo([-4.0, 138.0], 6);
        }
    } else {
        if (dobCallout) dobCallout.style.display = 'none';
        // Remove DOB highlight markers
        if (timelineDobHighlightLayers && map && typeof map.removeLayer === 'function') {
            map.removeLayer(timelineDobHighlightLayers);
            timelineDobHighlightLayers = null;
        }
        loadHistoricalPreDobLayers(timelineCurrentYear);
    }
}

function initTimelineControls() {
    var btnToggle = document.getElementById('btnToggleTimeline');
    if (btnToggle) {
        if (btnToggle.getAttribute('data-timeline-bound')) return;
        btnToggle.setAttribute('data-timeline-bound', 'true');
        btnToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleTimelineBar();
        });
    }

    var btnClose = document.getElementById('btnCloseTimeline');
    if (btnClose) {
        btnClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeTimeline();
        });
    }

    var btnPlay = document.getElementById('btnTimelinePlay');
    if (btnPlay) {
        btnPlay.addEventListener('click', function(e) {
            e.preventDefault();
            toggleTimelinePlay();
        });
    }

    var slider = document.getElementById('timelineSlider');
    if (slider) {
        slider.addEventListener('input', function() {
            var val = parseInt(this.value, 10);
            if (!isNaN(val) && val >= 0 && val < timelineYears.length) {
                changeTimelineYear(timelineYears[val]);
            }
        });
    }

    var ticks = document.querySelectorAll('.timeline-tick');
    ticks.forEach(function(tick) {
        tick.addEventListener('click', function() {
            var y = parseInt(tick.getAttribute('data-year'), 10);
            if (!isNaN(y)) {
                changeTimelineYear(y);
            }
        });
    });

    var btnPrev = document.getElementById('btnTimelinePrev');
    if (btnPrev) {
        btnPrev.addEventListener('click', function(e) {
            e.preventDefault();
            if (timelineCurrentIndex > 0) {
                timelineCurrentIndex--;
                changeTimelineYear(timelineYears[timelineCurrentIndex]);
            }
        });
    }

    var btnNext = document.getElementById('btnTimelineNext');
    if (btnNext) {
        btnNext.addEventListener('click', function(e) {
            e.preventDefault();
            if (timelineCurrentIndex < timelineYears.length - 1) {
                timelineCurrentIndex++;
                changeTimelineYear(timelineYears[timelineCurrentIndex]);
            }
        });
    }

    var btnFocusPapua = document.getElementById('btnFocusDobPapua');
    if (btnFocusPapua) {
        btnFocusPapua.addEventListener('click', function(e) {
            e.preventDefault();
            if (map && typeof map.flyTo === 'function') {
                map.flyTo([-4.0, 138.0], 6);
            }
        });
    }
}

// ============================================================
// P8: SPATIAL BUFFER & DISTANCE MEASUREMENT TOOL
// ============================================================
var measureActiveMode = 'none'; // 'none' | 'ruler' | 'buffer'
var measureUnit = 'km'; // 'km' | 'nm'
var measurePoints = []; // [{ lat: number, lng: number, marker: L.CircleMarker }]
var measureRulerPolyline = null;
var measureRulerTooltips = []; // [L.CircleMarker / L.Tooltip]
var measureBufferCenter = null; // { lat: number, lng: number, marker: L.CircleMarker }
var measureBufferCircle = null; // L.Circle
var measureBufferRadius = 25.0; // km
var measureBufferResults = []; // array of entities
var measureProximityLayerGroup = null; // L.layerGroup for proximity island pins

function kmToNm(km) {
    return km / 1.852;
}

function nmToKm(nm) {
    return nm * 1.852;
}

function calculateDistanceKm(lat1, lon1, lat2, lon2) {
    var R = 6371.0;
    var dLat = (lat2 - lat1) * Math.PI / 180.0;
    var dLon = (lon2 - lon1) * Math.PI / 180.0;
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180.0) * Math.cos(lat2 * Math.PI / 180.0) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function calculateBearingDeg(lat1, lon1, lat2, lon2) {
    var φ1 = lat1 * Math.PI / 180.0;
    var φ2 = lat2 * Math.PI / 180.0;
    var Δλ = (lon2 - lon1) * Math.PI / 180.0;
    var y = Math.sin(Δλ) * Math.cos(φ2);
    var x = Math.cos(φ1) * Math.sin(φ2) - Math.sin(φ1) * Math.cos(φ2) * Math.cos(Δλ);
    var θ = Math.atan2(y, x);
    return (θ * 180.0 / Math.PI + 360.0) % 360.0;
}

function bearingToDirection(deg) {
    var directions = ['U', 'UTL', 'TL', 'TTL', 'T', 'TTG', 'TG', 'STG', 'S', 'SBD', 'BD', 'BBD', 'B', 'BBL', 'BL', 'UBL'];
    var index = Math.round(deg / 22.5) % 16;
    return directions[index];
}

function formatMeasureDistance(distKm, unit) {
    if (unit === 'nm') {
        var nm = kmToNm(distKm);
        return (nm < 10 ? nm.toFixed(2) : nm.toFixed(1)) + ' NM';
    }
    return (distKm < 10 ? distKm.toFixed(2) : distKm.toFixed(1)) + ' km';
}

function setMeasureMode(mode) {
    measureActiveMode = mode;
    var btnRuler = document.getElementById('btnMeasureModeRuler');
    var btnBuffer = document.getElementById('btnMeasureModeBuffer');
    var ctrlRuler = document.getElementById('measureControlsRuler');
    var ctrlBuffer = document.getElementById('measureControlsBuffer');

    if (btnRuler) btnRuler.classList.toggle('is-active', mode === 'ruler');
    if (btnBuffer) btnBuffer.classList.toggle('is-active', mode === 'buffer');

    if (ctrlRuler) ctrlRuler.style.display = (mode === 'ruler') ? 'flex' : 'none';
    if (ctrlBuffer) ctrlBuffer.style.display = (mode === 'buffer') ? 'flex' : 'none';

    var mapCanvas = document.getElementById('map-canvas');
    if (mapCanvas) {
        if (mode === 'ruler' || mode === 'buffer') {
            mapCanvas.style.cursor = 'crosshair';
        } else {
            mapCanvas.style.cursor = '';
        }
    }
}

function openMeasureBar() {
    var bar = document.getElementById('measureFloatingBar');
    var btn = document.getElementById('btnToggleMeasure');
    if (bar) bar.style.display = 'block';
    if (btn) {
        btn.classList.add('is-active');
        btn.setAttribute('aria-expanded', 'true');
    }
    if (typeof closeTimeline === 'function') closeTimeline();
    setMeasureMode('ruler');
}

function closeMeasureBar() {
    var bar = document.getElementById('measureFloatingBar');
    var btn = document.getElementById('btnToggleMeasure');
    if (bar) bar.style.display = 'none';
    if (btn) {
        btn.classList.remove('is-active');
        btn.setAttribute('aria-expanded', 'false');
    }
    setMeasureMode('none');
}

function toggleMeasureBar() {
    var bar = document.getElementById('measureFloatingBar');
    if (bar && bar.style.display !== 'none') {
        closeMeasureBar();
    } else {
        openMeasureBar();
    }
}

function clearMeasurement() {
    if (map) {
        if (measureRulerPolyline) {
            map.removeLayer(measureRulerPolyline);
            measureRulerPolyline = null;
        }
        measurePoints.forEach(function(p) {
            if (p.marker) map.removeLayer(p.marker);
        });
        measureRulerTooltips.forEach(function(t) {
            if (t) map.removeLayer(t);
        });
        if (measureBufferCircle) {
            map.removeLayer(measureBufferCircle);
            measureBufferCircle = null;
        }
        if (measureBufferCenter && measureBufferCenter.marker) {
            map.removeLayer(measureBufferCenter.marker);
        }
        if (measureProximityLayerGroup) {
            measureProximityLayerGroup.clearLayers();
        }
    }
    measurePoints = [];
    measureRulerTooltips = [];
    measureBufferCenter = null;
    measureBufferResults = [];

    var statsRow = document.getElementById('measureRulerStats');
    if (statsRow) statsRow.style.display = 'none';

    var totalDistEl = document.getElementById('measureTotalDistance');
    if (totalDistEl) totalDistEl.textContent = '0.00 km';

    var pointsCountEl = document.getElementById('measurePointsCount');
    if (pointsCountEl) pointsCountEl.textContent = '0';

    var azimuthItem = document.getElementById('measureAzimuthItem');
    if (azimuthItem) azimuthItem.style.display = 'none';

    var resultsPanel = document.getElementById('measureResultsPanel');
    if (resultsPanel) resultsPanel.style.display = 'none';

    var resultsList = document.getElementById('measureResultsList');
    if (resultsList) resultsList.innerHTML = '';
}

function handleMeasurementMapClick(latlng) {
    if (measureActiveMode === 'ruler') {
        addRulerPoint(latlng.lat, latlng.lng);
    } else if (measureActiveMode === 'buffer') {
        setBufferCenter(latlng.lat, latlng.lng);
    }
}

function addRulerPoint(lat, lng) {
    if (!map) return;

    var marker = L.circleMarker([lat, lng], {
        radius: 6,
        color: '#10b981',
        weight: 2,
        fillColor: '#020617',
        fillOpacity: 0.9
    }).addTo(map);

    var pt = { lat: lat, lng: lng, marker: marker };
    measurePoints.push(pt);

    updateRulerVisuals();
}

function updateRulerVisuals() {
    if (!map || measurePoints.length === 0) return;

    var latlngs = measurePoints.map(function(p) { return [p.lat, p.lng]; });

    if (measureRulerPolyline) {
        measureRulerPolyline.setLatLngs(latlngs);
    } else {
        measureRulerPolyline = L.polyline(latlngs, {
            color: '#10b981',
            weight: 3,
            dashArray: '8, 8',
            className: 'measure-ruler-line'
        }).addTo(map);
    }

    // Clear old segment tooltips
    measureRulerTooltips.forEach(function(t) {
        if (map && t) map.removeLayer(t);
    });
    measureRulerTooltips = [];

    var totalKm = 0;
    var lastBearing = null;

    for (var i = 0; i < measurePoints.length - 1; i++) {
        var p1 = measurePoints[i];
        var p2 = measurePoints[i + 1];
        var segDist = calculateDistanceKm(p1.lat, p1.lng, p2.lat, p2.lng);
        totalKm += segDist;

        var segBearing = calculateBearingDeg(p1.lat, p1.lng, p2.lat, p2.lng);
        lastBearing = segBearing;

        var midLat = (p1.lat + p2.lat) / 2;
        var midLng = (p1.lng + p2.lng) / 2;

        var segLabel = formatMeasureDistance(segDist, measureUnit);
        var dirStr = bearingToDirection(segBearing);

        var segTooltipMarker = L.circleMarker([midLat, midLng], {
            radius: 0.1,
            opacity: 0,
            fillOpacity: 0
        }).addTo(map);

        segTooltipMarker.bindTooltip(segLabel + ' (' + Math.round(segBearing) + '° ' + dirStr + ')', {
            permanent: true,
            direction: 'center',
            className: 'measure-map-tooltip'
        }).openTooltip();

        measureRulerTooltips.push(segTooltipMarker);
    }

    var statsRow = document.getElementById('measureRulerStats');
    if (statsRow) statsRow.style.display = 'flex';

    var totalDistEl = document.getElementById('measureTotalDistance');
    if (totalDistEl) {
        var distStr = formatMeasureDistance(totalKm, measureUnit);
        var altStr = (measureUnit === 'km') ? '(' + formatMeasureDistance(totalKm, 'nm') + ')' : '(' + formatMeasureDistance(totalKm, 'km') + ')';
        totalDistEl.textContent = distStr + ' ' + altStr;
    }

    var pointsCountEl = document.getElementById('measurePointsCount');
    if (pointsCountEl) pointsCountEl.textContent = String(measurePoints.length);

    var azimuthItem = document.getElementById('measureAzimuthItem');
    var azimuthVal = document.getElementById('measureAzimuthVal');
    if (lastBearing !== null && azimuthItem && azimuthVal) {
        azimuthItem.style.display = 'inline-flex';
        azimuthVal.textContent = lastBearing.toFixed(1) + '° (' + bearingToDirection(lastBearing) + ')';
    } else if (azimuthItem) {
        azimuthItem.style.display = 'none';
    }
}

function setMeasureUnit(unit) {
    measureUnit = unit;
    var btnKm = document.getElementById('btnUnitKm');
    var btnNm = document.getElementById('btnUnitNm');
    if (btnKm) btnKm.classList.toggle('is-active', unit === 'km');
    if (btnNm) btnNm.classList.toggle('is-active', unit === 'nm');

    if (measureActiveMode === 'ruler') {
        updateRulerVisuals();
    } else if (measureBufferResults.length > 0) {
        renderBufferResults(measureBufferResults);
    }
}

function setBufferCenter(lat, lng) {
    if (!map) return;

    if (measureBufferCenter && measureBufferCenter.marker) {
        map.removeLayer(measureBufferCenter.marker);
    }

    var centerMarker = L.circleMarker([lat, lng], {
        radius: 7,
        color: '#10b981',
        weight: 3,
        fillColor: '#020617',
        fillOpacity: 0.95
    }).addTo(map);

    measureBufferCenter = { lat: lat, lng: lng, marker: centerMarker };

    drawBufferCircle();
    fetchProximityData(lat, lng, measureBufferRadius);
}

function drawBufferCircle() {
    if (!map || !measureBufferCenter) return;

    var radiusMeters = measureBufferRadius * 1000.0;

    if (measureBufferCircle) {
        measureBufferCircle.setLatLng([measureBufferCenter.lat, measureBufferCenter.lng]);
        measureBufferCircle.setRadius(radiusMeters);
    } else {
        measureBufferCircle = L.circle([measureBufferCenter.lat, measureBufferCenter.lng], {
            radius: radiusMeters,
            color: '#10b981',
            weight: 2,
            fillColor: '#10b981',
            fillOpacity: 0.15
        }).addTo(map);
    }
}

function updateBufferRadius(radiusKm) {
    var val = parseFloat(radiusKm);
    if (isNaN(val) || val <= 0) return;
    if (val > 500) val = 500;
    measureBufferRadius = val;

    var slider = document.getElementById('measureRadiusSlider');
    var input = document.getElementById('measureRadiusInput');
    if (slider && parseFloat(slider.value) !== val && val <= 200) slider.value = val;
    if (input && parseFloat(input.value) !== val) input.value = val;

    if (measureBufferCenter) {
        drawBufferCircle();
        fetchProximityData(measureBufferCenter.lat, measureBufferCenter.lng, measureBufferRadius);
    }
}

function fetchProximityData(lat, lng, radiusKm) {
    if (!measureProximityLayerGroup && map) {
        measureProximityLayerGroup = L.layerGroup().addTo(map);
    }

    var resultsPanel = document.getElementById('measureResultsPanel');
    var resultsList = document.getElementById('measureResultsList');
    var radiusTag = document.getElementById('measureResultsRadiusTag');

    if (resultsPanel) resultsPanel.style.display = 'block';
    if (radiusTag) radiusTag.textContent = radiusKm + ' km';
    if (resultsList) resultsList.innerHTML = '<div style="font-size:11.5px;color:var(--text-secondary);padding:8px 0;">Memindai entitas terdekat...</div>';

    var url = 'inc/geo_proximity.php?lat=' + encodeURIComponent(lat) +
              '&lng=' + encodeURIComponent(lng) +
              '&radius=' + encodeURIComponent(radiusKm) +
              '&target=all&limit=50';

    fetch(url)
        .then(function(res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function(data) {
            if (data && data.status === 'success') {
                measureBufferResults = data.results || [];
                renderBufferResults(measureBufferResults);
            } else {
                if (resultsList) resultsList.innerHTML = '<div style="font-size:11.5px;color:#ef4444;padding:8px 0;">Gagal: ' + escapeHtml((data && data.message) || 'Error') + '</div>';
            }
        })
        .catch(function(err) {
            if (resultsList) resultsList.innerHTML = '<div style="font-size:11.5px;color:#ef4444;padding:8px 0;">Kesalahan koneksi atau data tidak ditemukan.</div>';
        });
}

function renderBufferResults(results) {
    var resultsCount = document.getElementById('measureResultsCount');
    var resultsList = document.getElementById('measureResultsList');
    if (!resultsList) return;

    if (resultsCount) resultsCount.textContent = String(results.length);

    if (measureProximityLayerGroup) {
        measureProximityLayerGroup.clearLayers();
    }

    if (!results || results.length === 0) {
        resultsList.innerHTML = '<div style="font-size:11.5px;color:var(--text-secondary);padding:8px 0;">Tidak ada pulau atau wilayah dalam radius ini.</div>';
        return;
    }

    var html = '';
    results.forEach(function(item, idx) {
        var isIsland = (item.type === 'island');
        var distVal = (measureUnit === 'nm') ? (item.distance_nm.toFixed(2) + ' NM') : (item.distance_km.toFixed(2) + ' km');
        var badgeType = isIsland ? 'Pulau' : 'Wilayah';
        var bearingText = (item.bearing_deg !== undefined) ? (item.bearing_deg.toFixed(0) + '° ' + (item.direction || '')) : '';

        html += '<div class="measure-result-card" data-idx="' + idx + '" data-lat="' + item.lat + '" data-lng="' + item.lng + '">' +
                '  <div class="measure-card-info">' +
                '    <div class="measure-card-name">' + (isIsland ? '🏝️ ' : '🏛️ ') + escapeHtml(item.nama) + '</div>' +
                '    <div class="measure-card-meta">' + escapeHtml(item.kode) + ' &bull; ' + badgeType + '</div>' +
                '  </div>' +
                '  <div class="measure-card-distance">' +
                '    <span class="measure-card-dist-val">' + distVal + '</span>' +
                '    <span class="measure-card-bearing">' + bearingText + '</span>' +
                '  </div>' +
                '</div>';

        if (map && measureProximityLayerGroup) {
            var pinColor = isIsland ? '#10b981' : '#3b82f6';
            var pinMarker = L.circleMarker([item.lat, item.lng], {
                radius: 4.5,
                color: pinColor,
                weight: 1.5,
                fillColor: pinColor,
                fillOpacity: 0.85
            });

            pinMarker.bindPopup(
                '<div style="font-size:12px;font-family:inherit;">' +
                '  <strong>' + escapeHtml(item.nama) + '</strong> (' + escapeHtml(item.kode) + ')<br>' +
                '  <span style="color:var(--text-secondary);font-size:11px;">Jarak: <strong>' + distVal + '</strong> (' + bearingText + ')</span><br>' +
                '  <span style="font-size:10.5px;color:var(--text-secondary);">' + (item.status ? 'Status: ' + escapeHtml(item.status) : '') + '</span>' +
                '</div>'
            );

            measureProximityLayerGroup.addLayer(pinMarker);
        }
    });

    resultsList.innerHTML = html;

    var cards = resultsList.querySelectorAll('.measure-result-card');
    cards.forEach(function(card) {
        card.addEventListener('click', function() {
            var lat = parseFloat(card.getAttribute('data-lat'));
            var lng = parseFloat(card.getAttribute('data-lng'));
            if (!isNaN(lat) && !isNaN(lng) && map) {
                map.flyTo([lat, lng], 12);
            }
        });
    });
}

function exportMeasureBufferCsv() {
    if (!measureBufferCenter) {
        alert('Tentukan titik tengah radius di peta terlebih dahulu.');
        return;
    }

    var url = 'inc/geo_proximity.php?lat=' + encodeURIComponent(measureBufferCenter.lat) +
              '&lng=' + encodeURIComponent(measureBufferCenter.lng) +
              '&radius=' + encodeURIComponent(measureBufferRadius) +
              '&target=all&limit=200&format=csv';

    window.open(url, '_blank');
}

function initMeasurementControls() {
    var btnToggle = document.getElementById('btnToggleMeasure');
    if (btnToggle) {
        if (btnToggle.getAttribute('data-measure-bound')) return;
        btnToggle.setAttribute('data-measure-bound', 'true');
        btnToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleMeasureBar();
        });
    }

    var btnClose = document.getElementById('btnCloseMeasure');
    if (btnClose) {
        btnClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeMeasureBar();
        });
    }

    var btnRuler = document.getElementById('btnMeasureModeRuler');
    if (btnRuler) {
        btnRuler.addEventListener('click', function() {
            setMeasureMode('ruler');
        });
    }

    var btnBuffer = document.getElementById('btnMeasureModeBuffer');
    if (btnBuffer) {
        btnBuffer.addEventListener('click', function() {
            setMeasureMode('buffer');
        });
    }

    var btnClear = document.getElementById('btnMeasureClear');
    if (btnClear) {
        btnClear.addEventListener('click', function() {
            clearMeasurement();
        });
    }

    var btnKm = document.getElementById('btnUnitKm');
    if (btnKm) {
        btnKm.addEventListener('click', function() {
            setMeasureUnit('km');
        });
    }

    var btnNm = document.getElementById('btnUnitNm');
    if (btnNm) {
        btnNm.addEventListener('click', function() {
            setMeasureUnit('nm');
        });
    }

    var slider = document.getElementById('measureRadiusSlider');
    if (slider) {
        slider.addEventListener('input', function() {
            updateBufferRadius(this.value);
        });
    }

    var numInput = document.getElementById('measureRadiusInput');
    if (numInput) {
        numInput.addEventListener('change', function() {
            updateBufferRadius(this.value);
        });
    }

    var btnExportCsv = document.getElementById('btnExportMeasureCsv');
    if (btnExportCsv) {
        btnExportCsv.addEventListener('click', function(e) {
            e.preventDefault();
            exportMeasureBufferCsv();
        });
    }
}

// --- Init on load ---
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    initGlobalSearch();
    initShareButtons();
    hydrateFromUrl();
    initExportControls();
    initIslandClusterControls();
    initCompareControls();
    initTimelineControls();
    initMeasurementControls();

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

    // P4 exports
    window.getActiveRegionCode = getActiveRegionCode;
    window.getExportUrl = getExportUrl;
    window.triggerExportDownload = triggerExportDownload;
    window.initExportControls = initExportControls;

    // P5 exports
    window.getIslandClusterGroup = getIslandClusterGroup;
    window.populateIslandClusters = populateIslandClusters;
    window.clearIslandClusters = clearIslandClusters;
    window.highlightIslandInSidebar = highlightIslandInSidebar;
    window.initIslandClusterControls = initIslandClusterControls;
    window.formatIslandPopupHtml = formatIslandPopupHtml;

    // P6 exports
    window.openCompareModal = openCompareModal;
    window.closeCompareModal = closeCompareModal;
    window.switchCompareLevel = switchCompareLevel;
    window.loadCompareOptions = loadCompareOptions;
    window.fetchComparisonData = fetchComparisonData;
    window.renderComparisonView = renderComparisonView;
    window.renderDualPolygons = renderDualPolygons;
    window.generateCompareCsv = generateCompareCsv;
    window.downloadCompareCsv = downloadCompareCsv;
    window.calcRatioPairClient = calcRatioPairClient;
    window.formatNumberId = formatNumberId;
    window.initCompareControls = initCompareControls;

    // P7 exports
    window.initTimelineControls = initTimelineControls;
    window.openTimeline = openTimeline;
    window.closeTimeline = closeTimeline;
    window.toggleTimelineBar = toggleTimelineBar;
    window.toggleTimelinePlay = toggleTimelinePlay;
    window.changeTimelineYear = changeTimelineYear;
    window.fetchTimelineOverview = fetchTimelineOverview;
    window.renderHistoricalPaths = renderHistoricalPaths;
    window.renderDobHighlights = renderDobHighlights;
    window.clearTimelineLayers = clearTimelineLayers;

    // P8 exports
    window.initMeasurementControls = initMeasurementControls;
    window.setMeasureMode = setMeasureMode;
    window.openMeasureBar = openMeasureBar;
    window.closeMeasureBar = closeMeasureBar;
    window.toggleMeasureBar = toggleMeasureBar;
    window.clearMeasurement = clearMeasurement;
    window.handleMeasurementMapClick = handleMeasurementMapClick;
    window.addRulerPoint = addRulerPoint;
    window.setMeasureUnit = setMeasureUnit;
    window.setBufferCenter = setBufferCenter;
    window.drawBufferCircle = drawBufferCircle;
    window.updateBufferRadius = updateBufferRadius;
    window.fetchProximityData = fetchProximityData;
    window.renderBufferResults = renderBufferResults;
    window.exportMeasureBufferCsv = exportMeasureBufferCsv;
    window.calculateDistanceKm = calculateDistanceKm;
    window.calculateBearingDeg = calculateBearingDeg;
    window.bearingToDirection = bearingToDirection;
    window.kmToNm = kmToNm;
    window.nmToKm = nmToKm;
    window.formatMeasureDistance = formatMeasureDistance;
});

window.addEventListener('popstate', function() {
    hydrateFromUrl();
});
