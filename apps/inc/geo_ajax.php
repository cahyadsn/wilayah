<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_ajax.php
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

copyright (c) 2015-2019 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
include "db.php";
$r=array('status'=>false,'error'=>'an error occured');
if (!empty($_GET['id'])){
  $query = $db->prepare("SELECT * FROM {$tbl_wilayah} WHERE kode=:id");
  $query->execute(array(':id'=>$_GET['id']));
  $d = $query->fetchObject();
  if(empty($d->lat)){
    $r=array('status'=>false,'error'=>'data not found','kodepos'=>$d->kodepos);
  }else{
    $path=$d->path;
    if(empty($path)){ 
      $path=array(
            array('lat'=>$d->lat-0.01,'lng'=>$d->lng-0.01),
            array('lat'=>$d->lat+0.01,'lng'=>$d->lng-0.01),
            array('lat'=>$d->lat+0.01,'lng'=>$d->lng+0.01),
            array('lat'=>$d->lat-0.01,'lng'=>$d->lng+0.01)
      );
    }
    $data=array('kode'=> $d->kode,'lat'=> $d->lat,'lng'=> $d->lng,'kodepos'=> $d->kodepos,'path'=>$path);
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
}else{
  $data=array(
    ':lat'=>$_POST['lat'],
    ':lng'=>$_POST['lng'],
    ':kodepos'=>$_POST['kodepos'],
    ':path'=>$_POST['path']
  );
  $sql="UPDATE {$tbl_wilayah} SET ";
  $field=array();
  foreach($data as $f=>$v){
    $field[]=substr($f,1)."={$f}";
  }
  $sql.=implode(',',$field)." WHERE kode=:kode";
  $data[':kode']=$_POST['kode'];
  $query = $db->prepare($sql);
  $query->execute($data);
  $r=array('status'=>true,'msg'=>'data saved' );  
}
echo json_encode($r);