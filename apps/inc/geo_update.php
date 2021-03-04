<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_update.php
purpose  :
create   : 170912
last edit: 210304
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

copyright (c) 2017-2021 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
include "db.php";
$r=array('status'=>false,'msg'=>'do nothing');
$fields=array('lat','lng','path');
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
