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
        $profile_photo_path = null;

        if (empty($nama) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Nama dan email harus diisi']);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
            exit();
        }

        // Handle profile photo upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_photo'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB

            // Validate file type
            if (!in_array($file['type'], $allowed_types)) {
                echo json_encode(['success' => false, 'message' => 'Tipe file tidak didukung. Gunakan JPG, PNG, atau GIF.']);
                exit();
            }

            // Validate file size
            if ($file['size'] > $max_size) {
                echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB.']);
                exit();
            }

            // Create uploads directory if it doesn't exist
            $upload_dir = '../assets/uploads/profile_photos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate unique filename
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $profile_photo_path = 'assets/uploads/profile_photos/' . $filename;
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mengupload foto profil']);
                exit();
            }
        }

        $user_id = $_SESSION['user_id'];
        if (update_teacher_profile($user_id, $nama, $email, $mata_pelajaran, $nomor_telepon, $alamat_guru, $profile_photo_path)) {
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
