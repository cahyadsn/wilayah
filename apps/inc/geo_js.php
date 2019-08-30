<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_js.php
purpose  :
create   : 2017/09/12
last edit: 190830,170928
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
session_start();
header("Content-type: text/javascript");
if(isset($_SESSION['author']) && $_SESSION['author']=='cahyadsn'){
    $v=$_GET['v'];
    session_destroy();
} else {
    die('illegal call');
}
?>
var drag_me=edit_me=false;
$('#id_btn_update').prop('disabled',edit_me);
var pesan=function(msg){
  $("#msg_box").html(msg);
  $("#msg_box").addClass("w3-red w3-center");
  $("#msg_box").show();
  $("#msg_box").delay(2000).fadeOut();
}
var my_ajax=do_ajax();
var geo_ajax=do_ajax();
var ids,my_id,my_z;
var wil=new Array('prov','kota','kec','kel');
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

var map;
var geocoder;
var marker;
var myPolygon;
var markersArray = [];
var bounds,ne,sw,nw,se,squareCoords;
function initialize() {
    geocoder = new google.maps.Geocoder();
    var myLatlng =new google.maps.LatLng(-6.176655999999999, 106.83058389999997);
    var mapOptions = {
    center: myLatlng,
    zoom: 14
    };
    map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);
    marker = new google.maps.Marker({
        position: myLatlng,
        map: map,
        title: 'Jakarta'
    });
    ne = new google.maps.LatLng(-6.176655999999999, 106.83058389999997);
    sw = new google.maps.LatLng(-6.176655999999999, 106.83058389999997);
    nw = new google.maps.LatLng(ne.lat(), sw.lng());
    se = new google.maps.LatLng(sw.lat(), ne.lng());
    squareCoords = [
        ne,nw,sw,se
    ];
    myPolygon = new google.maps.Polygon({
        paths: squareCoords,
        draggable: drag_me,
        editable: edit_me,
        strokeColor: '#FF0000',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: '#FF0000',
        fillOpacity: 0.35
    })
    myPolygon.setMap(map);
    markersArray.push(marker);
    google.maps.event.addListener(marker,"click",function(){});
    google.maps.event.addListener(map, "mousemove", function (e) {
    var t = e.latLng;
    document.getElementById("mlat").innerHTML = "(" + t.lat().toFixed(6) + ", " + t.lng().toFixed(6) + ")"
    })
}

function clearOverlays() {
    for (var i = 0; i < markersArray.length; i++ ) {
        markersArray[i].setMap(null);
    }
    markersArray.length = 0;
}

