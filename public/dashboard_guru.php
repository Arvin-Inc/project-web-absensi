<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_logged_in() || $_SESSION['user_role'] != 'guru') {
    header("Location: login.php");
    exit();
}

$students = get_students();
$classes = get_classes();
$selected_kelas_id = $_GET['kelas_id'] ?? null;
$attendance_today = get_attendance_today($selected_kelas_id);

$generated_code = null;
$guru_id = $_SESSION['user_id'];

// Handle add class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_class'])) {
    $class_name = trim($_POST['class_name']);
    if (!empty($class_name)) {
        add_class($class_name);
        header("Location: dashboard_guru.php");
        exit();
    }
}

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

<nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
  <div class="px-3 py-3 lg:px-5 lg:pl-3">
    <div class="flex items-center justify-between">
      <div class="flex items-center justify-start rtl:justify-end">
        <button data-drawer-target="sidebar-multi-level-sidebar" data-drawer-toggle="sidebar-multi-level-sidebar" aria-controls="sidebar-multi-level-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
            <span class="sr-only">Open sidebar</span>
            <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
               <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
            </svg>
         </button>
        <a href="index.php" class="flex ms-2 md:me-24">
          <img src="##" class="h-8 me-3" alt="Absensi Kelas Logo" />
          <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white">Absensi Kelas</span>
        </a>
      </div>
      <div class="flex items-center">
          <div class="flex items-center ms-3">
            <div>
              <button type="button" class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                <span class="sr-only">Open user menu</span>
                <img class="w-8 h-8 rounded-full" src="https://ui-avatars.com/api/?name=Arfi+Nade" alt="user photo">
              </button>
            </div>
            <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-sm shadow-sm dark:bg-gray-700 dark:divide-gray-600" id="dropdown-user">
              <div class="px-4 py-3" role="none">
                <p class="text-sm text-gray-900 dark:text-white" role="none">
                  <?php echo $_SESSION['user_name']; ?>
                </p>
                <p class="text-sm font-medium text-gray-900 truncate dark:text-gray-300" role="none">
                  <?php echo $_SESSION['user_email']; ?>
                </p>
              </div>
              <ul class="py-1" role="none">
                <li>
                  <a href="#" onclick="showTab('students', this)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">Dashboard</a>
                </li>
                <li>
                  <a href="#" onclick="showTab('attendance', this)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">Absensi Hari Ini</a>
                </li>
                <li>
                  <a href="#" onclick="showTab('kelola-kelas', this)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">Kelola Kelas</a>
                </li>
                <li>
                  <a href="#" onclick="showTab('reports-izin', this)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">Laporan</a>
                </li>
                <li>
                  <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">Logout</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
    </div>
  </div>
