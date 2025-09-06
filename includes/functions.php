<?php
require_once '../config/db.php';

function get_students() {
    global $conn;
    $stmt = $conn->prepare("SELECT u.id, u.nama, u.email, k.nama as kelas FROM users u LEFT JOIN kelas k ON u.kelas_id = k.id WHERE u.role = 'siswa' ORDER BY u.nama");
    $stmt->execute();
    $result = $stmt->get_result();
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
    return $students;
}

function get_attendance_today() {
    global $conn;
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT a.*, u.nama FROM absensi a JOIN users u ON a.user_id = u.id WHERE a.tanggal = ? ORDER BY u.nama");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance = [];
    while ($row = $result->fetch_assoc()) {
        $attendance[] = $row;
    }
    $stmt->close();
    return $attendance;
}

function get_attendance_report($user_id = null, $start_date = null, $end_date = null, $kelas_id = null) {
    global $conn;
    $query = "SELECT a.*, u.nama, k.nama as kelas FROM absensi a JOIN users u ON a.user_id = u.id LEFT JOIN kelas k ON u.kelas_id = k.id WHERE 1=1";
    $params = [];
    $types = "";

    if ($user_id) {
        $query .= " AND a.user_id = ?";
        $params[] = $user_id;
        $types .= "i";
    }

    if ($start_date) {
        $query .= " AND a.tanggal >= ?";
        $params[] = $start_date;
        $types .= "s";
    }

    if ($end_date) {
        $query .= " AND a.tanggal <= ?";
        $params[] = $end_date;
        $types .= "s";
    }

    if ($kelas_id) {
        $query .= " AND u.kelas_id = ?";
        $params[] = $kelas_id;
        $types .= "i";
    }

    $query .= " ORDER BY a.tanggal DESC, u.nama";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    $stmt->close();
    return $reports;
}

function get_classes() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM kelas ORDER BY nama");
    $stmt->execute();
    $result = $stmt->get_result();
    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    $stmt->close();
    return $classes;
}



function get_user_profile($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, nama, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

function update_user_profile($user_id, $nama, $email) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nama, $email, $user_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function generate_code($guru_id) {
    global $conn;
    $today = date('Y-m-d');
    $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6)); // Random 6 char code

    // Check if code already exists for today
    $stmt = $conn->prepare("SELECT id FROM kode_absen WHERE tanggal = ?");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        // Update existing
        $stmt = $conn->prepare("UPDATE kode_absen SET kode = ?, guru_id = ? WHERE tanggal = ?");
        $stmt->bind_param("sis", $code, $guru_id, $today);
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO kode_absen (tanggal, kode, guru_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $today, $code, $guru_id);
    }
    $result = $stmt->execute();
    $stmt->close();
    return $result ? $code : false;
}

function get_code_today() {
    global $conn;
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT kode FROM kode_absen WHERE tanggal = ?");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['kode'] : null;
}

function verify_code($input_code) {
    $today_code = get_code_today();
    return $today_code && strtoupper($input_code) === $today_code;
}

function mark_attendance($user_id, $status, $selfie_path = null) {
    global $conn;
    $today = date('Y-m-d');

    // If status is Hadir, require selfie
    if ($status === 'Hadir' && !$selfie_path) {
        return false; // Selfie required for Hadir
    }

    // Check if attendance already exists for today
    $stmt = $conn->prepare("SELECT id FROM absensi WHERE user_id = ? AND tanggal = ?");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        // Update existing
        $stmt = $conn->prepare("UPDATE absensi SET status = ?, selfie = ? WHERE user_id = ? AND tanggal = ?");
        $stmt->bind_param("ssis", $status, $selfie_path, $user_id, $today);
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO absensi (user_id, tanggal, status, selfie) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $today, $status, $selfie_path);
    }
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function validate_image($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        return 'Tipe file tidak didukung. Hanya JPG, PNG, dan GIF yang diperbolehkan.';
    }

    if ($file['size'] > $max_size) {
        return 'Ukuran file terlalu besar. Maksimal 5MB.';
    }

    return true;
}

function upload_selfie($file, $user_id) {
    $upload_dir = '../assets/uploads/selfies/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'selfie_' . $user_id . '_' . date('Ymd_His') . '.' . $file_extension;
    $target_path = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return 'assets/uploads/selfies/' . $filename;
    }

    return false;
}
?>
