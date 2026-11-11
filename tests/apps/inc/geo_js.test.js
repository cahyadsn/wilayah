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
  <div id="toastNotification" class="toast-notification"><span id="toastMessage"></span></div>
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
        window.L = {
            map: () => ({
                setView: () => {},
                on: () => {},
                hasLayer: () => false,
                addLayer: () => {},
                removeLayer: () => {},
                flyTo: () => {}
            }),
            control: Object.assign(() => ({ addTo: () => {} }), {
                zoom: () => ({ addTo: () => {} }),
                layers: () => ({ addTo: () => {} })
            }),
            tileLayer: () => ({ addTo: () => {} }),
            marker: () => ({ bindPopup: () => ({ addTo: () => {} }) }),
            polygon: () => ({ addTo: () => {} }),
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
    });

});
