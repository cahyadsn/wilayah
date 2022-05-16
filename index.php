<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename 	: index.php
purpose  	: main application page
create   	: 20170117
last edit	: 20220516
author   	: cahya dsn
demo site 	: https://wilayah.cahyadsn.com/v2
soure code 	: https://github.com/cahyadsn/wilayah
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

copyright (c) 2017-2022 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
//--- Database configuration
$dbhost ='localhost';
$dbuser ='root';
$dbpass ='';
$dbname ='wilayah';
$dbdsn = "mysql:dbname={$dbname};host={$dbhost}";
try {
  $db = new PDO($dbdsn, $dbuser, $dbpass);
} catch (PDOException $e) {
  echo 'Connection failed: '.$e->getMessage();
}
$wil=array(
	2=>array(5,'Kota/Kabupaten','kab'),
	5=>array(8,'Kecamatan','kec'),
	8=>array(13,'Kelurahan','kel')
);
if (isset($_GET['id']) && !empty($_GET['id'])){
	$n=strlen($_GET['id']);
	$query = $db->prepare("SELECT * FROM wilayah WHERE LEFT(kode,:n)=:id AND CHAR_LENGTH(kode)=:m ORDER BY nama");
	$query->execute(array(':n'=>$n,':id'=>$_GET['id'],':m'=>$wil[$n][0]));
	echo"<option value=''>Pilih {$wil[$n][1]}</option>";
	while($d = $query->fetchObject())
		echo "<option value='{$d->kode}'>{$d->nama}</option>";
}else{
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Data Daerah</title>
		<style>
			td,select {width:240px;}
			#kab_box,#kec_box,#kel_box{display:none;}
		 </style>
		<script>
		var my_ajax=do_ajax();
		var ids;
		var wil=new Array('kab','kec','kel');
		function ajax(id){
			if(id.length<13){
				ids=id;
				var url="?id="+id+"&sid="+Math.random();
				my_ajax.onreadystatechange=stateChanged;
				my_ajax.open("GET",url,true);
				my_ajax.send(null);
			}
		}
		function do_ajax(){
			if (window.XMLHttpRequest) return new XMLHttpRequest();
			if (window.ActiveXObject) return new ActiveXObject("Microsoft.XMLHTTP");
			return null;
		}
		function stateChanged(){
			var n=ids.length;
			var w=(n==2?wil[0]:(n==5?wil[1]:wil[2]));
			var data;
			if (my_ajax.readyState==4){
				data=my_ajax.responseText;
				document.getElementById(w).innerHTML = data.length>=0 ? data:"<option selected>Pilih Kota/Kab</option>";
				<?php foreach($wil as $k=>$w):?>
					document.getElementById("<?php echo $w[2];?>_box").style.display=(n><?php echo $k-1;?>)?'table-row':'none';
				<?php endforeach;?>
			}
		}
		</script>
	</head>
	<body>
		<table>
			<tr>
			<td>Provinsi</td>
			<td>
				<select id="prov" onchange="ajax(this.value)">
					<option value="">Provinsi</option>
					<?php 
					$query=$db->prepare("SELECT kode,nama FROM wilayah WHERE CHAR_LENGTH(kode)=2 ORDER BY nama");
					$query->execute();
					while ($data=$query->fetchObject())
						echo '<option value="'.$data->kode.'">'.$data->nama.'</option>';
					?>
				<select>
			</td>
		</tr>
		<?php foreach($wil as $w):?>
		<tr id='<?php echo $w[2];?>_box'>
			<td><?php echo $w[1];?></td>
			<td>
				<select id="<?php echo $w[2];?>" onchange="ajax(this.value)">
					<option value="">Pilih <?php echo $w[1];?></option>
				</select>
			</td>
		</tr>
		<?php endforeach;?>
	</body>
</html>
<?php } ?>