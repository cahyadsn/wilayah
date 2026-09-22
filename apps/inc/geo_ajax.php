<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
filename : geo_ajax.php
purpose  :
create   : 170912
last edit: 2026-09-11 09:59:31
author   : cahya dsn
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
*/
require_once "db.php";
require_once "geo_helpers.php";

function isPathReasonable($path, $lat, $lng, $kode) {
  if (empty($path) || $lat === null || $lng === null) return false;
  $coords = json_decode($path, true);
  if (!is_array($coords) || empty($coords)) return false;

  $points = (isset($coords[0][0]) && is_numeric($coords[0][0])) ? $coords : (is_array($coords[0]) ? $coords[0] : array());
  if (empty($points)) return false;

  $lats = array_column($points, 0);
  $lngs = array_column($points, 1);

  if (empty($lats) || empty($lngs)) return false;

  $latMin = min($lats);
  $latMax = max($lats);
  $lngMin = min($lngs);
  $lngMax = max($lngs);

  $centerLat = ($latMin + $latMax) / 2;
  $centerLng = ($lngMin + $lngMax) / 2;
  $codeLen = strlen($kode);
  $threshold = ($codeLen >= 13 ? 0.03 : ($codeLen >= 8 ? 0.08 : 2.5));

  return abs($centerLat - (float)$lat) <= $threshold && abs($centerLng - (float)$lng) <= $threshold;
}

$r=array('status'=>false,'error'=>'an error occured');
$out = null;

if (!empty($_GET['id']) && is_string($_GET['id'])){
  $cache_dir = dirname(__DIR__) . '/cache';
  if (!is_dir($cache_dir)) {
      mkdir($cache_dir, 0755, true);
  }
  $geo_param = empty($_GET['geo']) ? '0' : '1';
  $cache_file_json = $cache_dir . '/geo_ajax_json_cache_' . md5($_GET['id'] . '_' . $geo_param) . '.json';
  $cache_ttl = 86400; // 1 day

  if (file_exists($cache_file_json) && (time() - filemtime($cache_file_json) < $cache_ttl)) {
      $out = file_get_contents($cache_file_json);
  } else {
      // Try get from table first (has geo data: lat, lng, path, luas, penduduk)
      $query = $db->prepare("SELECT kode, nama, lat, lng, path, luas, penduduk FROM {$tbl_wilayah} WHERE kode=:id");
      $query->execute(array(':id'=>$_GET['id']));
      $d = $query->fetchObject();
      if(!empty($d) && !empty($d->kode)){
        $path=$d->path;
        if(empty($path) || !isPathReasonable($path, $d->lat, $d->lng, $d->kode)){
          $path = json_encode(fallbackPathForCode($d->lat, $d->lng, $d->kode));
        }
        $data=array('kode'=>$d->kode,'nama'=>$d->nama,'lat'=>$d->lat,'lng'=>$d->lng,'path'=>$path,'luas'=>$d->luas,'penduduk'=>$d->penduduk);
        $r=array('status'=>true,'data'=>$data);
      }
      if(empty($_GET['geo'])){
        $n=strlen($_GET['id']);
        $m=($n==2?5:($n==5?8:13));
        $wil=($n==2?'Kota/Kab':($n==5?'Kecamatan':'Desa/Kelurahan'));

        $cache_file = $cache_dir . '/geo_opt_cache_' . md5($_GET['id']) . '.html';

        if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_ttl)) {
            $opt = file_get_contents($cache_file);
        } else {
            $query = $db->prepare("SELECT kode, nama FROM {$tbl_wilayah} WHERE kode LIKE CONCAT(:id, '%') AND CHAR_LENGTH(kode)=:m ORDER BY nama");
            $query->execute(array(':id'=>addcslashes($_GET['id'], '%_\\'),':m'=>$m));
            $opt_arr = ["<option value=''>Pilih {$wil}</option>"];
            while($d = $query->fetchObject()){
                $kode = htmlspecialchars($d->kode, ENT_QUOTES, 'UTF-8');
                $nama = htmlspecialchars($d->nama, ENT_QUOTES, 'UTF-8');
                $opt_arr[] = "<option value='{$kode}'>{$nama}</option>";
            }
            $opt = implode('', $opt_arr);
            file_put_contents($cache_file, $opt, LOCK_EX);
        }

        $r['opt']=$opt;
        $r['n']=$n;
      }
      $should_cache = true;
  }
}

if ($out === null) {
  $out = json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
  if (!empty($should_cache) && isset($cache_file_json)) {
      file_put_contents($cache_file_json, $out, LOCK_EX);
  }
}

header('Content-Type: application/json; charset=utf-8');
echo $out;