function geoChanged(){
    var n=ids.length;
    var data;
    var kel = document.getElementById("kel");
    var jenis=kel.options[kel.selectedIndex].value.substr(9,1)=='1'?'KELURAHAN ':'DESA ';
    $('#l_kel').html(kel.options[kel.selectedIndex].value?jenis+kel.options[kel.selectedIndex].text.toUpperCase():'-');
    if (geo_ajax.readyState==4){
        data=geo_ajax.responseText;
        var d=JSON.parse(data);
        if(!d.status){
        var geocoder;
        geocoder = new google.maps.Geocoder();
        var prop = document.getElementById("prop");
        var kab = document.getElementById("kota");
        var kec = document.getElementById("kec");
        var s = kel.options[kel.selectedIndex].text
                +', '
                +kec.options[kec.selectedIndex].text;
            s2= s
                +', '
                +kab.options[kab.selectedIndex].text
                +', '
                +prop.options[prop.selectedIndex].text;
        geocoder.geocode(
            {'address':s},
            function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                clearOverlays();
                var position=results[0].geometry.location;
                bounds=results[0].geometry.bounds;
                if(typeof bounds !== 'undefined') {
                    ne = bounds.getNorthEast();
                    sw = bounds.getSouthWest();
                    nw = new google.maps.LatLng(ne.lat(), sw.lng());
                    se = new google.maps.LatLng(sw.lat(), ne.lng());
                    squareCoords = [
                        ne,nw,sw,se
                    ];
                    myPolygon.setMap(null);
                    myPolygon = new google.maps.Polygon({
                paths: squareCoords,
                draggable: drag_me,
                editable: edit_me,
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.35
                    })
                    myPolygon.setMap(map);
                    getPolygonCoords();
                    google.maps.event.addListener(myPolygon.getPath(), "insert_at", getPolygonCoords);
                    google.maps.event.addListener(myPolygon.getPath(), "set_at", getPolygonCoords);
                    $("#n_kode").val(kel.value);
                    $("#n_lat").val(position.lat());
                    $("#n_lng").val(position.lng());
                    $("#n_kodepos").val(d.kodepos);
                        $.post(
                'inc/geo_update.php',
                {id:$('#kel').val(),lat:position.lat(),lng:position.lng(),path:$('#poly').html()},
                function(data){
                var d=JSON.parse(data);
                console.log(d.msg);
                }
                    );
                    map.setCenter(results[0].geometry.location);
                    marker = new google.maps.Marker({
                        map: map,
                        position: results[0].geometry.location,
                        title:s2
                    });
                    markersArray.push(marker);
                    google.maps.event.addListener(marker,"click",function(){});
                } else {
                    pesan('Data not found');
                    $('div#preload').hide();
                    $("#floating-panel").hide();
                }
                } else {
                presan('Geocode was not successful for the following reason: ' + status);
                $('div#preload').hide();
                $("#floating-panel").hide();
                }
            }
        );
        }else{
        $("#n_kode").val($('#kel').val());
        $("#n_lat").val(d.data.lat);
        $("#n_lng").val(d.data.lng);
        $("#n_kodepos").val(d.data.kodepos);
        $('#floating-panel').show();
        myPolygon.setMap(null);
        console.log(d.data.path);
        myPolygon = new google.maps.Polygon({
            paths: eval(d.data.path),
            draggable: drag_me,
            editable: edit_me,
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#FF0000',
            fillOpacity: 0.35
        })
        myPolygon.setMap(map);
        getPolygonCoords();
        google.maps.event.addListener(myPolygon.getPath(), "insert_at", getPolygonCoords);
        google.maps.event.addListener(myPolygon.getPath(), "set_at", getPolygonCoords);
        position=new google.maps.LatLng(d.data.lat,d.data.lng);
        clearOverlays();
        map.setCenter(position);
        marker = new google.maps.Marker({
            map: map,
            position: position,
            title:$('#kel').val()
        });
        markersArray.push(marker);
        google.maps.event.addListener(marker,"click",function(){});
        $('div#preload').hide();
        }
    }
}
function getPolygonCoords() {
    var len = myPolygon.getPath().getLength();
    var htmlStr = "[<br>";
    var p;
    for (var i = 0; i < len; i++) {
        p=myPolygon.getPath().getAt(i);
        htmlStr += "{lat:" + p.lat() + ",lng:"+p.lng()+"}"+(i<len-1?',':'')+"<br>";
    }
    htmlStr += "]"
    $('#poly').html(htmlStr);
    $('div#preload').hide();
    $('#floating-panel').show();
}
google.maps.event.addDomListener(window, 'load', initialize);
if (!google.maps.Polygon.prototype.getBounds) {
    google.maps.Polygon.prototype.getBounds=function(){
        var bounds = new google.maps.LatLngBounds()
        this.getPath().forEach(function(element,index){bounds.extend(element)})
        return bounds
    }
}

