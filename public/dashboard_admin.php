<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_logged_in() || $_SESSION['user_role'] != 'guru') {
    header("Location: login.php");
    exit();
}

$classes = get_classes();

$selected_class_id = $_GET['kelas_id'] ?? null;
$attendance_report = [];
if ($selected_class_id) {
    $attendance_report = get_attendance_report(null, null, null, $selected_class_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Admin - Absensi Kelas</title>
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
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">Dashboard Admin</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Selamat datang, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h2 class="text-2xl font-bold mb-4">Lihat Data Absensi per Kelas</h2>
        <form method="GET" class="mb-6">
            <label for="kelas_id" class="block mb-2 font-medium text-gray-700">Pilih Kelas:</label>
            <select id="kelas_id" name="kelas_id" class="border border-gray-300 rounded px-3 py-2 w-64">
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($classes as $kelas): ?>
                    <option value="<?php echo $kelas['id']; ?>" <?php if ($kelas['id'] == $selected_class_id) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($kelas['nama']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="ml-4 bg-primary text-white px-4 py-2 rounded hover:bg-blue-700">Tampilkan</button>
        </form>

        <?php if ($selected_class_id): ?>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($attendance_report)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">Belum ada data absensi untuk kelas ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($attendance_report as $record): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($record['nama']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d-m-Y', strtotime($record['tanggal'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $record['status']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
