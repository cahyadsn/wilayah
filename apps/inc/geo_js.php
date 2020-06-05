<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_js.php
purpose  :
create   : 2017/09/12
last edit: 200605,190830,170928
author   : cahya dsn
================================================================================
This program is free software; you can redistribute it and/or modify it under the
terms of the GNU General Public License as published by the Free Software
Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

copyright (c) 2017-2020 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
session_start();
header("Content-type: text/javascript");
if(isset($_SESSION['author']) && $_SESSION['author']=='cahyadsn'){
    $v=$_GET['v'];
    session_destroy();
} else {
    die('illegal call');
}
?>
var my_ajax=do_ajax();
var geo_ajax=do_ajax();
var ids,my_id,my_z;
var wil=new Array('prov','kota');
function ajax(id){
    $('div#preload').show();
    ids=id;
    var url="inc/geo_ajax.php?id="+id+"&sid="+Math.random();
    my_ajax.onreadystatechange=stateChanged;
    my_ajax.open("GET",url,true);
    my_ajax.send(null);
}
function geo(id){
    $('div#preload').show();
    ids=id;
    var geo_url="inc/geo_ajax.php?id="+id+"&geo=1&sid="+Math.random();
    geo_ajax.onreadystatechange=geoChanged;
    geo_ajax.open("GET",geo_url,true);
    geo_ajax.send(null);
}
function do_ajax(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

L.mapquest.key = '<MAPQUEST_KEY_HERE>';
var polyLatLngs;
var polyLayer;
var map;
var myPoint =[ -6.17501, 106.820497];
var marker;

function stateChanged(){
    var n=ids.length;
    var w=(n==2?wil[1]:'');
    var data;
    var prop = document.getElementById("prop");
    var kab = document.getElementById("kota");
    $('#l_kab').html(kab.options[kab.selectedIndex].value?kab.options[kab.selectedIndex].text:'-');
    $('#l_pro').html(prop.options[prop.selectedIndex].value?'PROVINSI '+prop.options[prop.selectedIndex].text:'-');
    if (my_ajax.readyState==4){
        data=my_ajax.responseText;
        var d=JSON.parse(data);
        if(w!='')
        document.getElementById(w).innerHTML = data.length>=0 ? d.opt:"<option selected>Pilih Kota/Kab</option>";
        $("#n_kode").text(d.data.kode);
        $("#n_lat").text(d.data.lat);
        $("#n_lng").text(d.data.lng);
        $("#kab_box").css('display',(n>1)?'table-row':'none');
        if(polyLayer)
        {
            map.removeLayer(polyLayer);
        }
        if(marker)
        {
            map.removeLayer(marker);
        }
        polyLatLngs=JSON.parse(d.data.path);
        myPoint=[d.data.lat,d.data.lng];
        marker = new L.marker(myPoint, {
           icon: L.mapquest.icons.marker(),
           draggable: false
         }).bindPopup('Kode wilayah '+d.data.kode).addTo(map);
        polyLayer=L.polygon(polyLatLngs, {color: 'blue'}).addTo(map);
        map.flyTo(myPoint, 9);
        $('div#preload').hide();
    }
}