function stateChanged(){
    var n=ids.length;
    var w=(n==2?wil[1]:(n==5?wil[2]:wil[3]));
    var data;
    r prop = document.getElementById("prop");
    r kab = document.getElementById("kota");
    r kec = document.getElementById("kec");
    r kel = document.getElementById("kel");
    $('#l_kel').html(kel.options[kel.selectedIndex].value?kel.options[kel.selectedIndex].text.toUpperCase():'-');
    '#l_kec').html(kec.options[kec.selectedIndex].value?'KECAMATAN '+kec.options[kec.selectedIndex].text.toUpperCase():'-');
    '#l_kab').html(kab.options[kab.selectedIndex].value?kab.options[kab.selectedIndex].text:'-');
    '#l_pro').html(prop.options[prop.selectedIndex].value?'PROVINSI '+prop.options[prop.selectedIndex].text:'-');
    //$('#floating-panel').hide();
    if (my_ajax.readyState==4){
        data=my_ajax.responseText;
        var d=JSON.parse(data);
        console.log(d);
    //--
        if(!d.status){
            var geocoder;
            geocoder = new google.maps.Geocoder();
            var s = prop.options[prop.selectedIndex].text;
                s =((d.n==5)?kab.options[kab.selectedIndex].text+',':'')+s;
                s =((d.n==8)?kec.options[kec.selectedIndex].text+', ':'')+s;
            var s2 =(d.n==8)?kec.options[kec.selectedIndex].text:'';
                s2 =((d.n==13)?kel.options[kel.selectedIndex].text+', ':'')+s2;
            if(d.n>8) s=s2;
            geocoder.geocode(
                {'address':s},
                function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        clearOverlays();
                        var position=results[0].geometry.location;
                        bounds=results[0].geometry.bounds;
                        if(typeof bounds !== 'undefined') {
                        ne = bounds.getNorthEast();
                        sw = bounds.getSouthWest();
                        nw = new google.maps.LatLng(ne.lat(), sw.lng());
                        se = new google.maps.LatLng(sw.lat(), ne.lng());
                        squareCoords = [
                            ne,nw,sw,se
                        ];
                        myPolygon.setMap(null);
                        myPolygon = new google.maps.Polygon({
                            paths: squareCoords,
                            draggable: drag_me,
                            editable: edit_me,
                            strokeColor: '#FF0000',
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: '#FF0000',
                            fillOpacity: 0.35
                        })
                        myPolygon.setMap(map);
                        getPolygonCoords();
                        google.maps.event.addListener(myPolygon.getPath(), "insert_at", getPolygonCoords);
                        google.maps.event.addListener(myPolygon.getPath(), "set_at", getPolygonCoords);
                        $("#n_kode").val(d.n==2?$('#prop').val():(d.n==5?$('#kota').val():(d.n==8?$('#kec').val():$('#kel').val())));
                        $("#n_lat").val(position.lat());
                        $("#n_lng").val(position.lng());
                        $("#n_kodepos").val(d.kodepos);
                        my_id=d.n==2?$('#prop').val():(d.n==5?$('#kota').val():(d.n==8?$('#kec').val():$('#kel').val()));
                        my_z=d.n==2?9:(d.n==5?11:(d.n==8?13:14));
                        $.post(
                            'inc/geo_update.php',
                            {id:my_id,lat:position.lat(),lng:position.lng(),path:$('#poly').html()},
                            function(data){
                            var d=JSON.parse(data);
                            console.log(d.msg);
                            }
                        );
                        map.fitBounds(myPolygon.getBounds());
                        marker = new google.maps.Marker({
                            map: map,
                            position: results[0].geometry.location,
                            title:s2
                        });
                        markersArray.push(marker);
                        google.maps.event.addListener(marker,"click",function(){});
                    } else {
                        pesan('Data not found');
                        $('div#preload').hide();
                        $("#floating-panel").hide();
                    }
                } else {
                    pesan('Geocode was not successful for the following reason: ' + status);
                    $('div#preload').hide();
                    $("#floating-panel").hide();
                }
            });
        }else{
            my_id=d.n==2?$('#prop').val():(d.n==5?$('#kota').val():(d.n==8?$('#kec').val():$('#kel').val()));
            my_z=d.n==2?9:(d.n==5?11:(d.n==8?13:14));
            $("#n_kode").val(my_id);
            $("#n_lat").val(d.data.lat);
            $("#n_lng").val(d.data.lng);
            $("#n_kodepos").val(d.data.kodepos);
            $("#poly").html(d.data.path);
            myPolygon.setMap(null);
            console.log(d.data.path);
            myPolygon = new google.maps.Polygon({
                paths: eval(d.data.path),
                draggable: drag_me,
                editable: edit_me,
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.35
            })
            myPolygon.setMap(map);
            getPolygonCoords();
            google.maps.event.addListener(myPolygon.getPath(), "insert_at", getPolygonCoords);
            google.maps.event.addListener(myPolygon.getPath(), "set_at", getPolygonCoords);
            position=new google.maps.LatLng(d.data.lat,d.data.lng);
            clearOverlays();
            map.fitBounds(myPolygon.getBounds());
            marker = new google.maps.Marker({
                map: map,
                position: position,
                title:my_id
            });
            markersArray.push(marker);
            google.maps.event.addListener(marker,"click",function(){});
            $('div#preload').hide();
        }
        //--
        document.getElementById(w).innerHTML = data.length>=0 ? d.opt:"<option selected>Pilih Kota/Kab</option>";
        $("#kab_box").css('display',(n>1)?'table-row':'none');
        $("#kec_box").css('display',(n>4)?'table-row':'none');
        $("#kel_box").css('display',(n>7)?'table-row':'none');
        $('#floating-panel').show();
        $('div#preload').hide();
        }
}