/**
 * BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
 * ================================================================================
 * filename : geo_js.test.js
 * purpose  : Jest tests for apps/inc/geo_js.php frontend logic (P1, P2, P3, P4, P5, P6, P7)
 * last edit: 2026-11-23 14:30:00
 * ================================================================================
 */
const fs = require('fs');
const path = require('path');
// JSDOM dynamically loaded in beforeAll

describe('geo_js unit tests', () => {
    let window;
    let document;
    let setSafeSelectOptions;
    let renderSearchResults;
    let closeSearchResults;
    let selectSearchResult;
    let getUrlParams;
    let updateUrlState;
    let buildChainFromKode;
    let showToast;
    let getThematicColorDensity;
    let getThematicColorSexRatio;
    let getThematicColor;
    let thematicFeatureStyle;
    let setThematicMode;
    let getActiveRegionCode;
    let getExportUrl;
    let triggerExportDownload;
    let initExportControls;
    let getIslandClusterGroup;
    let populateIslandClusters;
    let clearIslandClusters;
    let highlightIslandInSidebar;
    let initIslandClusterControls;
    let formatIslandPopupHtml;
    let updateIslandPanel;
    let resetIslandPanel;
    let renderIslandItems;
    let onSelectIsland;
    let openCompareModal;
    let closeCompareModal;
    let switchCompareLevel;
    let loadCompareOptions;
    let fetchComparisonData;
    let renderComparisonView;
    let renderDualPolygons;
    let generateCompareCsv;
    let downloadCompareCsv;
    let calcRatioPairClient;
    let formatNumberId;
    let initCompareControls;
    let initTimelineControls;
    let openTimeline;
    let closeTimeline;
    let toggleTimelineBar;
    let toggleTimelinePlay;
    let changeTimelineYear;
    let fetchTimelineOverview;
    let renderHistoricalPaths;
    let renderDobHighlights;
    let clearTimelineLayers;

    beforeAll(async () => {
        const { JSDOM } = await import('jsdom');
        const dom = new JSDOM(`<!DOCTYPE html>
<html>
<body>
  <div id="map-canvas"></div>
  <div id="preload"></div>
  <span id="n_kode"></span>
  <span id="n_lat"></span>
  <span id="n_lng"></span>
  <div class="global-search-wrapper" id="globalSearchWrapper">
    <input type="text" id="globalSearchInput" />
    <button type="button" id="globalSearchClear" style="display:none;"></button>
    <span id="globalSearchSpinner" style="display:none;"></span>
    <div id="globalSearchResults" style="display:none;"></div>
  </div>
  <select id="prop"></select>
  <div id="kab_box" style="display:none;"><select id="kota"></select></div>
  <div id="kec_box" style="display:none;"><select id="kec"></select></div>
  <div id="kel_box" style="display:none;"><select id="kel"></select></div>
  <button id="btnShareLink"></button>
  <button id="btnCopyLocation"></button>
  <button id="btnOpenCompare" type="button"></button>
  <div id="compareModal" class="compare-modal" style="display:none;" aria-hidden="true">
    <div class="compare-dialog">
      <button id="btnCompareClose" type="button"></button>
      <button id="tabCompareProv" class="compare-tab-btn active" type="button"></button>
      <button id="tabCompareKab" class="compare-tab-btn" type="button"></button>
      <select id="compareSelect1"><option value="">-- Pilih Wilayah 1 --</option></select>
      <select id="compareSelect2"><option value="">-- Pilih Wilayah 2 --</option></select>
      <button id="btnRunCompare" type="button" disabled></button>
      <div id="compareEmptyState" style="display:block;"></div>
      <div id="compareLoading" style="display:none;"></div>
      <div id="compareError" style="display:none;"><span id="compareErrorMessage"></span></div>
      <div id="compareResults" style="display:none;"></div>
    </div>
  </div>
  <div class="map-overlay-export" id="exportDropdownWrapper">
    <button type="button" class="map-overlay-export-btn" id="btnExportMenu"></button>
    <div class="export-dropdown-menu" id="exportDropdownMenu" style="display:none;">
      <button type="button" id="exportGeoJsonBtn"></button>
      <button type="button" id="exportKmlBtn"></button>
      <button type="button" id="exportCsvStatsBtn"></button>
    </div>
  </div>
  <button type="button" id="btnExportIslands"></button>
  <div id="toastNotification" class="toast-notification"><span id="toastMessage"></span></div>
  <div class="island-panel" id="islandPanel">
    <div class="island-cluster-toggle" id="islandClusterToggleWrapper" style="display:none;">
      <label class="switch" for="toggleShowAllIslands">
        <input type="checkbox" id="toggleShowAllIslands" aria-label="Sebarkan seluruh pulau di peta" />
        <span class="slider round"></span>
      </label>
      <span class="toggle-label" id="islandClusterToggleLabel">Sebarkan di peta</span>
    </div>
    <span id="islandCount"></span>
    <span id="islandLocationName"></span>
    <div id="islandSearchWrapper" style="display:none;">
      <input type="text" id="islandSearch" />
    </div>
    <div id="islandList" style="display:none;"></div>
    <div id="islandEmptyState"></div>
  </div>
  <button id="btnToggleTimeline" type="button"></button>
  <div class="timeline-floating-bar" id="timelineFloatingBar" style="display:none;">
    <span class="timeline-badge">P7</span>
    <span class="timeline-title">Linimasa Regulasi Wilayah</span>
    <span class="timeline-year-tag" id="timelineCurrentYearTag">2025</span>
    <button type="button" id="btnCloseTimeline">&times;</button>
    <button type="button" id="btnTimelinePlay">
      <svg class="icon-play" style="display:block;"></svg>
      <svg class="icon-pause" style="display:none;"></svg>
    </button>
    <input type="range" class="timeline-slider-input" id="timelineSlider" min="0" max="5" value="5" />
    <div class="timeline-ticks" id="timelineTicks">
      <div class="timeline-tick" data-index="0" data-year="2016"><span class="tick-dot"></span><span class="tick-label">2016</span></div>
      <div class="timeline-tick" data-index="1" data-year="2018"><span class="tick-dot"></span><span class="tick-label">2018</span></div>
      <div class="timeline-tick" data-index="2" data-year="2020"><span class="tick-dot"></span><span class="tick-label">2020</span></div>
      <div class="timeline-tick" data-index="3" data-year="2022"><span class="tick-dot"></span><span class="tick-label">2022</span></div>
      <div class="timeline-tick" data-index="4" data-year="2023"><span class="tick-dot"></span><span class="tick-label">2023</span></div>
      <div class="timeline-tick is-active" data-index="5" data-year="2025"><span class="tick-dot"></span><span class="tick-label">2025</span></div>
    </div>
    <button type="button" id="btnTimelinePrev">◀</button>
    <button type="button" id="btnTimelineNext">▶</button>
    <div id="timelineRegTitle"></div>
    <div id="timelineStatusPill"></div>
    <div id="timelineDescText"></div>
    <div id="chipTimelineProv"></div>
    <div id="chipTimelineKab"></div>
    <div id="chipTimelineKec"></div>
    <div id="chipTimelineDesa"></div>
    <div id="chipTimelinePulau"></div>
    <div id="timelineDobCallout" style="display:none;">
      <button type="button" id="btnFocusDobPapua"></button>
      <a id="timelineRefLink" href="#"></a>
    </div>
  </div>
</body>
</html>`, {
            url: "http://localhost",
            runScripts: "dangerously"
        });
        window = dom.window;
        document = dom.window.document;

        // Mock required objects/functions on window to avoid eval errors
        window.do_ajax = () => ({
            open: () => {},
            send: function() {
                if (this.onreadystatechange) {
                    this.readyState = 4;
                    this.responseText = JSON.stringify({ status: true, data: { kode: '31.71', lat: -6.18, lng: 106.83 } });
                    this.onreadystatechange();
                }
            }
        });
        window.fetch = () => Promise.resolve({
            ok: true,
            json: () => Promise.resolve({ status: true, opt: '<option value="1">Item</option>' })
        });
        const activeMapLayers = [];
        window.L = {
            map: () => ({
                setView: () => {},
                on: () => {},
                hasLayer: (l) => activeMapLayers.includes(l),
                addLayer: (l) => { if (!activeMapLayers.includes(l)) activeMapLayers.push(l); },
                removeLayer: (l) => {
                    const idx = activeMapLayers.indexOf(l);
                    if (idx !== -1) activeMapLayers.splice(idx, 1);
                },
                flyTo: () => {}
            }),
            control: Object.assign(() => ({ addTo: () => {} }), {
                zoom: () => ({ addTo: () => {} }),
                layers: () => ({ addTo: () => {} })
            }),
            tileLayer: () => ({ addTo: () => {} }),
            marker: (coords) => {
                let popupContent = '';
                const listeners = {};
                return {
                    coords,
                    bindPopup: function(content) {
                        popupContent = content;
                        return this;
                    },
                    addTo: function(targetMap) {
                        if (targetMap && typeof targetMap.addLayer === 'function') {
                            targetMap.addLayer(this);
                        }
                        return this;
                    },
                    openPopup: function() { return this; },
                    getPopup: function() { return { getContent: () => popupContent }; },
                    on: function(event, handler) {
                        listeners[event] = handler;
                        return this;
                    },
                    fire: function(event) {
                        if (listeners[event]) listeners[event]();
                    }
                };
            },
            markerClusterGroup: (options) => {
                const layers = [];
                return {
                    options: options || {},
                    addLayer: function(l) { layers.push(l); },
                    addLayers: function(arr) { if (Array.isArray(arr)) arr.forEach(l => layers.push(l)); },
                    removeLayer: function(l) {
                        const idx = layers.indexOf(l);
                        if (idx !== -1) layers.splice(idx, 1);
                    },
                    clearLayers: function() { layers.length = 0; },
                    getLayers: function() { return layers; },
                    zoomToShowLayer: function(l, cb) { if (cb) cb(); }
                };
            },
            polygon: () => ({ addTo: function() { return this; }, bindPopup: function() { return this; } }),
            circleMarker: () => ({ addTo: function() { return this; }, bindPopup: function() { return this; } }),
            featureGroup: () => {
                const grpLayers = [];
                return {
                    addLayer: function(l) { grpLayers.push(l); return this; },
                    addTo: function() { return this; },
                    clearLayers: function() { grpLayers.length = 0; return this; }
                };
            },
            layerGroup: () => ({ addTo: () => {} }),
            DomEvent: {
                disableClickPropagation: () => {},
                disableScrollPropagation: () => {}
            },
            Control: {
                extend: () => function() { return { addTo: () => {} }; }
            },
            DomUtil: {
                create: (tag, className) => {
                    const el = document.createElement(tag);
                    if (className) el.className = className;
                    return el;
                }
            }
        };
        window.myPoint = [0, 0];
        window.getThemeAwareMapTileLayer = () => ({ addTo: () => {} });
        window.getThemeAwareLabelOverlay = () => {};

        // Read the script
        const geoJsPath = path.resolve(__dirname, '../../../apps/inc/geo_js.php');
        let geoJsCode = fs.readFileSync(geoJsPath, 'utf8');
        const jsStart = geoJsCode.indexOf('?>') + 2;
        geoJsCode = geoJsCode.substring(jsStart);

        // Evaluate in JSDOM context
        window.eval(geoJsCode);
        setSafeSelectOptions = window.setSafeSelectOptions;
        renderSearchResults = window.renderSearchResults;
        closeSearchResults = window.closeSearchResults;
        selectSearchResult = window.selectSearchResult;
        getUrlParams = window.getUrlParams;
        updateUrlState = window.updateUrlState;
        buildChainFromKode = window.buildChainFromKode;
        showToast = window.showToast;
        getThematicColorDensity = window.getThematicColorDensity;
        getThematicColorSexRatio = window.getThematicColorSexRatio;
        getThematicColor = window.getThematicColor;
        thematicFeatureStyle = window.thematicFeatureStyle;
        setThematicMode = window.setThematicMode;
        getActiveRegionCode = window.getActiveRegionCode;
        getExportUrl = window.getExportUrl;
        triggerExportDownload = window.triggerExportDownload;
        initExportControls = window.initExportControls;
        getIslandClusterGroup = window.getIslandClusterGroup;
        populateIslandClusters = window.populateIslandClusters;
        clearIslandClusters = window.clearIslandClusters;
        highlightIslandInSidebar = window.highlightIslandInSidebar;
        initIslandClusterControls = window.initIslandClusterControls;
        formatIslandPopupHtml = window.formatIslandPopupHtml;
        updateIslandPanel = window.updateIslandPanel;
        resetIslandPanel = window.resetIslandPanel;
        renderIslandItems = window.renderIslandItems;
        onSelectIsland = window.onSelectIsland;
        openCompareModal = window.openCompareModal;
        closeCompareModal = window.closeCompareModal;
        switchCompareLevel = window.switchCompareLevel;
        loadCompareOptions = window.loadCompareOptions;
        fetchComparisonData = window.fetchComparisonData;
        renderComparisonView = window.renderComparisonView;
        renderDualPolygons = window.renderDualPolygons;
        generateCompareCsv = window.generateCompareCsv;
        downloadCompareCsv = window.downloadCompareCsv;
        calcRatioPairClient = window.calcRatioPairClient;
        formatNumberId = window.formatNumberId;
        initCompareControls = window.initCompareControls;
        initTimelineControls = window.initTimelineControls;
        openTimeline = window.openTimeline;
        closeTimeline = window.closeTimeline;
        toggleTimelineBar = window.toggleTimelineBar;
        toggleTimelinePlay = window.toggleTimelinePlay;
        changeTimelineYear = window.changeTimelineYear;
        fetchTimelineOverview = window.fetchTimelineOverview;
        renderHistoricalPaths = window.renderHistoricalPaths;
        renderDobHighlights = window.renderDobHighlights;
        clearTimelineLayers = window.clearTimelineLayers;
    });

    describe('setSafeSelectOptions', () => {
        let selectElement;

        beforeEach(() => {
            selectElement = document.createElement('select');
        });

        test('should parse and set options from a valid HTML string', () => {
            const html = '<option value="1">Satu</option><option value="2">Dua</option>';
            setSafeSelectOptions(selectElement, html);

            expect(selectElement.children.length).toBe(2);
            expect(selectElement.children[0].value).toBe('1');
            expect(selectElement.children[0].textContent).toBe('Satu');
            expect(selectElement.children[1].value).toBe('2');
            expect(selectElement.children[1].textContent).toBe('Dua');
        });

        test('should clear existing options', () => {
            selectElement.innerHTML = '<option value="old">Old</option>';
            setSafeSelectOptions(selectElement, '<option value="new">New</option>');

            expect(selectElement.children.length).toBe(1);
            expect(selectElement.children[0].value).toBe('new');
        });

        test('should handle empty input', () => {
            selectElement.innerHTML = '<option value="old">Old</option>';
            setSafeSelectOptions(selectElement, null);

            expect(selectElement.children.length).toBe(0);

            selectElement.innerHTML = '<option value="old">Old</option>';
            setSafeSelectOptions(selectElement, '');

            expect(selectElement.children.length).toBe(0);
        });

        test('should handle selected attribute', () => {
            const html = '<option value="1">Satu</option><option value="2" selected>Dua</option>';
            setSafeSelectOptions(selectElement, html);

            expect(selectElement.children[1].hasAttribute('selected')).toBe(true);
            expect(selectElement.children[1].getAttribute('selected')).toBe('selected');
        });
    });

    describe('Global Search Autocomplete', () => {
        let resultsContainer;
        let searchInput;

        beforeEach(() => {
            resultsContainer = document.getElementById('globalSearchResults');
            searchInput = document.getElementById('globalSearchInput');
            if (resultsContainer) resultsContainer.innerHTML = '';
            if (searchInput) searchInput.value = '';
        });

        test('should render search results with correct items and badges', () => {
            const sampleResults = [
                {
                    kode: '34.04',
                    nama: 'Kabupaten Sleman',
                    category: 'wilayah',
                    tipe: 'Kabupaten',
                    parent: 'Daerah Istimewa Yogyakarta'
                },
                {
                    kode: '11.72.40008',
                    nama: 'Pulau Rondo',
                    category: 'pulau',
                    tipe: 'Pulau (TBP)',
                    parent: 'Kota Sabang, Aceh'
                }
            ];

            renderSearchResults(sampleResults, 'test');

            expect(resultsContainer.style.display).toBe('block');
            const items = resultsContainer.querySelectorAll('.global-search-item');
            expect(items.length).toBe(2);

            const firstBadge = items[0].querySelector('.search-item-badge');
            expect(firstBadge.textContent).toBe('Kabupaten');
            expect(items[0].querySelector('.search-item-code').textContent).toBe('34.04');
            expect(items[0].querySelector('.search-item-name').textContent).toBe('Kabupaten Sleman');
            expect(items[0].querySelector('.search-item-parent').textContent).toBe('Daerah Istimewa Yogyakarta');

            const islandBadge = items[1].querySelector('.search-item-badge');
            expect(islandBadge.classList.contains('badge-pulau')).toBe(true);
        });

        test('should close and clear search results', () => {
            resultsContainer.innerHTML = '<div class="global-search-item">item</div>';
            resultsContainer.style.display = 'block';

            closeSearchResults();

            expect(resultsContainer.style.display).toBe('none');
            expect(resultsContainer.innerHTML).toBe('');
            expect(searchInput.getAttribute('aria-expanded')).toBe('false');
        });

        test('should update search input when item is selected', () => {
            const item = {
                kode: '31.71',
                nama: 'Kota Jakarta Pusat',
                category: 'wilayah',
                tipe: 'Kota',
                chain: {
                    prov: { kode: '31', nama: 'DKI Jakarta' }
                }
            };

            selectSearchResult(item);

            expect(searchInput.value).toBe('Kota Jakarta Pusat (31.71)');
            expect(resultsContainer.style.display).toBe('none');
        });
    });

    describe('Shareable URL & Deep Linking (P2)', () => {
        describe('getUrlParams', () => {
            test('should correctly parse valid parameters', () => {
                const search = '?kode=32.73&pulau=11.01.40001&theme=dark&zoom=14&lat=-6.9147&lng=107.6098';
                const params = getUrlParams(search);

                expect(params.kode).toBe('32.73');
                expect(params.pulau).toBe('11.01.40001');
                expect(params.theme).toBe('dark');
                expect(params.zoom).toBe(14);
                expect(params.lat).toBeCloseTo(-6.9147);
                expect(params.lng).toBeCloseTo(107.6098);
            });

            test('should sanitize and reject illegal or malicious code parameters', () => {
                const maliciousSearches = [
                    '?kode=<script>alert(1)</script>',
                    '?kode=32%27%20OR%201=1',
                    '?kode=invalid-code',
                    '?kode=12.34.56.78.90.12.34.56' // too long
                ];

                maliciousSearches.forEach(search => {
                    const params = getUrlParams(search);
                    expect(params.kode).toBeNull();
                });
            });

            test('should sanitize and reject invalid island codes', () => {
                const invalidSearches = [
                    '?pulau=11.01',
                    '?pulau=11.01.abcde',
                    '?pulau=<script>',
                    '?pulau=11.01.400001' // too many digits
                ];

                invalidSearches.forEach(search => {
                    const params = getUrlParams(search);
                    expect(params.pulau).toBeNull();
                });
            });

            test('should validate theme strictly to light or dark', () => {
                expect(getUrlParams('?theme=light').theme).toBe('light');
                expect(getUrlParams('?theme=dark').theme).toBe('dark');
                expect(getUrlParams('?theme=blue').theme).toBeNull();
                expect(getUrlParams('?theme=<script>').theme).toBeNull();
            });
        });

        describe('buildChainFromKode', () => {
            test('should build correct chain for Province (Level 1)', () => {
                const chain = buildChainFromKode('11');
                expect(chain).toEqual({
                    prov: { kode: '11' }
                });
            });

            test('should build correct chain for Regency/City (Level 2)', () => {
                const chain = buildChainFromKode('32.73');
                expect(chain).toEqual({
                    prov: { kode: '32' },
                    kab: { kode: '32.73' }
                });
            });

            test('should build correct chain for District (Level 3)', () => {
                const chain = buildChainFromKode('32.73.01');
                expect(chain).toEqual({
                    prov: { kode: '32' },
                    kab: { kode: '32.73' },
                    kec: { kode: '32.73.01' }
                });
            });

            test('should build correct chain for Village (Level 4)', () => {
                const chain = buildChainFromKode('32.73.01.1001');
                expect(chain).toEqual({
                    prov: { kode: '32' },
                    kab: { kode: '32.73' },
                    kec: { kode: '32.73.01' },
                    kel: { kode: '32.73.01.1001' }
                });
            });

            test('should return null for invalid code strings', () => {
                expect(buildChainFromKode('')).toBeNull();
                expect(buildChainFromKode(null)).toBeNull();
                expect(buildChainFromKode('abc')).toBeNull();
                expect(buildChainFromKode('32.abc')).toBeNull();
            });
        });

        describe('Toast notification & UI', () => {
            test('showToast should display message and set is-visible class', () => {
                const toast = document.getElementById('toastNotification');
                const toastMsg = document.getElementById('toastMessage');

                showToast('Halo dunia!', 50);

                expect(toast.classList.contains('is-visible')).toBe(true);
                expect(toastMsg.textContent).toBe('Halo dunia!');

                if (window.toastTimer) {
                    clearTimeout(window.toastTimer);
                }
            });
        });

        describe('updateUrlState', () => {
            test('should update search params without throwing errors', () => {
                expect(() => {
                    updateUrlState({ kode: '32.73', theme: 'dark' });
                }).not.toThrow();

                expect(() => {
                    updateUrlState({ kode: null, pulau: '11.01.40001' });
                }).not.toThrow();
            });
        });

        describe('Choropleth Thematic Map (P3)', () => {
            describe('getThematicColorDensity', () => {
                test('should return correct color for different density tiers', () => {
                    expect(getThematicColorDensity(10000)).toBe('#bd0026');
                    expect(getThematicColorDensity(5001)).toBe('#bd0026');
                    expect(getThematicColorDensity(2500)).toBe('#f03b20');
                    expect(getThematicColorDensity(1000)).toBe('#fd8d3c');
                    expect(getThematicColorDensity(500)).toBe('#fecc5c');
                    expect(getThematicColorDensity(100)).toBe('#ffffb2');
                    expect(getThematicColorDensity(0)).toBe('#ffffb2');
                });
            });

            describe('getThematicColorSexRatio', () => {
                test('should return correct color for sex ratio tiers', () => {
                    expect(getThematicColorSexRatio(106)).toBe('#d73027');
                    expect(getThematicColorSexRatio(104)).toBe('#fc8d59');
                    expect(getThematicColorSexRatio(100)).toBe('#fee08b');
                    expect(getThematicColorSexRatio(96)).toBe('#91bfdb');
                    expect(getThematicColorSexRatio(90)).toBe('#4575b4');
                });
            });

            describe('getThematicColor', () => {
                test('should delegate to density by default', () => {
                    expect(getThematicColor(6000, 'density')).toBe('#bd0026');
                    expect(getThematicColor(50, 'unknown')).toBe('#ffffb2');
                });

                test('should delegate to sex ratio when metric is sex_ratio', () => {
                    expect(getThematicColor(106, 'sex_ratio')).toBe('#d73027');
                    expect(getThematicColor(90, 'sex_ratio')).toBe('#4575b4');
                });
            });

            describe('thematicFeatureStyle', () => {
                test('should return style object with expected properties', () => {
                    const style = thematicFeatureStyle({ properties: { value: 6000 } });
                    expect(style).toBeDefined();
                    expect(style.fillColor).toBe('#bd0026');
                    expect(style.weight).toBe(1.5);
                    expect(style.color).toBe('#ffffff');
                    expect(style.fillOpacity).toBe(0.72);
                });
            });

            describe('getUrlParams with thematic', () => {
                test('should parse thematic parameter from query string', () => {
                    const params = getUrlParams('?thematic=density&kode=32');
                    expect(params.thematic).toBe('density');
                    expect(params.kode).toBe('32');
                });

                test('should parse sex_ratio thematic parameter', () => {
                    const params = getUrlParams('?thematic=sex_ratio');
                    expect(params.thematic).toBe('sex_ratio');
                });

                test('should ignore invalid thematic parameters', () => {
                    const params = getUrlParams('?thematic=hack<script>');
                    expect(params.thematic).toBeNull();
                });
            });
        });

        describe('Spatial Data Exporter (P4)', () => {
            describe('getExportUrl', () => {
                test('should generate valid GeoJSON boundary export URL', () => {
                    const url = getExportUrl('32.73', 'geojson', 'boundary');
                    expect(url).toBe('inc/geo_export.php?kode=32.73&format=geojson&target=boundary');
                });

                test('should generate valid KML boundary export URL', () => {
                    const url = getExportUrl('11', 'kml', 'boundary');
                    expect(url).toBe('inc/geo_export.php?kode=11&format=kml&target=boundary');
                });

                test('should generate valid CSV stats export URL', () => {
                    const url = getExportUrl('32', 'csv', 'stats');
                    expect(url).toBe('inc/geo_export.php?kode=32&format=csv&target=stats');
                });

                test('should generate valid CSV islands export URL', () => {
                    const url = getExportUrl('11.01', 'csv', 'islands');
                    expect(url).toBe('inc/geo_export.php?kode=11.01&format=csv&target=islands');
                });

                test('should use fallback defaults for invalid format and target', () => {
                    const url = getExportUrl('32.73', 'invalid_format', 'invalid_target');
                    expect(url).toBe('inc/geo_export.php?kode=32.73&format=geojson&target=boundary');
                });

                test('should use all when code is empty or dash', () => {
                    expect(getExportUrl('', 'csv', 'stats')).toBe('inc/geo_export.php?kode=all&format=csv&target=stats');
                    expect(getExportUrl('—', 'csv', 'stats')).toBe('inc/geo_export.php?kode=all&format=csv&target=stats');
                });
            });

            describe('getActiveRegionCode', () => {
                test('should return code from n_kode when set', () => {
                    const nKode = document.getElementById('n_kode');
                    nKode.textContent = '32.73';
                    expect(getActiveRegionCode()).toBe('32.73');
                });

                test('should fallback to active select value when n_kode is dash', () => {
                    const nKode = document.getElementById('n_kode');
                    nKode.textContent = '—';
                    document.getElementById('kab_box').style.display = 'none';
                    document.getElementById('kec_box').style.display = 'none';
                    document.getElementById('kel_box').style.display = 'none';
                    const prop = document.getElementById('prop');
                    prop.innerHTML = '<option value="32" selected>Jawa Barat</option>';
                    prop.value = '32';
                    expect(getActiveRegionCode()).toBe('32');
                });
            });

            describe('initExportControls', () => {
                test('should toggle dropdown menu visibility on menu button click', () => {
                    initExportControls();
                    const menuBtn = document.getElementById('btnExportMenu');
                    const menu = document.getElementById('exportDropdownMenu');

                    expect(menu.style.display).toBe('none');
                    menuBtn.click();
                    expect(menu.style.display).toBe('flex');
                    menuBtn.click();
                    expect(menu.style.display).toBe('none');
                });
            });
        });

        describe('Feature P5: Island Marker Clustering (Leaflet.markercluster)', () => {
            const sampleIslands = [
                { kode: '11.01.40001', nama: 'Pulau Rondo', lat: '6.07472', lng: '95.11306', status: 'Tidak Berpenduduk', luas: '0.82' },
                { kode: '11.01.40002', nama: 'Pulau Weh', lat: '5.82139', lng: '95.31139', status: 'Berpenduduk', luas: '121.00' },
                { kode: '11.01.40003', nama: 'Pulau Breueh', lat: '5.70000', lng: '95.05000', status: null, luas: null },
                { kode: '11.01.40004', nama: 'Pulau Invalid', lat: null, lng: null }
            ];

            beforeEach(() => {
                clearIslandClusters();
                resetIslandPanel();
                if (window.localStorage) window.localStorage.clear();
            });

            describe('getIslandClusterGroup', () => {
                test('should create cluster group with performance options', () => {
                    const group = getIslandClusterGroup();
                    expect(group).toBeDefined();
                    expect(group.options.chunkedLoading).toBe(true);
                    expect(group.options.maxClusterRadius).toBe(50);
                    expect(group.options.spiderfyOnMaxZoom).toBe(true);
                    expect(group.options.showCoverageOnHover).toBe(false);
                });

                test('should return singleton instance', () => {
                    const group1 = getIslandClusterGroup();
                    const group2 = getIslandClusterGroup();
                    expect(group1).toBe(group2);
                });
            });

            describe('formatIslandPopupHtml', () => {
                test('should format popup template with title, kode, status, and luas', () => {
                    const html = formatIslandPopupHtml(sampleIslands[0], 6.07472, 95.11306);
                    expect(html).toContain('island-popup');
                    expect(html).toContain('Pulau Rondo');
                    expect(html).toContain('11.01.40001');
                    expect(html).toContain('Tidak Berpenduduk');
                    expect(html).toContain('0.82 km²');
                    expect(html).toContain('6.07472°');
                });

                test('should escape special characters to prevent XSS injection', () => {
                    const maliciousItem = {
                        kode: '<script>alert(1)</script>',
                        nama: '"><img src=x onerror=alert(2)>',
                        status: '<b>Bold</b>',
                        luas: '5" onmouseover="alert(3)'
                    };
                    const html = formatIslandPopupHtml(maliciousItem, 1.23456, 104.56789);
                    expect(html).not.toContain('<script>');
                    expect(html).toContain('&lt;script&gt;');
                    expect(html).not.toContain('<img src=x');
                    expect(html).toContain('&quot;&gt;&lt;img src=x');
                    expect(html).toContain('&lt;b&gt;Bold&lt;/b&gt;');
                });
            });

            describe('populateIslandClusters and clearIslandClusters', () => {
                test('should populate markers into cluster group and add group to map', () => {
                    populateIslandClusters(sampleIslands);
                    const group = getIslandClusterGroup();
                    const layers = group.getLayers();
                    // 3 valid islands out of 4 (4th has null coords)
                    expect(layers.length).toBe(3);
                    expect(window.map.hasLayer(group)).toBe(true);
                });

                test('should clear layers and remove group from map on clearIslandClusters', () => {
                    populateIslandClusters(sampleIslands);
                    const group = getIslandClusterGroup();
                    expect(group.getLayers().length).toBe(3);

                    clearIslandClusters();
                    expect(group.getLayers().length).toBe(0);
                    expect(window.map.hasLayer(group)).toBe(false);
                });

                test('should handle empty or null islands gracefully', () => {
                    expect(() => populateIslandClusters([])).not.toThrow();
                    expect(() => populateIslandClusters(null)).not.toThrow();
                });
            });

            describe('highlightIslandInSidebar', () => {
                test('should set active class on corresponding sidebar element and update infobox', () => {
                    updateIslandPanel(sampleIslands, 'Kab. Aceh Besar');
                    const listEl = document.getElementById('islandList');
                    expect(listEl.querySelectorAll('.island-item').length).toBe(4);

                    highlightIslandInSidebar('11.01.40002');

                    const target = listEl.querySelector('.island-item[data-kode="11.01.40002"]');
                    expect(target.classList.contains('is-active')).toBe(true);

                    expect(document.getElementById('n_kode').textContent).toBe('11.01.40002');
                    expect(document.getElementById('n_lat').textContent).toBe('5.82139');
                    expect(document.getElementById('n_lng').textContent).toBe('95.31139');
                });
            });

            describe('initIslandClusterControls', () => {
                test('should respect stored preference in localStorage', () => {
                    window.localStorage.setItem('wilayahShowAllIslands', '1');
                    initIslandClusterControls();
                    const toggle = document.getElementById('toggleShowAllIslands');
                    expect(toggle.checked).toBe(true);
                });

                test('should toggle cluster visibility and save preference on change event', () => {
                    window.localStorage.setItem('wilayahShowAllIslands', '0');
                    initIslandClusterControls();
                    const toggle = document.getElementById('toggleShowAllIslands');
                    expect(toggle.checked).toBe(false);

                    // Simulate user checking the toggle
                    updateIslandPanel(sampleIslands, 'Kab. Aceh Besar');
                    toggle.checked = true;
                    toggle.dispatchEvent(new window.Event('change'));

                    expect(window.localStorage.getItem('wilayahShowAllIslands')).toBe('1');
                    const group = getIslandClusterGroup();
                    expect(group.getLayers().length).toBe(3);

                    // Uncheck toggle
                    toggle.checked = false;
                    toggle.dispatchEvent(new window.Event('change'));
                    expect(window.localStorage.getItem('wilayahShowAllIslands')).toBe('0');
                    expect(group.getLayers().length).toBe(0);
                });
            });

            describe('updateIslandPanel and resetIslandPanel with clustering', () => {
                test('should display toggle wrapper when islands exist and hide when 0', () => {
                    const toggleWrap = document.getElementById('islandClusterToggleWrapper');
                    updateIslandPanel(sampleIslands, 'Kepulauan Riau');
                    expect(toggleWrap.style.display).toBe('flex');

                    updateIslandPanel([], 'Kota Tanpa Pulau');
                    expect(toggleWrap.style.display).toBe('none');
                });

                test('should auto-populate clusters if toggle switch is already checked', () => {
                    const toggle = document.getElementById('toggleShowAllIslands');
                    toggle.checked = true;

                    updateIslandPanel(sampleIslands, 'Kepulauan Riau');
                    const group = getIslandClusterGroup();
                    expect(group.getLayers().length).toBe(3);
                });

                test('resetIslandPanel should clear clusters and hide toggle wrapper', () => {
                    updateIslandPanel(sampleIslands, 'Kepulauan Riau');
                    const toggleWrap = document.getElementById('islandClusterToggleWrapper');
                    expect(toggleWrap.style.display).toBe('flex');

                    resetIslandPanel();
                    expect(toggleWrap.style.display).toBe('none');
                    const group = getIslandClusterGroup();
                    expect(group.getLayers().length).toBe(0);
                });
            });

            describe('onSelectIsland', () => {
                test('should use cluster zoomToShowLayer when clustering is active', () => {
                    const toggle = document.getElementById('toggleShowAllIslands');
                    toggle.checked = true;
                    updateIslandPanel(sampleIslands, 'Aceh');

                    const group = getIslandClusterGroup();
                    let zoomed = false;
                    group.zoomToShowLayer = (m, cb) => {
                        zoomed = true;
                        if (cb) cb();
                    };

                    onSelectIsland(sampleIslands[0]);
                    expect(zoomed).toBe(true);
                    expect(document.getElementById('n_kode').textContent).toBe('11.01.40001');
                });

                test('should fallback to single marker when clustering is inactive', () => {
                    const toggle = document.getElementById('toggleShowAllIslands');
                    toggle.checked = false;
                    updateIslandPanel(sampleIslands, 'Aceh');

                    onSelectIsland(sampleIslands[1]);
                    expect(document.getElementById('n_kode').textContent).toBe('11.01.40002');
                    expect(document.getElementById('n_lat').textContent).toBe('5.82139');
                });
            });
        });

        describe('Side-by-Side Region Comparison Tool (P6)', () => {
            const sampleCompareData = {
                status: 'success',
                level: 'provinsi',
                entities: {
                    entity1: {
                        kode: '31',
                        nama: 'DKI JAKARTA',
                        ibukota: 'JAKARTA PUSAT',
                        lat: -6.1754,
                        lng: 106.8272,
                        elv: 8,
                        timezone: 'WIB',
                        luas_km2: 661.52,
                        penduduk: 10609681,
                        kepadatan: 16038.34,
                        penduduk_pria: 5334147,
                        penduduk_wanita: 5275534,
                        sex_ratio: 101.11,
                        total_kab: 1,
                        total_kota: 5,
                        total_kec: 44,
                        total_kel: 267,
                        total_desa: 0,
                        total_pulau: 2,
                        path: '[[[-6.1,106.7],[-6.1,106.9],[-6.3,106.9],[-6.3,106.7],[-6.1,106.7]]]'
                    },
                    entity2: {
                        kode: '32',
                        nama: 'JAWA BARAT',
                        ibukota: 'KOTA BANDUNG',
                        lat: -6.9147,
                        lng: 107.6098,
                        elv: 768,
                        timezone: 'WIB',
                        luas_km2: 35377.76,
                        penduduk: 49405552,
                        kepadatan: 1396.51,
                        penduduk_pria: 25112000,
                        penduduk_wanita: 24293552,
                        sex_ratio: 103.37,
                        total_kab: 18,
                        total_kota: 9,
                        total_kec: 627,
                        total_kel: 645,
                        total_desa: 5312,
                        total_pulau: 1,
                        path: '[[[-6.5,107.0],[-6.5,108.5],[-7.8,108.5],[-7.8,107.0],[-6.5,107.0]]]'
                    }
                },
                comparison: {
                    luas_delta_percent: -98.13,
                    penduduk_delta_percent: -78.53,
                    kepadatan_delta_percent: 1048.46,
                    pulau_delta_percent: 100,
                    kec_delta_percent: -92.98,
                    desa_delta_percent: -100,
                    kel_delta_percent: -58.6,
                    kab_delta_percent: -94.44,
                    kota_delta_percent: -44.44,
                    ratios: {
                        luas: [1.8, 98.2],
                        penduduk: [17.7, 82.3],
                        kepadatan: [92.0, 8.0],
                        pulau: [66.7, 33.3],
                        kec: [6.6, 93.4],
                        desa: [0.0, 100.0],
                        kel: [29.3, 70.7],
                        kab: [5.3, 94.7],
                        kota: [35.7, 64.3]
                    }
                }
            };

            describe('calcRatioPairClient', () => {
                test('should calculate 50/50 for equal positive values', () => {
                    const res = calcRatioPairClient(100, 100);
                    expect(res).toEqual([50.0, 50.0]);
                });

                test('should calculate correct percentages for unequal values', () => {
                    const res = calcRatioPairClient(25, 75);
                    expect(res).toEqual([25.0, 75.0]);
                });

                test('should return 50/50 when both values are 0 or negative', () => {
                    expect(calcRatioPairClient(0, 0)).toEqual([50.0, 50.0]);
                    expect(calcRatioPairClient(-10, -20)).toEqual([50.0, 50.0]);
                });

                test('should clamp negative values to 0 in ratio calculation', () => {
                    const res = calcRatioPairClient(-5, 50);
                    expect(res).toEqual([0.0, 100.0]);
                });
            });

            describe('formatNumberId', () => {
                test('should format numbers with Indonesian separator conventions', () => {
                    expect(formatNumberId(1000)).toBe('1.000');
                    expect(formatNumberId(1234567)).toBe('1.234.567');
                    expect(formatNumberId(1234.56)).toBe('1.234,56');
                    expect(formatNumberId(0)).toBe('0');
                    expect(formatNumberId(null)).toBe('—');
                });
            });

            describe('Modal lifecycle: openCompareModal and closeCompareModal', () => {
                test('openCompareModal should display modal and set aria attributes', () => {
                    const modal = document.getElementById('compareModal');
                    openCompareModal('provinsi');
                    expect(modal.style.display).toBe('flex');
                    expect(modal.getAttribute('aria-hidden')).toBe('false');
                });

                test('closeCompareModal should hide modal and set aria-hidden to true', () => {
                    const modal = document.getElementById('compareModal');
                    openCompareModal('provinsi');
                    closeCompareModal();
                    expect(modal.style.display).toBe('none');
                    expect(modal.getAttribute('aria-hidden')).toBe('true');
                });

                test('Escape key should close modal', () => {
                    const modal = document.getElementById('compareModal');
                    openCompareModal('provinsi');
                    expect(modal.style.display).toBe('flex');

                    const escEvent = new window.KeyboardEvent('keydown', { key: 'Escape' });
                    document.dispatchEvent(escEvent);
                    expect(modal.style.display).toBe('none');
                });
            });

            describe('switchCompareLevel', () => {
                test('switching level should toggle active class on tabs', () => {
                    const tabProv = document.getElementById('tabCompareProv');
                    const tabKab = document.getElementById('tabCompareKab');

                    switchCompareLevel('kabkota');
                    expect(tabKab.classList.contains('active')).toBe(true);
                    expect(tabProv.classList.contains('active')).toBe(false);

                    switchCompareLevel('provinsi');
                    expect(tabProv.classList.contains('active')).toBe(true);
                    expect(tabKab.classList.contains('active')).toBe(false);
                });
            });

            describe('generateCompareCsv', () => {
                test('should generate properly structured CSV with BOM and correct headers', () => {
                    const csv = generateCompareCsv(sampleCompareData);
                    expect(csv.startsWith('\uFEFF')).toBe(true);
                    expect(csv).toContain('Indikator');
                    expect(csv).toContain('DKI JAKARTA');
                    expect(csv).toContain('JAWA BARAT');
                    expect(csv).toContain('Luas Wilayah (km2)');
                    expect(csv).toContain('Jumlah Penduduk');
                    expect(csv).toContain('Kepadatan Penduduk (jiwa/km2)');
                    expect(csv).toContain('Jumlah Pulau');
                });
            });

            describe('renderComparisonView', () => {
                test('should render entity cards, metric bars, and action toolbar', () => {
                    const container = document.getElementById('compareResults');
                    renderComparisonView(sampleCompareData);

                    expect(container.style.display).toBe('flex');
                    expect(container.innerHTML).toContain('DKI JAKARTA');
                    expect(container.innerHTML).toContain('JAWA BARAT');
                    expect(container.innerHTML).toContain('btnCompareShowMap');
                    expect(container.innerHTML).toContain('btnCompareCopyLink');
                    expect(container.innerHTML).toContain('btnCompareExportCsv');
                    expect(container.innerHTML).toContain('compare-bar-fill');
                });
            });

            describe('URL parameter integration (P6 deep link)', () => {
                test('getUrlParams should parse compare parameter', () => {
                    const params = getUrlParams('?compare=31,32');
                    expect(params.compare).toBe('31,32');
                });

                test('updateUrlState should retain or clear compare parameter', () => {
                    // When compare parameter is supplied
                    updateUrlState({ compare: '31,32' });
                    expect(window.location.search).toContain('compare=31%2C32');

                    // When compare is null, should remove it
                    updateUrlState({ compare: null });
                    expect(window.location.search).not.toContain('compare=');
                });
            });
        });

        describe('P7 - Historical Regional Evolution Timeline', () => {
            const sampleOverview = {
                status: 'success',
                version: '1.0.0',
                years: [2016, 2018, 2020, 2022, 2023, 2025],
                dob_milestone: 2023,
                snapshots: [
                    { year: 2016, regulasi: 'Permendagri No. 56 Tahun 2015', total_provinsi: 34, total_kab_kota: 514, total_kecamatan: 7094, total_desa_kel: 82030, total_pulau: 13466, highlights: 'Rujukan pasca-reformasi' },
                    { year: 2018, regulasi: 'Permendagri No. 137 Tahun 2017', total_provinsi: 34, total_kab_kota: 514, total_kecamatan: 7201, total_desa_kel: 83441, total_pulau: 16056, highlights: 'Pemutakhiran pulau' },
                    { year: 2020, regulasi: 'Permendagri No. 72 Tahun 2019', total_provinsi: 34, total_kab_kota: 514, total_kecamatan: 7230, total_desa_kel: 83820, total_pulau: 16056, highlights: 'Penyesuaian kode' },
                    { year: 2022, regulasi: 'Permendagri No. 58/2021', total_provinsi: 34, total_kab_kota: 514, total_kecamatan: 7266, total_desa_kel: 83467, total_pulau: 16772, highlights: 'Gazeter Nasional' },
                    { year: 2023, regulasi: 'UU No. 14, 15, 16, 29 / 2022', total_provinsi: 38, total_kab_kota: 514, total_kecamatan: 7277, total_desa_kel: 83794, total_pulau: 17001, highlights: 'Pemekaran 4 DOB Papua', has_dob: true, new_regions_count: 4 },
                    { year: 2025, regulasi: 'Kepmendagri No 300.2.2-2430 Tahun 2025', total_provinsi: 38, total_kab_kota: 514, total_kecamatan: 7285, total_desa_kel: 83762, total_pulau: 17374, highlights: 'Regulasi terkini' }
                ]
            };

            beforeEach(() => {
                window.timelineSnapshots = sampleOverview.snapshots;
                window.timelineYears = sampleOverview.years;
            });

            test('openTimeline and closeTimeline should toggle floating bar and button state', () => {
                const bar = document.getElementById('timelineFloatingBar');
                const btn = document.getElementById('btnToggleTimeline');

                openTimeline();
                expect(bar.style.display).toBe('block');
                expect(btn.classList.contains('is-active')).toBe(true);
                expect(btn.getAttribute('aria-expanded')).toBe('true');

                closeTimeline();
                expect(bar.style.display).toBe('none');
                expect(btn.classList.contains('is-active')).toBe(false);
                expect(btn.getAttribute('aria-expanded')).toBe('false');
            });

            test('toggleTimelineBar should alternate between open and close', () => {
                const bar = document.getElementById('timelineFloatingBar');
                closeTimeline();
                expect(bar.style.display).toBe('none');

                toggleTimelineBar();
                expect(bar.style.display).toBe('block');

                toggleTimelineBar();
                expect(bar.style.display).toBe('none');
            });

            test('changeTimelineYear should update slider, year tag, and active tick', () => {
                changeTimelineYear(2020);

                const slider = document.getElementById('timelineSlider');
                const yearTag = document.getElementById('timelineCurrentYearTag');
                const activeTick = document.querySelector('.timeline-tick.is-active');

                expect(yearTag.textContent).toBe('2020');
                expect(slider.value).toBe('2'); // index 2 for 2020
                expect(activeTick).not.toBeNull();
                expect(activeTick.getAttribute('data-year')).toBe('2020');
            });

            test('changeTimelineYear should update milestone text, status pill, and chips', () => {
                changeTimelineYear(2016);
                const title = document.getElementById('timelineRegTitle');
                const pill = document.getElementById('timelineStatusPill');
                const chipProv = document.getElementById('chipTimelineProv');

                expect(title.textContent).toContain('56');
                expect(pill.textContent).toBe('Historis');
                expect(pill.classList.contains('is-historical')).toBe(true);
                expect(chipProv.innerHTML).toContain('34');

                // Switch to 2025 (latest)
                changeTimelineYear(2025);
                expect(pill.textContent).toBe('Terkini');
                expect(pill.classList.contains('is-historical')).toBe(false);
                expect(chipProv.innerHTML).toContain('38');
            });

            test('changeTimelineYear should display DOB callout on 2023 and hide it on pre-2023', () => {
                const dobCallout = document.getElementById('timelineDobCallout');

                changeTimelineYear(2023);
                expect(dobCallout.style.display).toBe('flex');

                changeTimelineYear(2022);
                expect(dobCallout.style.display).toBe('none');
            });

            test('toggleTimelinePlay should alternate play and pause states', () => {
                const btn = document.getElementById('btnTimelinePlay');
                const iconPlay = btn.querySelector('.icon-play');
                const iconPause = btn.querySelector('.icon-pause');

                // Start playback
                toggleTimelinePlay();
                expect(iconPlay.style.display).toBe('none');
                expect(iconPause.style.display).toBe('block');

                // Stop playback
                toggleTimelinePlay();
                expect(iconPlay.style.display).toBe('block');
                expect(iconPause.style.display).toBe('none');
            });

            test('initTimelineControls should bind controls without throwing', () => {
                expect(() => initTimelineControls()).not.toThrow();
            });
        });
    });

});
