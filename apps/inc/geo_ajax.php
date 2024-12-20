<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_ajax.php
purpose  :
create   : 170912
last edit: 2024-12-19 10:31:03
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

copyright (c) 2017-2024 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
include "db.php";
$r=array('status'=>false,'error'=>'an error occured');
if (!empty($_GET['id'])){
  $query = $db->prepare("SELECT * FROM {$tbl_wilayah} WHERE kode=:id");
  $query->execute(array(':id'=>$_GET['id']));
  $d = $query->fetchObject();
  if(empty($d->lat)){
    $r=array('status'=>false,'error'=>'data not found');
  }else{
    $path=$d->path;
    if(empty($path)){
      $path='[['
            .($d->lat-0.01).','.($d->lng-0.01).'],['
            .($d->lat+0.01).','.($d->lng-0.01).'],['
            .($d->lat+0.01).','.($d->lng+0.01).'],['
            .($d->lat-0.01).','.($d->lng+0.01).']]';
    }
    $data=array('kode'=> $d->kode,'nama'=> $d->nama,'lat'=> $d->lat,'lng'=> $d->lng,'path'=>$path,'luas'=>$d->luas,'penduduk'=>$d->penduduk);
    $r=array('status'=>true,'data'=>$data);
  }
  if(empty($_GET['geo'])){
    $n=strlen($_GET['id']);
    $m=($n==2?5:($n==5?8:13));
    $wil=($n==2?'Kota/Kab':($n==5?'Kecamatan':'Desa/Kelurahan'));
    $query = $db->prepare("SELECT * FROM {$tbl_wilayah} WHERE LEFT(kode,:n)=:id AND CHAR_LENGTH(kode)=:m ORDER BY nama");
    $query->execute(array(':n'=>$n,':id'=>$_GET['id'],':m'=>$m));
    $opt="<option value=''>Pilih {$wil}</option>";
    while($d = $query->fetchObject()){
        $opt.="<option value='{$d->kode}'>{$d->nama}</option>";
    }
    $r['opt']=$opt;
    $r['n']=$n;
  }
}
echo json_encode($r);