</nav>

    <!-- Sidebar -->
    <aside id="sidebar-multi-level-sidebar" class="fixed top-14 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
       <div class="h-full px-3 py-4 overflow-y-auto bg-gray-50 dark:bg-gray-800">
          <ul class="space-y-2 font-medium">
             <li>
                <a href="#" onclick="showTab('students', this)" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-primary dark:hover:bg-gray-700 group tab-button">
                   <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                      <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                      <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
                   </svg>
                   <span class="ms-3">Daftar Siswa</span>
                </a>
             </li>
             <li>
                <a href="#" onclick="showTab('attendance', this)" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-primary dark:hover:bg-gray-700 group tab-button">
                   <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
                      <path d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z"/>
                   </svg>
                   <span class="flex-1 ms-3 whitespace-nowrap">Absensi Hari Ini</span>
                </a>
             </li>
             <li>
                <a href="#" onclick="showTab('kelola-kelas', this)" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-primary dark:hover:bg-gray-700 group tab-button">
                   <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                      <path d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377ZM6 12H4a1 1 0 0 1 0-2h2a1 1 0 0 1 0 2Z"/>
                   </svg>
                   <span class="flex-1 ms-3 whitespace-nowrap">Kelola Kelas</span>
                </a>
             </li>
             <li>
                <a href="#" onclick="showTab('reports-izin', this)" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-primary dark:hover:bg-gray-700 group tab-button">
                   <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                      <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                   </svg>
                   <span class="flex-1 ms-3 whitespace-nowrap">Laporan</span>
                </a>
             </li>
             <li>
                <a href="logout.php" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-primary dark:hover:bg-gray-700 group">
                   <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 16">
                      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 8h11m0 0L8 4m4 4-4 4m4-11h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3"/>
                   </svg>
                   <span class="flex-1 ms-3 whitespace-nowrap">Logout</span>
                </a>
             </li>
          </ul>
       </div>
    </aside>

    <div class="p-4 sm:ml-64 mt-14">
        <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700">
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
            <form method="GET" class="mb-4">
                <label for="kelas_id" class="block mb-2 font-medium text-gray-700">Filter berdasarkan Kelas:</label>
                <select id="kelas_id" name="kelas_id" class="border border-gray-300 rounded px-3 py-2 w-64" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($classes as $kelas): ?>
                        <option value="<?php echo $kelas['id']; ?>" <?php if ($kelas['id'] == $selected_kelas_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($kelas['nama']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($attendance_today as $record): ?>
                        <li class="px-6 py-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900"><?php echo $record['nama']; ?></h3>
                                <p class="text-gray-500">Status: <?php echo $record['status']; ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($attendance_today)): ?>
                        <li class="px-6 py-4 text-center text-gray-500">Belum ada data absensi hari ini.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Kelola Kelas Tab -->
        <div id="kelola-kelas-tab" class="tab-content hidden">
            <h2 class="text-2xl font-bold mb-4">Kelola Kelas</h2>
            <h3 class="text-xl font-bold mb-4">Tambah Kelas Baru</h3>
            <form method="POST" class="mb-8">
                <label for="class_name" class="block mb-2 font-medium text-gray-700">Nama Kelas:</label>
                <input type="text" id="class_name" name="class_name" class="border border-gray-300 rounded px-3 py-2 w-64" required>
                <button type="submit" name="add_class" class="ml-4 bg-secondary text-white px-4 py-2 rounded hover:bg-green-700">Tambah Kelas</button>
            </form>
            <h3 class="text-xl font-bold mb-4">Daftar Kelas</h3>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($classes as $kelas): ?>
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($kelas['nama']); ?></h3>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Reports & Izin/Sakit Messages Tab -->
        <div id="reports-izin-tab" class="tab-content hidden">
            <h2 class="text-2xl font-bold mb-4">Laporan Absensi & Pesan Izin/Sakit</h2>
            <form method="GET" class="mb-4">
                <div class="flex space-x-4">
                    <input type="date" name="start_date" class="border border-gray-300 rounded px-3 py-2" />
                    <input type="date" name="end_date" class="border border-gray-300 rounded px-3 py-2" />
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
                </div>
            </form>

            <!-- Messages Section -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md mb-8">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Pesan Izin/Sakit Hari Ini</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php
                    $today_messages = get_today_messages();
                    if (!empty($today_messages)):
                        foreach ($today_messages as $message):
                    ?>
                        <div class="px-6 py-4">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <?php if ($message['selfie']): ?>
                                        <img src="../<?php echo $message['selfie']; ?>" alt="Selfie" class="h-12 w-12 rounded-full object-cover">
                                    <?php else: ?>
                                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-gray-600 text-sm"><?php echo substr($message['nama'], 0, 1); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-medium text-gray-900"><?php echo $message['nama']; ?></h4>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            <?php
                                            switch($message['status']) {
                                                case 'Izin': echo 'bg-yellow-100 text-yellow-800'; break;
                                                case 'Sakit': echo 'bg-blue-100 text-blue-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php echo $message['status']; ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($message['message'] ?? 'Tidak ada pesan'); ?></p>
                                    <p class="text-xs text-gray-400 mt-1"><?php echo date('d-m-Y H:i', strtotime($message['tanggal'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php
                        endforeach;
                    else:
                    ?>
                        <div class="px-6 py-4 text-center text-gray-500">
                            Tidak ada pesan izin/sakit hari ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Attendance Reports Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pesan</th>
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
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($report['message'] ?? ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($report['selfie']): ?>
                                        <img src="../<?php echo $report['selfie']; ?>" alt="Selfie" class="h-16 w-16 object-cover rounded" />
                                    <?php else: ?>
                                        <span class="text-gray-400 italic">Tidak ada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Selfie Section -->
            <h3 class="text-xl font-bold mb-4 mt-8">Selfie Absensi</h3>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="p-4">
                    <p class="text-gray-500">Daftar selfie yang diupload oleh siswa:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                        <?php
                        $selfie_dir = '../assets/uploads/selfies/';
                        if (is_dir($selfie_dir)) {
                            $files = scandir($selfie_dir);
                            foreach ($files as $file) {
                                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'jpg') {
                                    echo '<div class="bg-gray-100 p-2 rounded">';
                                    echo '<img src="' . $selfie_dir . $file . '" alt="Selfie" class="w-full h-32 object-cover rounded">';
                                    echo '<p class="text-sm text-gray-600 mt-2">' . $file . '</p>';
                                    echo '</div>';
                                }
                            }
                        } else {
                            echo '<p class="text-gray-500">Tidak ada selfie yang diupload.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded shadow-lg">
            <div class="flex items-center space-x-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                <span>Loading...</span>
            </div>
        </div>
    </div>

    <script>
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        function showTab(tabName, button) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.remove('hidden');
            // Update URL without reloading
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        }

        // Sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar-multi-level-sidebar');
            const toggleBtn = document.querySelector('[data-drawer-toggle="sidebar-multi-level-sidebar"]');

            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
            });

            // Show loading on form submit
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    document.getElementById('loading-overlay').classList.remove('hidden');
                });
            });

            // Check URL for active tab
            const activeTab = getUrlParameter('tab');
            if (activeTab) {
                showTab(activeTab);
            }
        });

        // Hide loading on page load
        window.addEventListener('load', function() {
            document.getElementById('loading-overlay').classList.add('hidden');
        });
    </script>
</body>
</html>
