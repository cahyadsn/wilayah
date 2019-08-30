<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_update.php
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
include "db.php";
$r=array('status'=>false,'msg'=>'do nothing');
$fields=array('lat','lng','kodepos','path');
if(isset($_POST['id'])){
  $sql="UPDATE {$tbl_wilayah} SET ";
  $field=$data=array();
  foreach($fields as $f){
    if(isset($_POST[$f]) && !empty($_POST[$f])){
      $field[]="{$f}=:{$f}";
      $data[":{$f}"]=strip_tags($_POST[$f]);
    }
  }
  $sql.=implode(',',$field)." WHERE kode=:id";
  $data[':id']=$_POST['id'];
  $query = $db->prepare($sql);
  $query->execute($data);
  $r=array('status'=>true,'msg'=>'data saved '.$sql.json_encode($data));
}
echo json_encode($r);