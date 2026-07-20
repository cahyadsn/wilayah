<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : css/wilayah.php
purpose  :
create   : 2017/09/12
last edit: 2026-06-11 15:54:53
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
//if(!defined('_AUTHOR')) die('illegal access forbiden');
header("Content-type: text/css");
?>
body,h1,h1,h3,input {font-family:"Raleway", Arial, Sans-serif;}
#map-canvas {width:100%;height:400px;border:solid #999 1px;}
select {width:240px;}
#kab_box,#kec_box,#kel_box,#msg_box {display:none;}
#floating-panel { position: relative;top: -590px;height:530px;left: 10px; z-index: 5; background-color: #fff; padding: 5px; border: 1px solid #999;width:350px !important;min-width:320px; font-size:0.7em;display:none;}
p#poly {overflow-y:scroll;height:170px;width:320px !important; min-width:310px;}
table.input {font-size:0.7em;}
div#preload {display:none;}