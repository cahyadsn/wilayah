const fs = require('fs');
const path = require('path');
// JSDOM dynamically loaded in beforeAll

describe('setSafeSelectOptions', () => {
    let window;
    let document;
    let DOMParser;
    let setSafeSelectOptions;

    beforeAll(async () => {
        const { JSDOM } = await import('jsdom');
        const dom = new JSDOM('<!DOCTYPE html><html><body><div id="map-canvas"></div></body></html>', {
            url: "http://localhost",
            runScripts: "dangerously"
        });
        window = dom.window;
        document = dom.window.document;
        DOMParser = dom.window.DOMParser;

        // Mock required objects/functions on window to avoid eval errors
        window.do_ajax = () => ({});
        window.L = {
            map: () => ({
                setView: () => {},
                on: () => {},
                hasLayer: () => false,
                addLayer: () => {},
                removeLayer: () => {}
            }),
            control: Object.assign(() => ({ addTo: () => {} }), {
                zoom: () => ({ addTo: () => {} }),
                layers: () => ({ addTo: () => {} })
            }),
            tileLayer: () => ({ addTo: () => {} }),
            marker: () => ({ bindPopup: () => ({ addTo: () => {} }) }),
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
    });

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
