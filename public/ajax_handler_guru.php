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

    case 'generate_code':
        $guru_id = $_SESSION['user_id'];
        $code = generate_code($guru_id);
        if ($code) {
            echo json_encode(['success' => true, 'message' => 'Kode berhasil dihasilkan', 'code' => $code]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghasilkan kode']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
        break;
}
?>
