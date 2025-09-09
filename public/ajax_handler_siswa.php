<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['user_role'] != 'siswa') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'self_attendance':
        $status = $_POST['status'] ?? '';
        $message = isset($_POST['message']) ? $_POST['message'] : null;
        $selfie_path = null;

        if (empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Status absensi harus dipilih']);
            exit();
        }

        // Handle selfie data for Hadir status
        if ($status === 'Hadir' && isset($_POST['selfie_data']) && !empty($_POST['selfie_data'])) {
            $selfie_data = $_POST['selfie_data'];
            // Remove the data URL prefix (data:image/jpeg;base64,)
            $selfie_data = str_replace('data:image/jpeg;base64,', '', $selfie_data);
            $selfie_data = str_replace(' ', '+', $selfie_data);
            $selfie_data = base64_decode($selfie_data);

            // Create uploads directory if it doesn't exist
            $upload_dir = '../assets/uploads/selfies/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate unique filename
            $filename = 'selfie_' . $user_id . '_' . date('Ymd_His') . '.jpg';
            $file_path = $upload_dir . $filename;

            // Save the image
            if (file_put_contents($file_path, $selfie_data)) {
                $selfie_path = 'assets/uploads/selfies/' . $filename;
            }
        }

        if (mark_attendance($user_id, $status, $selfie_path, $message)) {
            echo json_encode(['success' => true, 'message' => 'Absensi berhasil dicatat']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mencatat absensi']);
        }
        break;

    case 'update_profile':
        $nama = $_POST['nama'] ?? '';
        $email = $_POST['email'] ?? '';
        $nomor_siswa = isset($_POST['nomor_siswa']) ? $_POST['nomor_siswa'] : null;
        $alamat = isset($_POST['alamat']) ? $_POST['alamat'] : null;
        $profile_photo_path = null;

        if (empty($nama) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Nama dan email harus diisi']);
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

        if (update_user_profile($user_id, $nama, $email, $nomor_siswa, $alamat, $profile_photo_path)) {
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
