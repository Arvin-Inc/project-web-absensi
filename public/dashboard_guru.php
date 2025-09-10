<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_logged_in() || $_SESSION['user_role'] != 'guru') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_profile = get_user_profile($user_id);

$classes = get_classes();
$selected_kelas_id = $_GET['kelas_id'] ?? null;
$students = get_students($selected_kelas_id);
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
    <link href="css/output.css" rel="stylesheet">
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
        <h1 class="flex ms-2 md:me-24">
          <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white">Absensi Kelas</span>
        </h1>
      </div>
      <div class="flex items-center">
          <div class="flex items-center ms-3">
            <div>
              <button type="button" class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                <span class="sr-only">Open user menu</span>
                <?php if (!empty($user_profile['profile_photo'])): ?>
                  <img class="w-8 h-8 rounded-full" src="../<?php echo htmlspecialchars($user_profile['profile_photo']); ?>" alt="user photo">
                <?php else: ?>
                  <img class="w-8 h-8 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>" alt="user photo">
                <?php endif; ?>
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
                <a href="#" onclick="showTab('attendance', this)" class="flex items-center p-2 text-sm text-gray-700 rounded-lg dark:text-white hover:bg-blue-500 dark:hover:bg-gray-700 group tab-button">

                   <span class="flex-1 ms-3 whitespace-nowrap">Absensi Hari Ini</span>
                </a>
                </li>
                <li>
                   <a href="#" onclick="showTab('input-absensi', this)" class="flex items-center p-2 text-sm text-gray-900 rounded-lg dark:text-white hover:bg-blue-500 dark:hover:bg-gray-700 group tab-button">
                      <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                         <path d="M9 1.001 10 2l-1 1v7.068a6.99 6.99 0 0 1 2.623.171l.092.03.03.092A6.984 6.984 0 0 1 12 11.999v1.068l.962-.962 1.847 1.848L14.962 15H15v.062l.962.962-1.847 1.848L12 16.931v-1.068a6.984 6.984 0 0 1-1.93-.696l-.092-.03-.03-.092A6.99 6.99 0 0 1 8 10.068V3L7 2l1-1ZM4 4v6a4 4 0 0 0 4 4v2a6 6 0 0 1-6-6V4h2Z"/>
                      </svg>
                      <span class="flex-1 ms-3 whitespace-nowrap">Input Absensi</span>
                   </a>
                </li>
                <li>
                   <a href="#" onclick="showTab('kelola-kelas', this)" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-blue-500 dark:hover:bg-gray-700 group tab-button">
                      <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                         <path d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377ZM6 12H4a1 1 0 0 1 0-2h2a1 1 0 0 1 0 2Z"/>
                      </svg>
                      <span class="flex-1 ms-3 whitespace-nowrap">Kelola Kelas</span>
                   </a>
                </li>
                <li>
                   <a href="#" onclick="showTab('reports', this)" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-blue-500 dark:hover:bg-gray-700 group tab-button">
                      <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                         <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                      </svg>
                      <span class="flex-1 ms-3 whitespace-nowrap">Laporan Absensi</span>
                   </a>
                </li>
                <li>
                   <a href="#" onclick="showTab('profile', this)" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-primary dark:hover:bg-gray-700 group tab-button">
                      <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                         <path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z"/>
                      </svg>
                      <span class="flex-1 ms-3 whitespace-nowrap">Edit Profil</span>
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
                   <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857M9 7h6m-3-3v6m-6 4h12" />
                   </svg>
                   <span class="ms-3">Daftar Siswa</span>
                </a>
             </li>
             <li>
                <a href="#" onclick="showTab('attendance', this)" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-primary dark:hover:bg-gray-700 group tab-button">
                   <svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7a2 2 0 002 2z" />
                   </svg>
                   <span class="flex-1 ms-3 whitespace-nowrap">Absensi Hari Ini</span>
                </a>
             </li>
             <li>
                <a href="#" onclick="showTab('input-absensi', this)" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-primary dark:hover:bg-gray-700 group tab-button">
                   <svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                   </svg>
                   <span class="flex-1 ms-3 whitespace-nowrap">Input Absensi</span>
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
                <a href="#" onclick="showTab('reports', this)" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-primary dark:hover:bg-gray-700 group tab-button">
                   <svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6a2 2 0 012-2h6m2 10h-8a2 2 0 01-2-2v-6a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2z" />
                   </svg>
                   <span class="flex-1 ms-3 whitespace-nowrap">Laporan Absensi</span>
                </a>
             </li>
             <li>
                <a href="#" onclick="showTab('profile', this)" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-primary dark:hover:bg-gray-700 group tab-button">
                   <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z"/>
                   </svg>
                   <span class="flex-1 ms-3 whitespace-nowrap">Edit Profil</span>
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
            <form method="GET" class="mb-4">
                <label for="kelas_id_students" class="block mb-2 font-medium text-gray-700">Filter berdasarkan Kelas:</label>
                <select id="kelas_id_students" name="kelas_id" class="border border-gray-300 rounded px-3 py-2 w-full max-w-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($classes as $kelas): ?>
                        <option value="<?php echo $kelas['id']; ?>" <?php if ($kelas['id'] == $selected_kelas_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($kelas['nama']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($students as $student): ?>
                    <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200">
                        <div class="bg-gradient-to-r from-primary to-blue-600 p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <?php if (!empty($student['profile_photo'])): ?>
                                        <img src="../<?php echo htmlspecialchars($student['profile_photo']); ?>" alt="Foto Profil" class="w-12 h-12 rounded-full object-cover" />
                                    <?php else: ?>
                                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                            <span class="text-white font-bold text-lg"><?php echo substr($student['nama'], 0, 1); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-white"><?php echo htmlspecialchars($student['nama']); ?></h3>
                                    <p class="text-blue-100 text-sm">Kelas: <?php echo htmlspecialchars($student['kelas'] ?? 'Belum ditentukan'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Email</p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($student['email']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">No. Telepon</p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($student['nomor_siswa'] ?? 'Belum diisi'); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Alamat</p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($student['alamat'] ?? 'Belum diisi'); ?></p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($students)): ?>
                    <div class="col-span-full bg-white shadow-lg rounded-lg p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada siswa</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            <?php if ($selected_kelas_id): ?>
                                Tidak ada siswa di kelas yang dipilih.
                            <?php else: ?>
                                Belum ada data siswa yang terdaftar.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Attendance Tab -->
        <div id="attendance-tab" class="tab-content hidden">
            <h2 class="text-2xl font-bold mb-4">Absensi Hari Ini (<?php echo date('d-m-Y'); ?>)</h2>
            <form method="GET" class="mb-4">
                <label for="kelas_id" class="block mb-2 font-medium text-gray-700">Filter berdasarkan Kelas:</label>
                <select id="kelas_id" name="kelas_id" class="border border-gray-300 rounded px-3 py-2 w-full max-w-sm" onchange="this.form.submit()">
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
                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo $record['nama']; ?></h3>
                                    <p class="text-gray-500">Status: <?php echo $record['status']; ?></p>
                                    <?php if (!empty($record['message'])): ?>
                                        <p class="text-gray-700 text-sm"><?php echo nl2br(htmlspecialchars($record['message'])); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($record['selfie'])): ?>
                                    <div class="flex-shrink-0">
                                        <img src="../assets/uploads/selfies/<?php echo htmlspecialchars($record['selfie']); ?>" alt="Selfie" class="w-16 h-16 object-cover rounded-lg border">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($attendance_today)): ?>
                        <li class="px-6 py-4 text-center text-gray-500">Belum ada data absensi hari ini.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Input Absensi Tab -->
        <div id="input-absensi-tab" class="tab-content hidden">
            <h2 class="text-2xl font-bold mb-4">Input Absensi Hari Ini (<?php echo date('d-m-Y'); ?>)</h2>
            <form method="GET" class="mb-4">
                <label for="kelas_id_input" class="block mb-2 font-medium text-gray-700">Pilih Kelas:</label>
                <select id="kelas_id_input" name="kelas_id" class="border border-gray-300 rounded px-3 py-2 w-full max-w-sm" onchange="this.form.submit()">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($classes as $kelas): ?>
                        <option value="<?php echo $kelas['id']; ?>" <?php if ($kelas['id'] == $selected_kelas_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($kelas['nama']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if (!empty($students)): ?>
                <form id="attendance-form" method="POST" action="ajax_handler_guru.php">
                    <input type="hidden" name="action" value="bulk_mark_attendance" />

                    <!-- Mobile Card View -->
                    <div class="block md:hidden space-y-4">
                        <?php foreach ($students as $student): ?>
                            <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200">
                                <div class="bg-gradient-to-r from-primary to-blue-600 p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <?php if (!empty($student['profile_photo'])): ?>
                                                <img src="../<?php echo htmlspecialchars($student['profile_photo']); ?>" alt="Foto Profil" class="w-10 h-10 rounded-full object-cover" />
                                            <?php else: ?>
                                                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                                    <span class="text-white font-bold text-sm"><?php echo substr($student['nama'], 0, 1); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-base font-semibold text-white"><?php echo htmlspecialchars($student['nama']); ?></h3>
                                            <p class="text-blue-100 text-xs">Kelas: <?php echo htmlspecialchars($student['kelas'] ?? 'Belum ditentukan'); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Status Absensi:</span>
                                        <select name="attendance[<?php echo $student['id']; ?>]" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                            <option value="">-- Pilih --</option>
                                            <option value="Hadir">Hadir</option>
                                            <option value="Izin">Izin</option>
                                            <option value="Sakit">Sakit</option>
                                            <option value="Alpa">Alpa</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td class="px-2 sm:px-6 py-4 whitespace-nowrap">
                                            <input type="text" value="<?php echo htmlspecialchars($student['nama']); ?>" readonly class="w-full min-w-0 px-1 sm:px-2 py-1 text-xs sm:text-sm border border-gray-300 rounded bg-gray-50 text-gray-700 focus:outline-none focus:ring-1 focus:ring-primary">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <select name="attendance[<?php echo $student['id']; ?>]" class="border border-gray-300 rounded px-2 py-1">
                                                <option value="">-- Pilih Status --</option>
                                                <option value="Hadir">Hadir</option>
                                                <option value="Izin">Izin</option>
                                                <option value="Sakit">Sakit</option>
                                                <option value="Alpa">Alpa</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <button type="submit" class="mt-4 bg-primary text-white px-4 py-2 rounded hover:bg-blue-700">Simpan Absensi</button>
                </form>
            <?php else: ?>
                <p class="text-gray-500">Tidak ada siswa di kelas ini.</p>
            <?php endif; ?>
        </div>

        <!-- Kelola Kelas Tab -->
        <div id="kelola-kelas-tab" class="tab-content hidden">
            <h2 class="text-2xl font-bold mb-4">Kelola Kelas</h2>
            <form method="POST" class="mb-4">
                <label for="class_name" class="block mb-2 font-medium text-gray-700">Nama Kelas Baru:</label>
                <input type="text" id="class_name" name="class_name" class="border border-gray-300 rounded px-3 py-2 w-64" required>
                <button type="submit" name="add_class" class="ml-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah Kelas</button>
            </form>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($classes as $kelas): ?>
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($kelas['nama']); ?></h3>
                                    <p class="text-gray-500">ID: <?php echo $kelas['id']; ?></p>
                                </div>
                                <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-700">Hapus</button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($classes)): ?>
                        <li class="px-6 py-4 text-center text-gray-500">Belum ada kelas yang terdaftar.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="reports-tab" class="tab-content hidden">
            <h2 class="text-2xl font-bold mb-4">Laporan Absensi</h2>
            <form method="GET" class="mb-4">
                <label for="kelas_id_reports" class="block mb-2 font-medium text-gray-700">Filter berdasarkan Kelas:</label>
                <select id="kelas_id_reports" name="kelas_id" class="border border-gray-300 rounded px-3 py-2 w-full max-w-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($classes as $kelas): ?>
                        <option value="<?php echo $kelas['id']; ?>" <?php if ($kelas['id'] == $selected_kelas_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($kelas['nama']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php
            $attendance_reports = get_attendance_report($selected_kelas_id);
            if (!empty($attendance_reports)):
            ?>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($attendance_reports as $report): ?>
                        <li class="px-6 py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($report['nama']); ?></h3>
                                    <p class="text-gray-500">Tanggal: <?php echo htmlspecialchars($report['tanggal']); ?></p>
                                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($report['message'])); ?></p>
                                </div>
                                <?php if (!empty($report['selfie'])): ?>
                                    <div class="flex-shrink-0">
                                        <img src="../assets/uploads/selfies/<?php echo htmlspecialchars($report['selfie']); ?>" alt="Selfie" class="w-16 h-16 object-cover rounded-lg border">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php else: ?>
                <p class="text-gray-500">Belum ada laporan absensi.</p>
            <?php endif; ?>
        </div>

        <!-- Profile Tab -->
        <div id="profile-tab" class="tab-content hidden">
            <h2 class="text-2xl font-bold mb-4">Edit Profil</h2>
            <?php
            $teacher_profile = get_teacher_profile($_SESSION['user_id']);
            ?>
            <div class="bg-white shadow-lg rounded-lg p-6">
                <form id="profile-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_teacher_profile" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($teacher_profile['nama'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($teacher_profile['email'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div>
                            <label for="mata_pelajaran" class="block text-sm font-medium text-gray-700 mb-2">Mata Pelajaran</label>
                            <input type="text" id="mata_pelajaran" name="mata_pelajaran" value="<?php echo htmlspecialchars($teacher_profile['mata_pelajaran'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div>
                            <label for="nomor_telepon" class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                            <input type="tel" id="nomor_telepon" name="nomor_telepon" value="<?php echo htmlspecialchars($teacher_profile['nomor_telepon'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div class="md:col-span-2">
                            <label for="alamat_guru" class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <textarea id="alamat_guru" name="alamat_guru" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"><?php echo htmlspecialchars($teacher_profile['alamat_guru'] ?? ''); ?></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label for="profile_photo" class="block text-sm font-medium text-gray-700 mb-2">Foto Profil</label>
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <?php if (!empty($teacher_profile['profile_photo'])): ?>
                                        <img src="../<?php echo htmlspecialchars($teacher_profile['profile_photo']); ?>" alt="Foto Profil" class="w-16 h-16 object-cover rounded-full border">
                                    <?php else: ?>
                                        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                                            <span class="text-gray-500 text-sm">No Photo</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <input type="file" id="profile_photo" name="profile_photo" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    <p class="text-sm text-gray-500 mt-1">Pilih file gambar (JPG, PNG, GIF) maksimal 2MB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        </div>
    </div>

    <script>
        function showTab(tabId, element) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.add('hidden'));

            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => button.classList.remove('bg-gray-100/10', 'text-white'));

            // Show selected tab
            document.getElementById(tabId + '-tab').classList.remove('hidden');

            // Add active class to clicked button
            if (element) {
                element.classList.add('bg-gray-100/10', 'text-white');
            }
        }

        // Handle attendance form submission
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle
            const sidebar = document.getElementById('sidebar-multi-level-sidebar');
            const toggleBtn = document.querySelector('[data-drawer-toggle="sidebar-multi-level-sidebar"]');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                });
            }

            const attendanceForm = document.getElementById('attendance-form');
            if (attendanceForm) {
                attendanceForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);

                    fetch('ajax_handler_guru.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            // Reload the page to show updated attendance
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menyimpan absensi.');
                    });
                });
            }

            // Handle profile form submission
            const profileForm = document.getElementById('profile-form');
            if (profileForm) {
                profileForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);

                    fetch('ajax_handler_guru.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            // Reload the page to show updated profile
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menyimpan profil.');
                    });
                });
            }

            // Show default tab on page load
            showTab('students');
        });
    </script>
</body>
</html>
