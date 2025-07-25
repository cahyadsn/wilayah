/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : db/wilayah_pendudk.sql
purpose  :
note     : Data jumlah penduduk dan kode wilayah sesuai Kepmendagri 
           No 300.2.2-2138 Tahun 2025
create   : 2025-06-27 18:42:05
last edit: 2025-07-04 07:58:13
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

copyright (c) 2025 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
--
-- Table structure for table penduduk
--

DROP TABLE IF EXISTS wilayah_penduduk;
CREATE TABLE IF NOT EXISTS wilayah_penduduk (
    kode varchar(13) NOT NULL,
    nama varchar(100) NOT NULL,
    pria integer NOT NULL,
    wanita integer NOT NULL,
    total integer NOT NULL,
    PRIMARY KEY (kode)
);
CREATE INDEX penduduk_nama_idx ON wilayah_penduduk(nama);
--
-- Dumping data for table penduduk
--

-- 
INSERT INTO wilayah_penduduk(kode, nama, pria, wanita, total)
VALUES
('0','INDONESIA','143863392','141110251','284973643'),
('11','Aceh','2815060','2808419','5623479'),
('11.01','Kabupaten Aceh Selatan','120041','119588','239629'),
('11.02','Kabupaten Aceh Tenggara','118140','117449','235589'),
('11.03','Kabupaten Aceh Timur','232341','229050','461391'),
('11.04','Kabupaten Aceh Tengah','117470','115136','232606'),
('11.05','Kabupaten Aceh Barat','105470','103750','209220'),
('11.06','Kabupaten Aceh Besar','221967','221020','442987'),
('11.07','Kabupaten Pidie','222253','228823','451076'),
('11.08','Kabupaten Aceh Utara','319278','321729','641007'),
('11.09','Kabupaten Simeulue','49595','47217','96812'),
('11.10','Kabupaten Aceh Singkil','70857','69604','140461'),
('11.11','Kabupaten Bireuen','230892','237804','468696'),
('11.12','Kabupaten Aceh Barat Daya','78693','77435','156128'),
('11.13','Kabupaten Gayo Lues','54118','53388','107506'),
('11.14','Kabupaten Aceh Jaya','51377','50030','101407'),
('11.15','Kabupaten Nagan Raya','91321','89782','181103'),
('11.16','Kabupaten Aceh Tamiang','157740','154321','312061'),
('11.17','Kabupaten Bener Meriah','91798','89578','181376'),
('11.18','Kabupaten Pidie Jaya','82520','83997','166517'),
('11.71','Kota Banda Aceh','132249','133061','265310'),
('11.72','Kota Sabang','21508','21285','42793'),
('11.73','Kota Lhokseumawe','98717','99988','198705'),
('11.74','Kota Langsa','92245','91669','183914'),
('11.75','Kota Subulussalam','54470','52715','107185'),
('12','Sumatera Utara','7828098','7812807','15640905'),
('12.01','Kabupaten Tapanuli Tengah','185732','183750','369482'),
('12.02','Kabupaten Tapanuli Utara','164338','167137','331475'),
('12.03','Kabupaten Tapanuli Selatan','163327','161238','324565'),
('12.04','Kabupaten Nias','72364','75625','147989'),
('12.05','Kabupaten Langkat','564266','556443','1120709'),
('12.06','Kabupaten Karo','207909','214586','422495'),
('12.07','Kabupaten Deli Serdang','1044298','1036110','2080408'),
('12.08','Kabupaten Simalungun','502885','501418','1004303'),
('12.09','Kabupaten Asahan','403875','395576','799451'),
('12.10','Kabupaten Labuhanbatu','259760','253250','513010'),
('12.11','Kabupaten Dairi','166297','167038','333335'),
('12.12','Kabupaten Toba','109370','111138','220508'),
('12.13','Kabupaten Mandailing Natal','250699','250919','501618'),
('12.14','Kabupaten Nias Selatan','185317','184500','369817'),
('12.15','Kabupaten Pakpak Bharat','28977','28303','57280'),
('12.16','Kabupaten Humbang Hasundutan','105199','105823','211022'),
('12.17','Kabupaten Samosir','74432','75443','149875'),
('12.18','Kabupaten Serdang Bedagai','349969','346876','696845'),
('12.19','Kabupaten Batu Bara','239864','234968','474832'),
('12.20','Kabupaten Padang Lawas Utara','138480','135106','273586'),
('12.21','Kabupaten Padang Lawas','136098','135176','271274'),
('12.22','Kabupaten Labuhanbatu Selatan','169625','162834','332459'),
('12.23','Kabupaten Labuhanbatu Utara','202100','197206','399306'),
('12.24','Kabupaten Nias Utara','76180','77158','153338'),
('12.25','Kabupaten Nias Barat','47472','50054','97526'),
('12.71','Kota Medan','1263569','1282883','2546452'),
('12.72','Kota Pematangsiantar','137836','141936','279772'),
('12.73','Kota Sibolga','50556','49726','100282'),
('12.74','Kota Tanjungbalai','95289','92650','187939'),
('12.75','Kota Binjai','157385','158224','315609'),
('12.76','Kota Tebing Tinggi','91715','92349','184064'),
('12.77','Kota Padangsidimpuan','115723','116259','231982'),
('12.78','Kota Gunungsitoli','67192','71105','138297'),
('13','Sumatera Barat','2923132','2897227','5820359'),
('13.01','Kabupaten Pesisir Selatan','267507','266279','533786'),
('13.02','Kabupaten Solok','207180','205895','413075'),
('13.03','Kabupaten Sijunjung','124672','122651','247323'),
('13.04','Kabupaten Tanah Datar','191513','191161','382674'),
('13.05','Kabupaten Padang Pariaman','230615','227695','458310'),
('13.06','Kabupaten Agam','267450','265804','533254'),
('13.07','Kabupaten Lima Puluh Kota','200980','201808','402788'),
('13.08','Kabupaten Pasaman','157676','156161','313837'),
('13.09','Kabupaten Kepulauan Mentawai','50832','47005','97837'),
('13.10','Kabupaten Dharmasraya','122982','119210','242192'),
('13.11','Kabupaten Solok Selatan','92075','89450','181525'),
('13.12','Kabupaten Pasaman Barat','228552','225501','454053'),
('13.71','Kota Padang','472670','474312','946982'),
('13.72','Kota Solok','42982','42571','85553'),
('13.73','Kota Sawahlunto','34500','34059','68559'),
('13.74','Kota Padang Panjang','32189','31706','63895'),
('13.75','Kota Bukittinggi','71136','70668','141804'),
('13.76','Kota Payakumbuh','75097','73980','149077'),
('13.77','Kota Pariaman','52524','51311','103835'),
('14','Riau','3625379','3473918','7099297'),
('14.01','Kabupaten Kampar','459172','439801','898973'),
('14.02','Kabupaten Indragiri Hulu','249632','237407','487039'),
('14.03','Kabupaten Bengkalis','348949','332935','681884'),
('14.04','Kabupaten Indragiri Hilir','367777','345517','713294'),
('14.05','Kabupaten  Pelalawan','229892','216547','446439'),
('14.06','Kabupaten  Rokan Hulu','298478','288516','586994'),
('14.07','Kabupaten  Rokan Hilir','352235','335838','688073'),
('14.08','Kabupaten Siak','254501','241259','495760'),
('14.09','Kabupaten Kuantan Singingi','186061','179928','365989'),
('14.10','Kabupaten Kepulauan Meranti','109205','102792','211997'),
('14.71','Kota Pekanbaru','586917','580682','1167599'),
('14.72','Kota Dumai','182560','172696','355256'),
('15','Jambi','1952872','1881567','3834439'),
('15.01','Kabupaten  Kerinci','135055','133896','268951'),
('15.02','Kabupaten  Merangin','202407','195054','397461'),
('15.03','Kabupaten Sarolangun','159668','153429','313097'),
('15.04','Kabupaten Batanghari','157571','149790','307361'),
('15.05','Kabupaten  Muaro Jambi','235136','222102','457238'),
('15.06','Kabupaten Tanjung Jabung Barat','173688','163290','336978'),
('15.07','Kabupaten Tanjung Jabung Timur','124922','119983','244905'),
('15.08','Kabupaten Bungo','193358','186669','380027'),
('15.09','Kabupaten Tebo','193428','183113','376541'),
('15.71','Kota Jambi','326268','323388','649656'),
('15.72','Kota Sungai Penuh','51371','50853','102224'),
('16','Sumatera Selatan','4616445','4448245','9064690'),
('16.01','Kabupaten Ogan Komering Ulu','199719','190329','390048'),
('16.02','Kabupaten Ogan Komering Ilir','411305','389754','801059'),
('16.03','Kabupaten Muara Enim','333560','320171','653731'),
('16.04','Kabupaten Lahat','229204','218937','448141'),
('16.05','Kabupaten Musi Rawas','218376','209347','427723'),
('16.06','Kabupaten Musi Banyuasin','378473','358424','736897'),
('16.07','Kabupaten Banyuasin','454414','433910','888324'),
('16.08','Kabupaten Ogan Komering Ulu Timur','355275','339557','694832'),
('16.09','Kabupaten Ogan Komering Ulu Selatan','211134','195804','406938'),
('16.10','Kabupaten Ogan Ilir','225871','220149','446020'),
('16.11','Kabupaten Empat Lawang','173894','162889','336783'),
('16.12','Kabupaten Penukal Abab Lematang Ilir','108942','105853','214795'),
('16.13','Kabupaten Musi Rawas Utara','103582','100106','203688'),
('16.71','Kota Palembang','901923','899444','1801367'),
('16.72','Kota Pagar Alam','78395','74876','153271'),
('16.73','Kota Lubuk Linggau','124846','122704','247550'),
('16.74','Kota Prabumulih','107532','105991','213523'),
('17','Bengkulu','1086715','1041242','2127957'),
('17.01','Kabupaten Bengkulu Selatan','90004','87133','177137'),
('17.02','Kabupaten Rejang Lebong','147374','141208','288582'),
('17.03','Kabupaten Bengkulu Utara','158297','151476','309773'),
('17.04','Kabupaten Kaur','70220','66313','136533'),
('17.05','Kabupaten Seluma','111327','104701','216028'),
('17.06','Kabupaten Mukomuko','105858','99744','205602'),
('17.07','Kabupaten Lebong','59153','56367','115520'),
('17.08','Kabupaten Kepahiang','78369','73541','151910'),
('17.09','Kabupaten Bengkulu Tengah','64656','61683','126339'),
('17.71','Kota Bengkulu','201457','199076','400533'),
('18','Lampung','4668816','4475447','9144263'),
('18.01','Kabupaten Lampung Selatan','571874','552809','1124683'),
('18.02','Kabupaten Lampung Tengah','711099','683296','1394395'),
('18.03','Kabupaten Lampung Utara','343702','331924','675626'),
('18.04','Kabupaten Lampung Barat','162740','151016','313756'),
('18.05','Kabupaten Tulang Bawang','227534','212506','440040'),
('18.06','Kabupaten Tanggamus','331215','309821','641036'),
('18.07','Kabupaten Lampung Timur','570974','551631','1122605'),
('18.08','Kabupaten Way Kanan','253309','241749','495058'),
('18.09','Kabupaten Pesawaran','257325','243722','501047'),
('18.10','Kabupaten Pringsewu','227681','217153','444834'),
('18.11','Kabupaten Mesuji','124285','116995','241280'),
('18.12','Kabupaten Tulang Bawang Barat','159586','152930','312516'),
('18.13','Kabupaten Pesisir Barat','91926','85504','177430'),
('18.71','Kota Bandar Lampung','543865','533799','1077664'),
('18.72','Kota Metro','91701','90592','182293'),
('19','Kepulauan Bangka Belitung','794861','754701','1549562'),
('19.01','Kabupaten Bangka','173294','164461','337755'),
('19.02','Kabupaten Belitung','97923','94482','192405'),
('19.03','Kabupaten Bangka Selatan','111561','104518','216079'),
('19.04','Kabupaten Bangka Tengah','108922','101762','210684'),
('19.05','Kabupaten Bangka Barat','112511','105348','217859'),
('19.06','Kabupaten Belitung Timur','68071','64424','132495'),
('19.71','Kota Pangkal Pinang','122579','119706','242285'),
('21','Kepulauan Riau','1155100','1116790','2271890'),
('21.01','Kabupaten Bintan','92601','87803','180404'),
('21.02','Kabupaten Karimun','139992','133954','273946'),
('21.03','Kabupaten Natuna','43041','41284','84325'),
('21.04','Kabupaten Lingga','52075','48964','101039'),
('21.05','Kabupaten Kepulauan Anambas','26187','24735','50922'),
('21.71','Kota Batam','680759','661279','1342038'),
('21.72','Kota Tanjung Pinang','120445','118771','239216'),
('31','Daerah Khusus Ibukota Jakarta','5537335','5500881','11038216'),
('31.01','Kabupaten Administrasi Kepulauan Seribu','15350','14984','30334'),
('31.71','Kota Administrasi Jakarta Pusat','530678','526592','1057270'),
('31.72','Kota Administrasi Jakarta Utara','921931','910101','1832032'),
('31.73','Kota Administrasi Jakarta Barat','1285376','1271376','2556752'),
('31.74','Kota Administrasi Jakarta Selatan','1163740','1167671','2331411'),
('31.75','Kota Administrasi Jakarta Timur','1620260','1610157','3230417'),
('32','Jawa Barat','25997794','25318584','51316378'),
('32.01','Kabupaten Bogor','2974061','2835729','5809790'),
('32.02','Kabupaten Sukabumi','1458705','1410238','2868943'),
('32.03','Kabupaten Cianjur','1350395','1288430','2638825'),
('32.04','Kabupaten Bandung','1953971','1885750','3839721'),
('32.05','Kabupaten Garut','1458601','1393276','2851877'),
('32.06','Kabupaten Tasikmalaya','1016563','979496','1996059'),
('32.07','Kabupaten Ciamis','651625','646158','1297783'),
('32.08','Kabupaten Kuningan','629774','610225','1239999'),
('32.09','Kabupaten Cirebon','1263071','1225975','2489046'),
('32.10','Kabupaten Majalengka','689351','680218','1369569'),
('32.11','Kabupaten Sumedang','619513','607147','1226660'),
('32.12','Kabupaten Indramayu','997493','982587','1980080'),
('32.13','Kabupaten Subang','838911','838717','1677628'),
('32.14','Kabupaten Purwakarta','539514','524418','1063932'),
('32.15','Kabupaten Karawang','1321284','1290781','2612065'),
('32.16','Kabupaten Bekasi','1711222','1676379','3387601'),
('32.17','Kabupaten Bandung Barat','974016','937645','1911661'),
('32.18','Kabupaten Pangandaran','224519','222753','447272'),
('32.71','Kota Bogor','578674','565434','1144108'),
('32.72','Kota Sukabumi','186135','183961','370096'),
('32.73','Kota Bandung','1298551','1293212','2591763'),
('32.74','Kota Cirebon','178969','177660','356629'),
('32.75','Kota Bekasi','1285093','1287116','2572209'),
('32.76','Kota Depok','1008092','1002820','2010912'),
('32.77','Kota Cimahi','292434','289560','581994'),
('32.78','Kota Tasikmalaya','391746','379093','770839'),
('32.79','Kota Banjar','105511','103806','209317'),
('33','Jawa Tengah','19340389','19090256','38430645'),
('33.01','Kabupaten Cilacap','1043157','1016591','2059748'),
('33.02','Kabupaten Banyumas','942169','926277','1868446'),
('33.03','Kabupaten Purbalingga','536866','520884','1057750'),
('33.04','Kabupaten Banjarnegara','545513','526464','1071977'),
('33.05','Kabupaten Kebumen','734040','712793','1446833'),
('33.06','Kabupaten Purworejo','406024','403627','809651'),
('33.07','Kabupaten Wonosobo','482688','463267','945955'),
('33.08','Kabupaten Magelang','677734','667928','1345662'),
('33.09','Kabupaten Boyolali','557020','557050','1114070'),
('33.10','Kabupaten Klaten','647853','654795','1302648'),
('33.11','Kabupaten Sukoharjo','458337','458135','916472'),
('33.12','Kabupaten Wonogiri','528932','528563','1057495'),
('33.13','Kabupaten Karanganyar','475381','478315','953696'),
('33.14','Kabupaten Sragen','510418','513120','1023538'),
('33.15','Kabupaten Grobogan','765275','755699','1520974'),
('33.16','Kabupaten Blora','464464','463497','927961'),
('33.17','Kabupaten Rembang','334294','331207','665501'),
('33.18','Kabupaten Pati','687717','698187','1385904'),
('33.19','Kabupaten Kudus','438626','439195','877821'),
('33.20','Kabupaten Jepara','645757','637930','1283687'),
('33.21','Kabupaten Demak','634249','619955','1254204'),
('33.22','Kabupaten Semarang','543176','545553','1088729'),
('33.23','Kabupaten Temanggung','414995','407885','822880'),
('33.24','Kabupaten Kendal','551157','543057','1094214'),
('33.25','Kabupaten Batang','431380','424498','855878'),
('33.26','Kabupaten Pekalongan','525912','508329','1034241'),
('33.27','Kabupaten Pemalang','814263','786744','1601007'),
('33.28','Kabupaten Tegal','888045','857233','1745278'),
('33.29','Kabupaten Brebes','1049539','1016887','2066426'),
('33.71','Kota Magelang','63612','65097','128709'),
('33.72','Kota Surakarta','290512','298730','589242'),
('33.73','Kota Salatiga','98571','100400','198971'),
('33.74','Kota Semarang','842737','859642','1702379'),
('33.75','Kota Pekalongan','161308','156913','318221'),
('33.76','Kota Tegal','148668','145809','294477'),
('34','Daerah Istimewa Yogyakarta','1853487','1889878','3743365'),
('34.01','Kabupaten Kulon Progo','220557','224437','444994'),
('34.02','Kabupaten Bantul','488162','492107','980269'),
('34.03','Kabupaten Gunungkidul','384854','392072','776926'),
('34.04','Kabupaten Sleman','557436','568135','1125571'),
('34.71','Kota Yogyakarta','202478','213127','415605'),
('35','Jawa Timur','20894359','21025547','41919906'),
('35.01','Kabupaten Pacitan','294808','293910','588718'),
('35.02','Kabupaten Ponorogo','486147','492861','979008'),
('35.03','Kabupaten Trenggalek','378680','375130','753810'),
('35.04','Kabupaten Tulungagung','570819','568405','1139224'),
('35.05','Kabupaten Blitar','633732','627967','1261699'),
('35.06','Kabupaten Kediri','853689','838287','1691976'),
('35.07','Kabupaten Malang','1376937','1357686','2734623'),
('35.08','Kabupaten Lumajang','554003','562228','1116231'),
('35.09','Kabupaten Jember','1306216','1309658','2615874'),
('35.10','Kabupaten Banyuwangi','894056','897733','1791789'),
('35.11','Kabupaten Bondowoso','385405','402602','788007'),
('35.12','Kabupaten Situbondo','338331','353304','691635'),
('35.13','Kabupaten Probolinggo','592531','608400','1200931'),
('35.14','Kabupaten Pasuruan','831105','834817','1665922'),
('35.15','Kabupaten Sidoarjo','1015862','1012012','2027874'),
('35.16','Kabupaten Mojokerto','581967','574177','1156144'),
('35.17','Kabupaten Jombang','694052','683953','1378005'),
('35.18','Kabupaten Nganjuk','579531','573044','1152575'),
('35.19','Kabupaten Madiun','365305','372570','737875'),
('35.20','Kabupaten Magetan','340213','352587','692800'),
('35.21','Kabupaten Ngawi','450288','456714','907002'),
('35.22','Kabupaten Bojonegoro','686426','679801','1366227'),
('35.23','Kabupaten Tuban','634173','632223','1266396'),
('35.24','Kabupaten Lamongan','684425','683078','1367503'),
('35.25','Kabupaten Gresik','667540','659957','1327497'),
('35.26','Kabupaten Bangkalan','513154','528877','1042031'),
('35.27','Kabupaten Sampang','512140','521786','1033926'),
('35.28','Kabupaten Pamekasan','442986','461979','904965'),
('35.29','Kabupaten Sumenep','552127','590875','1143002'),
('35.71','Kota Kediri','149683','151741','301424'),
('35.72','Kota Blitar','80376','80828','161204'),
('35.73','Kota Malang','442076','447283','889359'),
('35.74','Kota Probolinggo','121121','122625','243746'),
('35.75','Kota Pasuruan','106811','106658','213469'),
('35.76','Kota Mojokerto','70647','71625','142272'),
('35.77','Kota Madiun','99077','102656','201733'),
('35.78','Kota Surabaya','1494734','1523288','3018022'),
('35.79','Kota Batu','113186','112222','225408'),
('36','Banten','6543019','6338355','12881374'),
('36.01','Kabupaten Pandeglang','738650','698396','1437046'),
('36.02','Kabupaten Lebak','786683','744881','1531564'),
('36.03','Kabupaten Tangerang','1761008','1698698','3459706'),
('36.04','Kabupaten Serang','913981','877814','1791795'),
('36.71','Kota Tangerang','983453','973896','1957349'),
('36.72','Kota Cilegon','243024','237354','480378'),
('36.73','Kota Serang','387152','372777','759929'),
('36.74','Kota Tangerang Selatan','729068','734539','1463607'),
('51','Bali','2193831','2181432','4375263'),
('51.01','Kabupaten Jembrana','165808','164413','330221'),
('51.02','Kabupaten Tabanan','238355','240038','478393'),
('51.03','Kabupaten Badung','267478','270261','537739'),
('51.04','Kabupaten Gianyar','253020','251825','504845'),
('51.05','Kabupaten Klungkung','111918','111805','223723'),
('51.06','Kabupaten Bangli','131868','128658','260526'),
('51.07','Kabupaten Karangasem','272181','266209','538390'),
('51.08','Kabupaten Buleleng','417978','410178','828156'),
('51.71','Kota Denpasar','335225','338045','673270'),
('52','Nusa Tenggara Barat','2870780','2880515','5751295'),
('52.01','Kabupaten Lombok Barat','380974','381783','762757'),
('52.02','Kabupaten Lombok Tengah','560768','567948','1128716'),
('52.03','Kabupaten Lombok Timur','726950','730641','1457591'),
('52.04','Kabupaten Sumbawa','263993','263722','527715'),
('52.05','Kabupaten Dompu','139345','138492','277837'),
('52.06','Kabupaten Bima','272459','271123','543582'),
('52.07','Kabupaten Sumbawa Barat','78434','77625','156059'),
('52.08','Kabupaten Lombok Utara','135503','134486','269989'),
('52.71','Kota Mataram','230016','231920','461936'),
('52.72','Kota Bima','82338','82775','165113'),
('53','Nusa Tenggara Timur','2850092','2850680','5700772'),
('53.01','Kabupaten Kupang','198513','193026','391539'),
('53.02','Kab Timor Tengah Selatan','243758','246884','490642'),
('53.03','Kabupaten Timor Tengah Utara','137887','138145','276032'),
('53.04','Kabupaten Belu','116041','116747','232788'),
('53.05','Kabupaten Alor','113431','116299','229730'),
('53.06','Kabupaten Flores Timur','142457','147424','289881'),
('53.07','Kabupaten Sikka','168616','177998','346614'),
('53.08','Kabupaten Ende','138303','145503','283806'),
('53.09','Kabupaten Ngada','83666','85779','169445'),
('53.10','Kabupaten Manggarai','175131','174705','349836'),
('53.11','Kabupaten Sumba Timur','142097','135193','277290'),
('53.12','Kabupaten Sumba Barat','72474','69286','141760'),
('53.13','Kabupaten Lembata','72994','76927','149921'),
('53.14','Kabupaten Rote Ndao','77526','76501','154027'),
('53.15','Kabupaten Manggarai Barat','146223','144465','290688'),
('53.16','Kabupaten Nagekeo','81399','83058','164457'),
('53.17','Kabupaten Sumba Tengah','47189','44949','92138'),
('53.18','Kabupaten Sumba Barat Daya','181632','173390','355022'),
('53.19','Kabupaten Manggarai Timur','154948','150320','305268'),
('53.20','Kabupaten Sabu Raijua','48349','46511','94860'),
('53.21','Kabupaten Malaka','104472','106436','210908'),
('53.71','Kota Kupang','202986','201134','404120'),
('61','Kalimantan Barat','2902699','2743569','5646268'),
('61.01','Kabupaten Sambas','333805','317441','651246'),
('61.02','Kabupaten Mempawah','160730','153418','314148'),
('61.03','Kabupaten Sanggau','259316','239758','499074'),
('61.04','Kabupaten Ketapang','302644','280897','583541'),
('61.05','Kabupaten Sintang','236285','220919','457204'),
('61.06','Kabupaten Kapuas Hulu','142348','134640','276988'),
('61.07','Kabupaten Bengkayang','153717','142249','295966'),
('61.08','Kabupaten Landak','218633','196400','415033'),
('61.09','Kabupaten Sekadau','116971','108523','225494'),
('61.10','Kabupaten Melawi','112311','105289','217600'),
('61.11','Kabupaten Kayong Utara','65223','61675','126898'),
('61.12','Kabupaten Kubu Raya','329708','316383','646091'),
('61.71','Kota Pontianak','343257','343774','687031'),
('61.72','Kota Singkawang','127751','122203','249954'),
('62','Kalimantan Tengah','1459913','1365377','2825290'),
('62.01','Kabupaten Kotawaringin Barat','151332','143270','294602'),
('62.02','Kabupaten Kotawaringin Timur','235087','219428','454515'),
('62.03','Kabupaten Kapuas','215852','202002','417854'),
('62.04','Kabupaten Barito Selatan','70622','67292','137914'),
('62.05','Kabupaten Barito Utara','83033','76815','159848'),
('62.06','Kabupaten Katingan','94401','87562','181963'),
('62.07','Kabupaten Seruyan','83322','76527','159849'),
('62.08','Kabupaten Sukamara','35146','32088','67234'),
('62.09','Kabupaten Lamandau','60380','53877','114257'),
('62.10','Kabupaten Gunung Mas','70582','63900','134482'),
('62.11','Kabupaten Pulang Pisau','74870','69793','144663'),
('62.12','Kabupaten Murung Raya','64673','59618','124291'),
('62.13','Kabupaten Barito Timur','60804','57861','118665'),
('62.71','Kota Palangkaraya','159809','155344','315153'),
('63','Kalimantan Selatan','2176733','2128548','4305281'),
('63.01','Kabupaten Tanah Laut','187908','181910','369818'),
('63.02','Kabupaten Kotabaru','171691','161096','332787'),
('63.03','Kabupaten Banjar','298174','292219','590393'),
('63.04','Kabupaten Barito Kuala','167086','162713','329799'),
('63.05','Kabupaten Tapin','101669','100392','202061'),
('63.06','Kabupaten Hulu Sungai Selatan','120554','118917','239471'),
('63.07','Kabupaten Hulu Sungai Tengah','136840','134665','271505'),
('63.08','Kabupaten Hulu Sungai Utara','119428','118822','238250'),
('63.09','Kabupaten Tabalong','135361','132426','267787'),
('63.10','Kabupaten Tanah Bumbu','183226','173995','357221'),
('63.11','Kabupaten Balangan','70475','68475','138950'),
('63.71','Kota Banjarmasin','341066','340627','681693'),
('63.72','Kota Banjarbaru','143255','142291','285546'),
('64','Kalimantan Timur','2138665','1984638','4123303'),
('64.01','Kabupaten Paser','160276','149391','309667'),
('64.02','Kabupaten Kutai Kartanegara','420744','386220','806964'),
('64.03','Kabupaten Berau','158927','140078','299005'),
('64.07','Kabupaten Kutai Barat','98069','88512','186581'),
('64.08','Kabupaten Kutai Timur','240268','208582','448850'),
('64.09','Kabupaten Penajam Paser Utara','104765','97302','202067'),
('64.11','Kabupaten Mahakam Ulu','21192','18523','39715'),
('64.71','Kota Balikpapan','387112','370306','757418'),
('64.72','Kota Samarinda','448161','433064','881225'),
('64.74','Kota Bontang','99151','92660','191811'),
('65','Kalimantan Utara','403628','366999','770627'),
('65.01','Kabupaten Bulungan','89856','80383','170239'),
('65.02','Kabupaten Malinau','46163','41419','87582'),
('65.03','Kabupaten Nunukan','119672','107788','227460'),
('65.04','Kabupaten Tana Tidung','15762','14274','30036'),
('65.71','Kota Tarakan','132175','123135','255310'),
('71','Sulawesi Utara','1351734','1293557','2645291'),
('71.01','Kabupaten Bolaang Mongondow','133940','123438','257378'),
('71.02','Kabupaten Minahasa','168503','162340','330843'),
('71.03','Kabupaten Kepulauan Sangihe','69187','66673','135860'),
('71.04','Kabupaten Kepulauan Talaud','51713','48663','100376'),
('71.05','Kabupaten Minahasa Selatan','125878','117419','243297'),
('71.06','Kabupaten Minahasa Utara','116001','113230','229231'),
('71.07','Kabupaten Minahasa Tenggara','62974','58580','121554'),
('71.08','Kabupaten Bolaang Mongondow Utara','45175','42827','88002'),
('71.09','Kabupaten Kep. Siau Tagulandang Biaro','35553','34975','70528'),
('71.10','Kabupaten Bolaang Mongondow Timur','47657','43687','91344'),
('71.11','Kabupaten Bolaang Mongondow Selatan','39368','36737','76105'),
('71.71','Kota Manado','230999','228410','459409'),
('71.72','Kota Bitung','109932','106094','216026'),
('71.73','Kota Tomohon','52524','51288','103812'),
('71.74','Kota Kotamobagu','62330','59196','121526'),
('72','Sulawesi Tengah','1652891','1566603','3219494'),
('72.01','Kabupaten Banggai','191889','185915','377804'),
('72.02','Kabupaten Poso','130653','122697','253350'),
('72.03','Kabupaten Donggala','163846','154767','318613'),
('72.04','Kabupaten Toli-Toli','124099','118684','242783'),
('72.05','Kabupaten Buol','83257','79016','162273'),
('72.06','Kabupaten Morowali','106590','92378','198968'),
('72.07','Kabupaten Banggai Kepulauan','65973','64035','130008'),
('72.08','Kabupaten Parigi Moutong','235958','225618','461576'),
('72.09','Kabupaten Tojo Una Una','89092','84442','173534'),
('72.10','Kabupaten Sigi','140141','133319','273460'),
('72.11','Kabupaten Banggai Laut','39279','38551','77830'),
('72.12','Kabupaten Morowali Utara','83888','70116','154004'),
('72.71','Kota Palu','198226','197065','395291'),
('73','Sulawesi Selatan','4729375','4798901','9528276'),
('73.01','Kabupaten Kepulauan Selayar','70405','72691','143096'),
('73.02','Kabupaten Bulukumba','235138','245534','480672'),
('73.03','Kabupaten Bantaeng','107585','109682','217267'),
('73.04','Kabupaten Jeneponto','208893','214112','423005'),
('73.05','Kabupaten Takalar','162897','169966','332863'),
('73.06','Kabupaten Gowa','408960','416409','825369'),
('73.07','Kabupaten Sinjai','136939','139330','276269'),
('73.08','Kabupaten Bone','404150','423222','827372'),
('73.09','Kabupaten Maros','206548','207858','414406'),
('73.10','Kabupaten Pangkajene dan Kepulauan','177856','183935','361791'),
('73.11','Kabupaten Barru','96277','99724','196001'),
('73.12','Kabupaten Soppeng','116800','124533','241333'),
('73.13','Kabupaten Wajo','195841','204858','400699'),
('73.14','Kabupaten Sidenreng Rappang','164571','167989','332560'),
('73.15','Kabupaten Pinrang','208789','214357','423146'),
('73.16','Kabupaten Enrekang','119217','115253','234470'),
('73.17','Kabupaten Luwu','194269','191092','385361'),
('73.18','Kabupaten Tana Toraja','133053','125881','258934'),
('73.22','Kabupaten Luwu Utara','169309','165852','335161'),
('73.24','Kabupaten Luwu Timur','166934','156488','323422'),
('73.26','Kabupaten Toraja Utara','135991','130522','266513'),
('73.71','Kota Makassar','736111','746243','1482354'),
('73.72','Kota Parepare','81252','82062','163314'),
('73.73','Kota Palopo','91590','91308','182898'),
('74','Sulawesi Tenggara','1427378','1397211','2824589'),
('74.01','Kabupaten Kolaka','132053','125680','257733'),
('74.02','Kabupaten Konawe','138997','130131','269128'),
('74.03','Kabupaten Muna','115311','118159','233470'),
('74.04','Kabupaten Buton','60874','61150','122024'),
('74.05','Kabupaten Konawe Selatan','170499','161890','332389'),
('74.06','Kabupaten Bombana','84868','83187','168055'),
('74.07','Kabupaten Wakatobi','59167','59626','118793'),
('74.08','Kabupaten Kolaka Utara','73626','69978','143604'),
('74.09','Kabupaten Konawe Utara','42935','40514','83449'),
('74.10','Kabupaten Buton Utara','38045','36673','74718'),
('74.11','Kabupaten Kolaka Timur','67438','63253','130691'),
('74.12','Kabupaten Konawe Kepulauan','22328','21763','44091'),
('74.13','Kabupaten Muna Barat','45338','46233','91571'),
('74.14','Kabupaten Buton Tengah','59704','62041','121745'),
('74.15','Kabupaten Buton Selatan','50888','51993','102881'),
('74.71','Kota Kendari','184274','182180','366454'),
('74.72','Kota Bau Bau','81033','82760','163793'),
('75','Gorontalo','631495','619465','1250960'),
('75.01','Kabupaten Gorontalo','213513','210468','423981'),
('75.02','Kabupaten Boalemo','77920','74931','152851'),
('75.03','Kabupaten Bone Bolango','88055','86733','174788'),
('75.04','Kabupaten Pohuwato','83050','79939','162989'),
('75.05','Kabupaten Gorontalo Utara','67267','64724','131991'),
('75.71','Kota Gorontalo','101690','102670','204360'),
('76','Sulawesi Barat','744533','722208','1466741'),
('76.01','Kabupaten Pasangkayu','94161','89215','183376'),
('76.02','Kabupaten Mamuju','147755','140627','288382'),
('76.03','Kabupaten Mamasa','86407','81204','167611'),
('76.04','Kabupaten Polewali Mandar','246600','245739','492339'),
('76.05','Kabupaten Majene','95207','95016','190223'),
('76.06','Kabupaten Mamuju Tengah','74403','70407','144810'),
('81','Maluku','969012','966574','1935586'),
('81.01','Kabupaten Maluku Tengah','215458','216903','432361'),
('81.02','Kabupaten Maluku Tenggara','63992','65177','129169'),
('81.03','Kabupaten Kepulauan Tanimbar','66317','66020','132337'),
('81.04','Kabupaten Buru','71951','70396','142347'),
('81.05','Kabupaten Seram Bagian Timur','71186','69786','140972'),
('81.06','Kabupaten Seram Bagian Barat','109295','108094','217389'),
('81.07','Kabupaten Kepulauan Aru','57767','54764','112531'),
('81.08','Kabupaten Maluku Barat Daya','49454','47566','97020'),
('81.09','Kabupaten Buru Selatan','40969','40360','81329'),
('81.71','Kota Ambon','177887','181774','359661'),
('81.72','Kota Tual','44736','45734','90470'),
('82','Maluku Utara','729780','664451','1394231'),
('82.01','Kabupaten Halmahera Barat','71695','68069','139764'),
('82.02','Kabupaten Halmahera Tengah','67500','39000','106500'),
('82.03','Kabupaten Halmahera Utara','105804','100429','206233'),
('82.04','Kabupaten Halmahera Selatan','133453','125111','258564'),
('82.05','Kabupaten Kepulauan Sula','50397','50238','100635'),
('82.06','Kabupaten Halmahera Timur','51953','48520','100473'),
('82.07','Kabupaten Pulau Morotai','42749','40164','82913'),
('82.08','Kabupaten Pulau Taliabu','33930','32431','66361'),
('82.71','Kota Ternate','109668','101168','210836'),
('82.72','Kota Tidore Kepulauan','62631','59321','121952'),
('91','Papua','574173','528187','1102360'),
('91.03','Kabupaten Jayapura','105242','98530','203772'),
('91.05','Kabupaten Kepulauan Yapen','59587','56627','116214'),
('91.06','Kabupaten Biak Numfor','76234','74084','150318'),
('91.10','Kabupaten Sarmi','23842','21242','45084'),
('91.11','Kabupaten Keerom','39105','35227','74332'),
('91.15','Kabupaten Waropen','20370','18470','38840'),
('91.19','Kabupaten Supiori','14454','13511','27965'),
('91.20','Kabupaten Mamberamo Raya','21313','19723','41036'),
('91.71','Kota Jayapura','214026','190773','404799'),
('92','Papua Barat','297017','279238','576255'),
('92.02','Kabupaten Manokwari','105983','99229','205212'),
('92.03','Kabupaten Fak Fak','48104','45893','93997'),
('92.06','Kabupaten Teluk Bintuni','44299','39906','84205'),
('92.07','Kabupaten Teluk Wondama','24806','22688','47494'),
('92.08','Kabupaten Kaimana','33311','31627','64938'),
('92.11','Kabupaten Manokwari Selatan','19863','19163','39026'),
('92.12','Kabupaten Pegunungan Arfak','20651','20732','41383'),
('93','Papua Selatan','292552','269668','562220'),
('93.01','Kabupaten Merauke','132601','122567','255168'),
('93.02','Kabupaten Boven Digoel','38130','33867','71997'),
('93.03','Kabupaten Mappi','58942','55211','114153'),
('93.04','Kabupaten Asmat','62879','58023','120902'),
('94','Papua Tengah','723511','645601','1369112'),
('94.01','Kabupaten Nabire','93211','85963','179174'),
('94.02','Kabupaten Puncak Jaya','117954','102439','220393'),
('94.03','Kabupaten Paniai','69021','56742','125763'),
('94.04','Kabupaten Mimika','167756','150923','318679'),
('94.05','Kabupaten Puncak','93973','84099','178072'),
('94.06','Kabupaten Dogiyai','60839','55867','116706'),
('94.07','Kabupaten Intan Jaya','71863','65833','137696'),
('94.08','Kabupaten Deiyai','48894','43735','92629'),
('95','Papua Pegunungan','786734','683784','1470518'),
('95.01','Kabupaten Jayawijaya','142968','133320','276288'),
('95.02','Kab Pegunungan Bintang','61112','53469','114581'),
('95.03','Kabupaten Yahukimo','192939','163082','356021'),
('95.04','Kabupaten Tolikara','136195','115836','252031'),
('95.05','Kabupaten Mamberamo Tengah','26375','23753','50128'),
('95.06','Kabupaten Yalimo','55886','49488','105374'),
('95.07','Kabupaten Lanny Jaya','110979','92943','203922'),
('95.08','Kabupaten Nduga','60280','51893','112173'),
('96','Papua Barat Oaya','324005','299181','623186'),
('96.01','Kabupaten Sorong','67118','62551','129669'),
('96.02','Kabupaten Sorong Selatan','29200','27779','56979'),
('96.03','Kabupaten Raja Ampat','37942','34923','72865'),
('96.04','Kabupaten Tambrauw','16122','14919','31041'),
('96.05','Kabupaten Maybrat','23387','23217','46604'),
('96.71','Kota Sorong','150236','135792','286028');