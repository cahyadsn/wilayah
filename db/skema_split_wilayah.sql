create table provinsi (
    id varchar(36),
    kode varchar(50) not null,
    nama varchar(255) not null,
    primary key (id),
    unique (kode)
);

create table kota_kabupaten (
    id varchar(36),
    id_provinsi varchar(36) not null,
    kode varchar(50) not null,
    nama varchar(255) not null,
    primary key (id),
    unique (kode),
    foreign key (id_provinsi) references provinsi(id)
);

create table kecamatan (
    id varchar(36),
    id_kota_kabupaten varchar(36) not null,
    kode varchar(50) not null,
    nama varchar(255) not null,
    primary key (id),
    unique (kode),
    foreign key (id_kota_kabupaten) references kota_kabupaten(id)
);

create table kelurahan (
    id varchar(36),
    id_kecamatan varchar(36) not null,
    kode varchar(50) not null,
    nama varchar(255) not null,
    primary key (id),
    unique (kode),
    foreign key (id_kecamatan) references kecamatan(id)
);
