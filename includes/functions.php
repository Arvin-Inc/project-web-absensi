<?php
require_once '../config/db.php';

function get_students($kelas_id = null) {
    global $conn;
    $query = "SELECT u.id, u.nama, u.email, u.nomor_siswa, u.alamat, k.nama as kelas FROM users u LEFT JOIN kelas k ON u.kelas_id = k.id WHERE u.role = 'siswa'";
    $params = [];
    $types = "";

    if ($kelas_id) {
        $query .= " AND u.kelas_id = ?";
        $params[] = $kelas_id;
        $types .= "i";
    }

    $query .= " ORDER BY u.nama";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
    return $students;
}

function get_attendance_today($kelas_id = null) {
    global $conn;
    $today = date('Y-m-d');
    $query = "SELECT a.*, u.nama FROM absensi a JOIN users u ON a.user_id = u.id WHERE a.tanggal = ?";
    $params = [$today];
    $types = "s";

    if ($kelas_id) {
        $query .= " AND u.kelas_id = ?";
        $params[] = $kelas_id;
        $types .= "i";
    }

    $query .= " ORDER BY u.nama";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
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

function get_izin_messages($kelas_id = null) {
    global $conn;
    $query = "SELECT u.nama, a.tanggal, a.message, a.selfie
              FROM absensi a
              JOIN users u ON a.user_id = u.id
              WHERE a.status = 'Izin'";

    $params = [];
    $types = "";

    if ($kelas_id) {
        $query .= " AND u.kelas_id = ?";
        $params[] = $kelas_id;
        $types .= "i";
    }

    $query .= " ORDER BY a.tanggal DESC";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();
    return $messages;
}

function get_today_messages() {
    global $conn;
    $today = date('Y-m-d');
    $query = "SELECT a.*, u.nama, u.nomor_siswa, u.alamat
              FROM absensi a
              JOIN users u ON a.user_id = u.id
              WHERE a.status IN ('Izin', 'Sakit') AND a.tanggal = ?
              ORDER BY a.tanggal DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();
    return $messages;
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

function add_class($nama) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO kelas (nama) VALUES (?)");
    $stmt->bind_param("s", $nama);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}



function get_user_profile($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, nama, email, role, nomor_siswa, alamat, profile_photo FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

function update_user_profile($user_id, $nama, $email, $nomor_siswa = null, $alamat = null, $profile_photo = null) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, nomor_siswa = ?, alamat = ?, profile_photo = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $nama, $email, $nomor_siswa, $alamat, $profile_photo, $user_id);
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

function mark_attendance($user_id, $status, $selfie_path = null, $message = null) {
    global $conn;
    $today = date('Y-m-d');

    // Check if attendance already exists for today
    $stmt = $conn->prepare("SELECT id FROM absensi WHERE user_id = ? AND tanggal = ?");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        // Update existing
        $stmt = $conn->prepare("UPDATE absensi SET status = ?, selfie = ?, message = ? WHERE user_id = ? AND tanggal = ?");
        $stmt->bind_param("sssis", $status, $selfie_path, $message, $user_id, $today);
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO absensi (user_id, tanggal, status, selfie, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $today, $status, $selfie_path, $message);
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
    // Validate file first
    $validation_errors = validate_file_upload($file);
    if (!empty($validation_errors)) {
        log_security_event('FILE_UPLOAD_FAILED', 'Validation failed: ' . implode(', ', $validation_errors));
        return false;
    }

    // Use secure upload function
    $upload_dir = '../assets/uploads/selfies/';
    $filename = secure_file_upload($file, $upload_dir, 'selfie_' . $user_id . '_' . date('Ymd_His'));

    if ($filename) {
        log_security_event('FILE_UPLOAD_SUCCESS', 'Selfie uploaded for user: ' . $user_id);
        return 'assets/uploads/selfies/' . $filename;
    }

    log_security_event('FILE_UPLOAD_FAILED', 'Failed to save file for user: ' . $user_id);
    return false;
}

function register_guru($nama, $email, $password, $mata_pelajaran, $nomor_telepon, $alamat_guru) {
    global $conn;
    $errors = [];

    // Validate inputs
    if (empty($nama)) {
        $errors[] = "Nama harus diisi.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid.";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }
    if (empty($mata_pelajaran)) {
        $errors[] = "Mata pelajaran harus diisi.";
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        return ['success' => false, 'errors' => ['Email sudah terdaftar.']];
    }
    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new guru
    $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, mata_pelajaran, nomor_telepon, alamat_guru) VALUES (?, ?, ?, 'guru', ?, ?, ?)");
    $stmt->bind_param("ssssss", $nama, $email, $hashed_password, $mata_pelajaran, $nomor_telepon, $alamat_guru);

    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true];
    } else {
        $stmt->close();
        return ['success' => false, 'errors' => ['Gagal mendaftarkan guru.']];
    }
}

function get_teachers() {
    global $conn;
    $stmt = $conn->prepare("SELECT id, nama, email, mata_pelajaran, nomor_telepon, alamat_guru FROM users WHERE role = 'guru' ORDER BY nama");
    $stmt->execute();
    $result = $stmt->get_result();
    $teachers = [];
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
    $stmt->close();
    return $teachers;
}

function get_teacher_profile($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, nama, email, role, mata_pelajaran, nomor_telepon, alamat_guru FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

function update_teacher_profile($user_id, $nama, $email, $mata_pelajaran, $nomor_telepon, $alamat_guru) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, mata_pelajaran = ?, nomor_telepon = ?, alamat_guru = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $nama, $email, $mata_pelajaran, $nomor_telepon, $alamat_guru, $user_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}
?>
