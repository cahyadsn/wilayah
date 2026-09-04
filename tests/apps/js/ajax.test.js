const fs = require('fs');
const path = require('path');

// Read the original file
const ajaxJsPath = path.resolve(__dirname, '../../../apps/js/ajax.js');
const ajaxJsCode = fs.readFileSync(ajaxJsPath, 'utf8');

// Evaluate the script in the context of the test environment
eval(ajaxJsCode);

describe('do_ajax', () => {
    let originalXMLHttpRequest;

    beforeEach(() => {
        // Backup the original XMLHttpRequest object, if any
        originalXMLHttpRequest = global.window ? global.window.XMLHttpRequest : undefined;

        // Mock the window object if it doesn't exist in Jest's environment
        if (!global.window) {
            global.window = {};
        }
    });

    afterEach(() => {
        // Restore the original XMLHttpRequest object
        if (originalXMLHttpRequest !== undefined) {
            global.window.XMLHttpRequest = originalXMLHttpRequest;
        } else {
            delete global.window.XMLHttpRequest;
        }
    });

    test('returns XMLHttpRequest instance when window.XMLHttpRequest is available', () => {
        // Create a mock XMLHttpRequest class
        class MockXMLHttpRequest {
            constructor() {
                this.mock = true;
            }
        }

        // Setup window.XMLHttpRequest
        global.window.XMLHttpRequest = MockXMLHttpRequest;
        global.XMLHttpRequest = MockXMLHttpRequest;

        const result = do_ajax();

        expect(result).toBeInstanceOf(MockXMLHttpRequest);
    });

    test('returns null when window.XMLHttpRequest is not available', () => {
        // Ensure window.XMLHttpRequest is undefined
        global.window.XMLHttpRequest = undefined;

        const result = do_ajax();

        expect(result).toBeNull();
    });
});
