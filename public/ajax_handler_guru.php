<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['user_role'] != 'guru') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add_class':
        $class_name = trim($_POST['class_name'] ?? '');
        if (empty($class_name)) {
            echo json_encode(['success' => false, 'message' => 'Nama kelas tidak boleh kosong']);
            exit();
        }

        if (add_class($class_name)) {
            echo json_encode(['success' => true, 'message' => 'Kelas berhasil ditambahkan']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan kelas']);
        }
        break;

    case 'mark_attendance':
        $user_id = $_POST['user_id'] ?? '';
        $status = $_POST['status'] ?? '';

        if (empty($user_id) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            exit();
        }

        if (mark_attendance($user_id, $status)) {
            echo json_encode(['success' => true, 'message' => 'Absensi berhasil dicatat']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mencatat absensi']);
        }
        break;

    case 'bulk_mark_attendance':
        $attendance_data = $_POST['attendance'] ?? [];

        if (empty($attendance_data)) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada data absensi yang dipilih']);
            exit();
        }

        $success_count = 0;
        $error_count = 0;

        foreach ($attendance_data as $user_id => $status) {
            if (!empty($status)) {
                if (mark_attendance($user_id, $status)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }

        if ($success_count > 0) {
            $message = "Absensi berhasil dicatat untuk {$success_count} siswa";
            if ($error_count > 0) {
                $message .= ", {$error_count} gagal";
            }
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mencatat absensi']);
        }
        break;

    case 'generate_code':
        $guru_id = $_SESSION['user_id'];
        $code = generate_code($guru_id);
        if ($code) {
            echo json_encode(['success' => true, 'message' => 'Kode berhasil dihasilkan', 'code' => $code]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghasilkan kode']);
        }
        break;

    case 'update_teacher_profile':
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mata_pelajaran = trim($_POST['mata_pelajaran'] ?? '');
        $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
        $alamat_guru = trim($_POST['alamat_guru'] ?? '');

        if (empty($nama) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Nama dan email harus diisi']);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
            exit();
        }

        $user_id = $_SESSION['user_id'];
        if (update_teacher_profile($user_id, $nama, $email, $mata_pelajaran, $nomor_telepon, $alamat_guru)) {
            $_SESSION['user_name'] = $nama;
            echo json_encode(['success' => true, 'message' => 'Profil berhasil diperbarui', 'user_name' => $nama]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui profil']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
        break;
}
?>
