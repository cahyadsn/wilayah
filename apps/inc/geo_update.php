<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_update.php
purpose  :
create   : 170912
last edit: 2026-10-22 09:20:00
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
require_once __DIR__ . '/session.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['author']) || $_SESSION['author'] !== 'cahyadsn') {
    die(json_encode(array('status' => false, 'msg' => 'unauthorized'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
}

if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !is_string($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die(json_encode(array('status' => false, 'msg' => 'csrf token validation failed'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
}

require_once "db.php";
$r=array('status'=>false,'msg'=>'do nothing');
$fields=array('lat','lng','path');
if(isset($_POST['id']) && is_string($_POST['id']) && preg_match('/^[0-9]+(\.[0-9]+)*$/', $_POST['id']) && strlen($_POST['id']) <= 13){
  $sql="UPDATE {$tbl_wilayah} SET ";
  $field=$data=array();
  foreach($fields as $f){
    if(isset($_POST[$f]) && $_POST[$f] !== ''){
      if ($f === 'lat' || $f === 'lng') {
        if (is_numeric($_POST[$f]) && is_finite((float)$_POST[$f])) {
          $field[]="{$f}=:{$f}";
          $data[":{$f}"]=(float)$_POST[$f];
        }
      } elseif ($f === 'path') {
        $decoded = json_decode($_POST[$f], true);
        if ($decoded !== null || $_POST[$f] === '') {
          $field[]="{$f}=:{$f}";
          $data[":{$f}"]=$_POST[$f];
        }
      }
    }
  }
  if (!empty($field)) {
    $sql.=implode(',',$field)." WHERE kode=:id";
    $data[':id']=$_POST['id'];
    $query = $db->prepare($sql);
    $query->execute($data);
    $r=array('status'=>true,'msg'=>'data saved');
    $cache_dir = dirname(__DIR__) . '/cache';
    if (is_dir($cache_dir)) {
      @unlink($cache_dir . '/geo_ajax_json_cache_' . hash('sha256', $_POST['id'] . '_0') . '.json');
      @unlink($cache_dir . '/geo_ajax_json_cache_' . hash('sha256', $_POST['id'] . '_1') . '.json');
      @unlink($cache_dir . '/geo_opt_cache_' . hash('sha256', $_POST['id']) . '.html');
    }
  } else {
    $r=array('status'=>false,'msg'=>'No valid fields to update');
  }
}
echo json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
