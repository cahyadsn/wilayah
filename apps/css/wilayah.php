<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : css/wilayah.php
purpose  :
create   : 2017/09/12
last edit: 190830,170927
author   : cahya dsn
================================================================================
This program is free software; you can redistribute it and/or modify it under the
terms of the GNU General Public License as published by the Free Software
Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

copyright (c) 2017-2019 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
//if(!defined('_AUTHOR')) die('illegal access forbiden');
header("Content-type: text/css");
?>
#map-canvas {width:100%;height:600px;border:solid #999 1px;}
select {width:240px;}
#kab_box,#kec_box,#kel_box,#msg_box{display:none;}
#floating-panel { position: relative;top: -590px;height:530px;left: 10px; z-index: 5; background-color: #fff; padding: 5px; border: 1px solid #999;width:350px !important;min-width:320px; font-size:0.7em;display:none;}
p#poly{overflow-y:scroll;height:170px;width:320px !important; min-width:310px;}
table.input {font-size:0.7em;}
div#preload{display:none;}