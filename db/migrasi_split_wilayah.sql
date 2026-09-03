insert into provinsi (id, kode, nama)
select kode as id, kode, nama from wilayah_2020 where length(kode) = 2;

insert into kota_kabupaten (id, id_provinsi, kode, nama)
select kode as id, substring(kode, 1, 2) as id_provinsi, kode, nama from wilayah_2020 where length(kode) = 5;

insert into kecamatan (id, id_kota_kabupaten, kode, nama)
select kode as id, substring(kode, 1, 5) as id_kota_kabupaten, kode, nama from wilayah_2020 where length(kode) = 8;

insert into kelurahan (id, id_kecamatan, kode, nama)
select kode as id, substring(kode, 1, 8) as id_kecamatan, kode, nama from wilayah_2020 where length(kode) = 13;

