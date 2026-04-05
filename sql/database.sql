-- Database: parkir
-- Create database
CREATE DATABASE IF NOT EXISTS parkir CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE parkir;

-- Table: tb_user
CREATE TABLE IF NOT EXISTS tb_user (
    id_user INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    role ENUM('admin', 'petugas', 'owner') NOT NULL,
    status_aktif TINYINT(1) DEFAULT 1
);

-- Table: tb_tarif
CREATE TABLE IF NOT EXISTS tb_tarif (
    id_tarif INT(11) AUTO_INCREMENT PRIMARY KEY,
    jenis_kendaraan ENUM('motor', 'mobil', 'lainnya') NOT NULL,
    tarif_per_jam DECIMAL(10,0) NOT NULL
);

-- Table: tb_area_parkir
CREATE TABLE IF NOT EXISTS tb_area_parkir (
    id_area INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_area VARCHAR(50) NOT NULL,
    kapasitas INT(5) NOT NULL,
    terisi INT(5) DEFAULT 0
);

-- Table: tb_kendaraan
CREATE TABLE IF NOT EXISTS tb_kendaraan (
    id_kendaraan INT(11) AUTO_INCREMENT PRIMARY KEY,
    plat_nomor VARCHAR(15) NOT NULL UNIQUE,
    jenis_kendaraan VARCHAR(20) NOT NULL,
    warna VARCHAR(20),
    pemilik VARCHAR(100),
    id_user INT(11),
    FOREIGN KEY (id_user) REFERENCES tb_user(id_user) ON DELETE SET NULL
);

-- Table: tb_transaksi
CREATE TABLE IF NOT EXISTS tb_transaksi (
    id_parkir INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_kendaraan INT(11) NOT NULL,
    waktu_masuk DATETIME NOT NULL,
    waktu_keluar DATETIME,
    id_tarif INT(11),
    durasi_jam INT(5),
    biaya_total DECIMAL(10,0),
    status ENUM('masuk', 'keluar') DEFAULT 'masuk',
    id_user INT(11),
    id_area INT(11),
    FOREIGN KEY (id_kendaraan) REFERENCES tb_kendaraan(id_kendaraan) ON DELETE CASCADE,
    FOREIGN KEY (id_tarif) REFERENCES tb_tarif(id_tarif) ON DELETE SET NULL,
    FOREIGN KEY (id_user) REFERENCES tb_user(id_user) ON DELETE SET NULL,
    FOREIGN KEY (id_area) REFERENCES tb_area_parkir(id_area) ON DELETE SET NULL
);

-- Table: tb_log_aktivitas
CREATE TABLE IF NOT EXISTS tb_log_aktivitas (
    id_log INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_user INT(11),
    aktivitas VARCHAR(100) NOT NULL,
    waktu_aktivitas DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES tb_user(id_user) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif) VALUES 
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Insert default petugas user (password: petugas123)
INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif) VALUES 
('Petugas 1', 'petugas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas', 1);

-- Insert default owner user (password: owner123)
INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif) VALUES 
('Owner 1', 'owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 1);

-- Insert sample tarif
INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam) VALUES 
('motor', 2000),
('mobil', 5000),
('lainnya', 3000);

-- Insert sample area parkir
INSERT INTO tb_area_parkir (nama_area, kapasitas, terisi) VALUES 
('Area A - Motor', 50, 0),
('Area B - Mobil', 30, 0),
('Area C - Campuran', 20, 0);
