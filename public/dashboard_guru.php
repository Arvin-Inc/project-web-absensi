<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_logged_in() || $_SESSION['user_role'] != 'guru') {
    header("Location: login.php");
    exit();
}

$students = get_students();
$attendance_today = get_attendance_today();

$generated_code = null;
$guru_id = $_SESSION['user_id'];

// Handle generate code request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_code'])) {
    $generated_code = generate_code($guru_id);
}

// Handle attendance input
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_attendance'])) {
    $user_id = $_POST['user_id'];
    $status = $_POST['status'];
    mark_attendance($user_id, $status);
    header("Location: dashboard_guru.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Guru - Absensi Kelas</title>
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
                    <h1 class="text-xl font-bold text-gray-800">Dashboard Guru</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Selamat datang, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="dashboard_admin.php" class="text-primary hover:text-blue-700 font-semibold">Data Absesnsi Kelas</a>
                    <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Tabs -->
        <div class="mb-8">
            <nav class="flex space-x-4" aria-label="Tabs">
                <button onclick="showTab('students')" class="tab-button bg-primary text-white px-4 py-2 rounded-md">Daftar Siswa</button>
                <button onclick="showTab('attendance')" class="tab-button bg-gray-200 text-gray-700 px-4 py-2 rounded-md">Absensi Hari Ini</button>
                <button onclick="showTab('selfie')" class="tab-button bg-gray-200 text-gray-700 px-4 py-2 rounded-md">Selfie</button>
                <button onclick="showTab('reports')" class="tab-button bg-gray-200 text-gray-700 px-4 py-2 rounded-md">Laporan</button>
            </nav>
        </div>

        <!-- Students Tab -->
        <div id="students-tab" class="tab-content">
            <h2 class="text-2xl font-bold mb-4">Daftar Siswa</h2>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($students as $student): ?>
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo $student['nama']; ?></h3>
                                    <p class="text-gray-500"><?php echo $student['email']; ?></p>
                                </div>
                                <form method="POST" class="flex space-x-2">
                                    <input type="hidden" name="user_id" value="<?php echo $student['id']; ?>">
                                    <select name="status" class="border border-gray-300 rounded px-2 py-1">
                                        <option value="Hadir">Hadir</option>
                                        <option value="Izin">Izin</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Alpha">Alpha</option>
                                    </select>
                                    <button type="submit" name="mark_attendance" class="bg-primary text-white px-4 py-1 rounded hover:bg-blue-700">Input</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Attendance Tab -->
        <div id="attendance-tab" class="tab-content hidden">
            <h2 class="text-2xl font-bold mb-4">Absensi Hari Ini (<?php echo date('d-m-Y'); ?>)</h2>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($attendance_today as $record): ?>
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo $record['nama']; ?></h3>
                                    <p class="text-gray-500">Status: <?php echo $record['status']; ?></p>
                                </div>
                                <div>
                                    <?php if ($record['selfie']): ?>
                                        <img src="../<?php echo $record['selfie']; ?>" alt="Selfie" class="h-16 w-16 object-cover rounded" />
                                    <?php else: ?>
                                        <span class="text-gray-400 italic">Tidak ada selfie</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($attendance_today)): ?>
                        <li class="px-6 py-4 text-center text-gray-500">Belum ada data absensi hari ini.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Selfie Tab -->
        <div id="selfie-tab" class="tab-content hidden">
            <h2 class="text-2xl font-bold mb-4">Selfie Siswa</h2>
            <div class="bg-white shadow overflow-hidden sm:rounded-md p-6">
                <ul class="grid grid-cols-4 gap-4">
                    <?php foreach ($attendance_today as $record): ?>
                        <?php if ($record['selfie']): ?>
                            <li>
                                <img src="../<?php echo $record['selfie']; ?>" alt="Selfie <?php echo $record['nama']; ?>" class="h-32 w-32 object-cover rounded cursor-pointer" onclick="openModal(this.src, '<?php echo htmlspecialchars($record['nama'], ENT_QUOTES); ?>')" />
                                <p class="text-center mt-2"><?php echo $record['nama']; ?></p>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (empty($attendance_today) || !array_filter($attendance_today, fn($r) => $r['selfie'])): ?>
                        <li class="col-span-4 text-center text-gray-500">Belum ada selfie hari ini.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="reports-tab" class="tab-content hidden">
            <h2 class="text-2xl font-bold mb-4">Laporan Absensi</h2>
            <form method="GET" class="mb-4">
                <div class="flex space-x-4">
                    <input type="date" name="start_date" class="border border-gray-300 rounded px-3 py-2" />
                    <input type="date" name="end_date" class="border border-gray-300 rounded px-3 py-2" />
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
                </div>
            </form>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selfie</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $start_date = $_GET['start_date'] ?? null;
                        $end_date = $_GET['end_date'] ?? null;
                        $reports = get_attendance_report(null, $start_date, $end_date);
                        foreach ($reports as $report):
                        ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $report['nama']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d-m-Y', strtotime($report['tanggal'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $report['status']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($report['selfie']): ?>
                                        <img src="../<?php echo $report['selfie']; ?>" alt="Selfie" class="h-16 w-16 object-cover rounded" />
                                    <?php else: ?>
                                        <span class="text-gray-400 italic">Tidak ada selfie</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
    </script>
</body>
</html>
