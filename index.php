<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename 	: index.php
purpose  	: main application page
create   	: 20170117
last edit	: 2026-06-17 13:42:39
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
require_once 'apps/inc/db.php';
$wil=array(
	2=>array(5,'Kota/Kabupaten','kab'),
	5=>array(8,'Kecamatan','kec'),
	8=>array(13,'Kelurahan','kel')
);
if (isset($_GET['id']) && !empty($_GET['id'])){
	$n=strlen($_GET['id']);
	if (isset($wil[$n])) {
		$query = $db->prepare("SELECT kode, nama FROM wilayah WHERE kode LIKE :id AND CHAR_LENGTH(kode)=:m ORDER BY nama");
		$query->execute(array(':id'=>$_GET['id'].'%',':m'=>$wil[$n][0]));
		echo"<option value=''>Pilih {$wil[$n][1]}</option>";
		while($d = $query->fetchObject())
			echo "<option value='{$d->kode}'>{$d->nama}</option>";
	}
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
		const wil = ['kab', 'kec', 'kel'];
		
		function resetSelect(targetId) {
			const selectEl = document.getElementById(targetId);
			if (selectEl) {
				const label = targetId === 'kab' ? 'Kota/Kabupaten' : (targetId === 'kec' ? 'Kecamatan' : 'Kelurahan');
				selectEl.innerHTML = `<option value="">Pilih ${label}</option>`;
			}
			const boxEl = document.getElementById(`${targetId}_box`);
			if (boxEl) {
				boxEl.style.display = 'none';
			}
		}

		function ajax(id){
			if (!id) {
				wil.forEach(resetSelect);
				return;
			}
			const n = id.length;
			if(n < 13){
				if (n === 2) {
					resetSelect('kec');
					resetSelect('kel');
				} else if (n === 5) {
					resetSelect('kel');
				}

				const url = "?id=" + encodeURIComponent(id) + "&sid=" + Math.random();
				fetch(url)
					.then(response => {
						if (!response.ok) {
							throw new Error('Network response was not ok');
						}
						return response.text();
					})
					.then(data => {
						const w = (n === 2 ? wil[0] : (n === 5 ? wil[1] : wil[2]));
						document.getElementById(w).innerHTML = data.length >= 0 ? data : `<option selected>Pilih ${w === 'kab' ? 'Kota/Kab' : (w === 'kec' ? 'Kecamatan' : 'Kelurahan')}</option>`;
						<?php foreach($wil as $k=>$w):?>
							document.getElementById("<?php echo $w[2];?>_box").style.display = (n > <?php echo $k-1;?>) ? 'table-row' : 'none';
						<?php endforeach;?>
					})
					.catch(error => {
						console.error('Fetch error:', error);
					});
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
					$cache_file = sys_get_temp_dir() . '/provinsi_cache.html';
					$cache_ttl = 86400;

					if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_ttl)) {
						echo file_get_contents($cache_file);
					} else {
						$query=$db->prepare("SELECT kode,nama FROM wilayah WHERE CHAR_LENGTH(kode)=2 ORDER BY nama");
						$query->execute();
						$arr = [];
						while ($data=$query->fetchObject()){
							$arr[] = '<option value="'.$data->kode.'">'.$data->nama.'</option>';
						}
						$html = implode('', $arr);
						file_put_contents($cache_file, $html, LOCK_EX);
						echo $html;
					}
					?>
				</select>
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