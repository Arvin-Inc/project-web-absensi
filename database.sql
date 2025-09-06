-- Database: db_9009
USE db_9009;

-- Tabel kelas
CREATE TABLE kelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL
);

-- Tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('guru', 'siswa') NOT NULL,
    kelas_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_kelas FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE SET NULL
);

-- Tabel absensi
CREATE TABLE absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tanggal DATE NOT NULL,
    status ENUM('Hadir', 'Izin', 'Sakit', 'Alpha') NOT NULL,
    selfie VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel kode_absen
CREATE TABLE kode_absen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL UNIQUE,
    kode VARCHAR(10) NOT NULL,
    guru_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_guru FOREIGN KEY (guru_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO kelas (nama) VALUES ('Kelas A'), ('Kelas B');

INSERT INTO users (nama, email, password, role, kelas_id) VALUES
('Guru 1', 'guru@example.com', '$2y$10$examplehashedpassword', 'guru', NULL),
('Siswa 1', 'siswa1@example.com', '$2y$10$examplehashedpassword', 'siswa', 1),
('Siswa 2', 'siswa2@example.com', '$2y$10$examplehashedpassword', 'siswa', 2);
