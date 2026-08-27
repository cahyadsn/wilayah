<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : change.color.php
purpose  : change theme and/or color
create   : 170912
last edit: 2026-08-24 10:43:38
author   : cahya dsn
================================================================================
This program is free software; you can redistribute it and/or modify it under the
terms of the MIT License.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

See the MIT License for more details

copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
if(isset($_POST) && !empty($_POST)){
    require_once __DIR__ . '/session.php';

    // Verify CSRF token
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !is_string($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('CSRF token validation failed');
    }

    //-- set web theme, default 'dark'
    if(isset($_POST['theme'])){
        $_SESSION['theme']=$_POST['theme']==='light'?'light':'dark';
    }
    //-- set web color theme
    if(isset($_POST['color']) && is_string($_POST['color'])){
        $_SESSION['c']=htmlspecialchars($_POST['color'], ENT_QUOTES, 'UTF-8');
    }
}
