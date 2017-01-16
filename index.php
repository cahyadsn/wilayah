<?php
$dbhost ='localhost';
$dbuser ='root';
$dbpass ='';
$dbname ='wilayah';
$db_dsn = "mysql:dbname=$dbname;host=$dbhost";
try {
  $db = new PDO($db_dsn, $dbuser, $dbpass);
} catch (PDOException $e) {
  echo 'Connection failed: '.$e->getMessage();
}
if (!empty($_GET['id'])){
	$n=strlen($_GET['id']);
	$m=($n==2?5:($n==5?8:13));
	$wil=($n==2?'Kota/Kab':($n==5?'Kecamatan':'Desa/Kelurahan'));
	$query = $db->prepare("SELECT * FROM wilayah WHERE LEFT(kode,:n)=:id AND CHAR_LENGTH(kode)=:m ORDER BY nama");
	$query->execute(array(':n'=>$n,':id'=>$_GET['id'],':m'=>$m));
	echo"<option value=''>Pilih {$wil}</option>";
	while($d = $query->fetchObject()){
		echo "<option value='{$d->kode}'>{$d->nama}</option>";
	}
}else{
?>
<!doctype html>
<html>
	<head>
		<title>Data Daerah</title>
		<style>
			select {width:240px;}
			#kab_box,#kec_box,#kel_box{display:none;}
		 </style>
		<script>
		var my_ajax=do_ajax();
		var ids;
		var wil=new Array('prov','kota','kec','kel');
		function ajax(id){
			ids=id;
			var url="?id="+id+"&sid="+Math.random();
			my_ajax.onreadystatechange=stateChanged;
			my_ajax.open("GET",url,true);
			my_ajax.send(null);
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
		function stateChanged(){
			var n=ids.length;
			var w=(n==2?wil[1]:(n==5?wil[2]:wil[3]));
			var data;
			if (my_ajax.readyState==4){
				data=my_ajax.responseText;
				document.getElementById(w).innerHTML = data.length>=0 ? data:"<option selected>Pilih Kota/Kab</option>";
				document.getElementById("kab_box").style.display=(n>1)?'table-row':'none';
				document.getElementById("kec_box").style.display=(n>4)?'table-row':'none';
				document.getElementById("kel_box").style.display=(n>7)?'table-row':'none';
			}
		}
		</script>
	</head>
	<body>
		<table>
			<tr>
			<td>Pilih Provinsi</td>
			<td>
				<select name="prop" id="prop" onchange="ajax(this.value)">
					<option value="">Pilih Provinsi</option>
					<?php 
					$query=$db->prepare("SELECT kode,nama FROM wilayah WHERE CHAR_LENGTH(kode)=2 ORDER BY nama");
					$query->execute();
					while ($data=$query->fetchObject()){
						echo '<option value="'.$data->kode.'">'.$data->nama.'</option>';
					}
					?>
				<select>
			</td>
		</tr>
		<tr id='kab_box'>
			<td>Pilih Kota/Kab</td>
			<td>
				<select name="kota" id="kota" onchange="ajax(this.value)">
					<option value="">Pilih Kota</option>
				</select>
			</td>
		</tr>
		<tr id='kec_box'>
			<td>Pilih Kec</td>
			<td>
				<select name="kec" id="kec" onchange="ajax(this.value)">
					<option value="">Pilih Kecamatan</option>
				</select>
			</td>
		</tr>
		<tr id='kel_box'>
			<td>Pilih Kelurahan/Desa</td>
			<td>
				<select name="kel" id="kel">
					<option value="">Pilih Kelurahan/Desa</option>
				</select>
			</td>
		</tr>
	</body>
</html>
<?php } ?>
