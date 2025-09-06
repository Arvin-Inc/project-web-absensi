<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_logged_in() || $_SESSION['user_role'] != 'siswa') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_profile = get_user_profile($user_id);
$attendance_reports = get_attendance_report($user_id);

$attendance_success = null;
$attendance_error = null;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    if (update_user_profile($user_id, $nama, $email)) {
        $_SESSION['user_name'] = $nama;
        $success = "Profil berhasil diperbarui.";
        $user_profile = get_user_profile($user_id);
    } else {
        $error = "Gagal memperbarui profil.";
    }
}

// Handle self-attendance with selfie upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['self_attendance'])) {
    $status = $_POST['status'];
    if ($status === 'Hadir') {
        if (isset($_FILES['selfie']) && $_FILES['selfie']['error'] === UPLOAD_ERR_OK) {
            $validation = validate_image($_FILES['selfie']);
            if ($validation === true) {
                $selfie_path = upload_selfie($_FILES['selfie'], $user_id);
                if ($selfie_path) {
                    if (mark_attendance($user_id, $status, $selfie_path)) {
                        $attendance_success = "Absensi berhasil dicatat dengan selfie.";
                    } else {
                        $attendance_error = "Gagal mencatat absensi.";
                    }
                } else {
                    $attendance_error = "Gagal mengunggah selfie.";
                }
            } else {
                $attendance_error = $validation;
            }
        } else {
            $attendance_error = "Selfie wajib diunggah untuk status Hadir.";
        }
    } else {
        // For other statuses, no selfie required
        if (mark_attendance($user_id, $status)) {
            $attendance_success = "Absensi berhasil dicatat.";
        } else {
            $attendance_error = "Gagal mencatat absensi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Siswa - Absensi Kelas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#10B981'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">Dashboard Siswa</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Selamat datang, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Tabs -->
        <div class="mb-8">
            <nav class="flex space-x-4" aria-label="Tabs">
                <button onclick="showTab('attendance')" class="tab-button bg-primary text-white px-4 py-2 rounded-md">Status Absensi</button>
                <button onclick="showTab('profile')" class="tab-button bg-gray-200 text-gray-700 px-4 py-2 rounded-md">Edit Profil</button>
            </nav>
        </div>

        <!-- Attendance Tab -->
        <div id="attendance-tab" class="tab-content">
            <h2 class="text-2xl font-bold mb-4">Status Absensi Anda</h2>

            <!-- Self-Attendance Form -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md p-6 mb-6">
                <h3 class="text-lg font-medium mb-4">Catat Absensi Hari Ini</h3>
                <?php if ($attendance_success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo $attendance_success; ?>
                    </div>
                <?php endif; ?>
                <?php if ($attendance_error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $attendance_error; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status Absensi</label>
                        <select id="status" name="status" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Alpha">Alpha</option>
                        </select>
                    </div>
                    <div class="mb-4" id="selfie-field" style="display: none;">
                        <label for="selfie" class="block text-sm font-medium text-gray-700">Upload Selfie</label>
                        <input type="file" id="selfie" name="selfie" accept="image/*" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary" />
                        <img id="selfie-preview" src="#" alt="Preview Selfie" class="mt-2 max-w-xs hidden" />
                    </div>
                    <button type="submit" name="self_attendance" class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-700">
                        Catat Absensi
                    </button>
                </form>
            </div>

            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selfie</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($attendance_reports as $report): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo date('d-m-Y', strtotime($report['tanggal'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php 
                                        switch($report['status']) {
                                            case 'Hadir': echo 'bg-green-100 text-green-800'; break;
                                            case 'Izin': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'Sakit': echo 'bg-blue-100 text-blue-800'; break;
                                            case 'Alpha': echo 'bg-red-100 text-red-800'; break;
                                        }
                                        ?>">
                                        <?php echo $report['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($report['selfie']): ?>
                                        <img src="../<?php echo $report['selfie']; ?>" alt="Selfie" class="h-16 w-16 object-cover rounded" />
                                    <?php else: ?>
                                        <span class="text-gray-400 italic">Tidak ada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($attendance_reports)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">Belum ada data absensi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Profile Tab -->
        <div id="profile-tab" class="tab-content hidden">
            <h2 class="text-2xl font-bold mb-4">Edit Profil</h2>
            <div class="bg-white shadow overflow-hidden sm:rounded-md p-6">
                <?php if (isset($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-4">
                        <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                        <input type="text" id="nama" name="nama" value="<?php echo $user_profile['nama']; ?>" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo $user_profile['email']; ?>" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                    <button type="submit" name="update_profile" class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-700">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('bg-primary', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.remove('hidden');
            // Add active class to clicked button
            event.target.classList.remove('bg-gray-200', 'text-gray-700');
            event.target.classList.add('bg-primary', 'text-white');
        }

        // Show/hide selfie field based on status
        document.getElementById('status').addEventListener('change', function() {
            const selfieField = document.getElementById('selfie-field');
            if (this.value === 'Hadir') {
                selfieField.style.display = 'block';
                document.getElementById('selfie').required = true;
            } else {
                selfieField.style.display = 'none';
                document.getElementById('selfie').required = false;
            }
        });

        // Preview selfie image
        document.getElementById('selfie').addEventListener('change', function(event) {
            const preview = document.getElementById('selfie-preview');
            const file = event.target.files[0];
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            } else {
                preview.src = '#';
                preview.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
