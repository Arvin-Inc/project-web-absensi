<?php
require_once '../config/db.php';

function login($email, $password) {
    global $conn;
    $errors = [];

    // Validation
    if (empty($email)) {
        $errors[] = "Email harus diisi.";
    }
    if (empty($password)) {
        $errors[] = "Password harus diisi.";
    }

    if (empty($errors)) {
        // Check user in database
        $stmt = $conn->prepare("SELECT id, nama, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nama'];
                $_SESSION['user_role'] = $user['role'];
                return ['success' => true, 'role' => $user['role']];
            } else {
                $errors[] = "Password salah.";
            }
        } else {
            $errors[] = "Email tidak ditemukan.";
        }
        $stmt->close();
    }

    return ['success' => false, 'errors' => $errors];
}

function register($nama, $email, $password, $role, $kelas_id = null, $nomor_siswa = null, $alamat = null) {
    global $conn;
    $errors = [];

    // Validation
    if (empty($nama)) {
        $errors[] = "Nama harus diisi.";
    }
    if (empty($email)) {
        $errors[] = "Email harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    if (empty($password)) {
        $errors[] = "Password harus diisi.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }
    if (!in_array($role, ['guru', 'siswa'])) {
        $errors[] = "Role tidak valid.";
    }
    if ($role == 'siswa' && empty($kelas_id)) {
        $errors[] = "Kelas harus dipilih untuk siswa.";
    }
    if ($role == 'siswa' && empty($nomor_siswa)) {
        $errors[] = "Nomor siswa harus diisi untuk siswa.";
    }
    if ($role == 'siswa' && empty($alamat)) {
        $errors[] = "Alamat harus diisi untuk siswa.";
    }

    if (empty($errors)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email sudah terdaftar.";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, kelas_id, nomor_siswa, alamat) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssiss", $nama, $email, $hashed_password, $role, $kelas_id, $nomor_siswa, $alamat);
            if ($stmt->execute()) {
                return ['success' => true];
            } else {
                $errors[] = "Gagal mendaftarkan akun.";
            }
        }
        $stmt->close();
    }

    return ['success' => false, 'errors' => $errors];
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
