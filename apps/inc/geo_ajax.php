<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_ajax.php
purpose  :
create   : 170912
last edit: 2026-06-01 21:39:05
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
require_once "db.php";
function fallbackBox($lat, $lng, $delta = 0.01) {
  return '[['.($lat-$delta).','.($lng-$delta).'],'
       .'['.($lat+$delta).','.($lng-$delta).'],'
       .'['.($lat+$delta).','.($lng+$delta).'],'
       .'['.($lat-$delta).','.($lng+$delta).']]';
}

function isPathReasonable($path, $lat, $lng, $kode) {
  if (empty($path) || $lat === null || $lng === null) return false;
  $coords = json_decode($path, true);
  if (!is_array($coords) || empty($coords)) return false;

  $points = (isset($coords[0][0]) && is_numeric($coords[0][0])) ? $coords : (is_array($coords[0]) ? $coords[0] : array());
  if (empty($points)) return false;

  $latMin = $latMax = (float)$points[0][0];
  $lngMin = $lngMax = (float)$points[0][1];
  foreach ($points as $pt) {
    if (!is_array($pt) || count($pt) < 2) continue;
    $plat = (float)$pt[0];
    $plng = (float)$pt[1];
    if ($plat < $latMin) $latMin = $plat;
    if ($plat > $latMax) $latMax = $plat;
    if ($plng < $lngMin) $lngMin = $plng;
    if ($plng > $lngMax) $lngMax = $plng;
  }

  $centerLat = ($latMin + $latMax) / 2;
  $centerLng = ($lngMin + $lngMax) / 2;
  $codeLen = strlen($kode);
  $threshold = ($codeLen >= 13 ? 0.03 : ($codeLen >= 8 ? 0.08 : 2.5));

  return abs($centerLat - (float)$lat) <= $threshold && abs($centerLng - (float)$lng) <= $threshold;
}

$r=array('status'=>false,'error'=>'an error occured');
if (!empty($_GET['id'])){
  // Try wilayah_level_1_2 first (has geo data: lat, lng, path, luas, penduduk)
  $query = $db->prepare("SELECT * FROM wilayah_level_1_2 WHERE kode=:id");
  $query->execute(array(':id'=>$_GET['id']));
  $d = $query->fetchObject();
  if(!empty($d) && !empty($d->kode)){
    $path=$d->path;
    if(!isPathReasonable($path, $d->lat, $d->lng, $d->kode)){
      $delta = (strlen($d->kode) >= 13 ? 0.004 : (strlen($d->kode) >= 8 ? 0.008 : 0.01));
      $path = fallbackBox($d->lat, $d->lng, $delta);
    }
    $data=array('kode'=>$d->kode,'nama'=>$d->nama,'lat'=>$d->lat,'lng'=>$d->lng,'path'=>$path,'luas'=>$d->luas,'penduduk'=>$d->penduduk);
    $r=array('status'=>true,'data'=>$data);
  } else {
    // Fallback to wilayah table (kode + nama only)
    $query = $db->prepare("SELECT * FROM {$tbl_wilayah} WHERE kode=:id");
    $query->execute(array(':id'=>$_GET['id']));
    $d = $query->fetchObject();
    if(!empty($d) && !empty($d->kode)){
      $r=array('status'=>true,'data'=>array('kode'=>$d->kode,'nama'=>$d->nama,'lat'=>-6.17501,'lng'=>106.820497,'path'=>'','luas'=>0,'penduduk'=>0));
    }
  }
  if(empty($_GET['geo'])){
    $n=strlen($_GET['id']);
    $m=($n==2?5:($n==5?8:13));
    $wil=($n==2?'Kota/Kab':($n==5?'Kecamatan':'Desa/Kelurahan'));
    $query = $db->prepare("SELECT * FROM {$tbl_wilayah} WHERE kode LIKE CONCAT(:id, '%') AND CHAR_LENGTH(kode)=:m ORDER BY nama");
    $query->execute(array(':id'=>$_GET['id'],':m'=>$m));
    $opt="<option value=''>Pilih {$wil}</option>";
    while($d = $query->fetchObject()){
        $opt.="<option value='{$d->kode}'>{$d->nama}</option>";
    }
    $r['opt']=$opt;
    $r['n']=$n;
  }
}
echo json_encode($r);
