<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Kelas</title>
    <link href="css/output.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-green-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">Absensi</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="login.php" class="text-gray-600 hover:text-blue-500 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                    <a href="register.php" class="bg-blue-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
                Absensi
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-emerald-500">Kelas</span>
            </h1>
            <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                Kelola absensi siswa dengan mudah dan efisien. Sistem yang dirancang untuk guru dan siswa dengan antarmuka yang intuitif.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="register.php" class="bg-blue-500 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 transition duration-300">
                    Daftar Sekarang
                </a>
                <a href="login.php" class="border-2 border-blue-500 text-blue-500 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-500 hover:text-white transition duration-300">
                    Masuk
                </a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Fitur Unggulan</h2>
            <p class="text-gray-600">Semua yang Anda butuhkan untuk mengelola absensi dengan baik</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Mudah Digunakan</h3>
                <p class="text-gray-600">Antarmuka yang sederhana dan intuitif untuk semua pengguna.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="w-12 h-12 bg-emerald-500 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Laporan Real-time</h3>
                <p class="text-gray-600">Pantau absensi siswa secara real-time dengan laporan harian dan mingguan.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Aman & Terpercaya</h3>
                <p class="text-gray-600">Data absensi Anda aman dengan sistem keamanan yang terintegrasi.</p>
            </div>
        </div>
    </div>

    <!-- Footer -->


<footer class="bg-gray-800">
    <div class="mx-auto w-full max-w-screen-xl p-4 py-6 lg:py-8">
        <div class="md:justify-between">
          <div class="mb-6 md:mb-0">
              <a href="https://flowbite.com/" class="flex items-center">
                  <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white mb-6">Absensi</span>
              </a>
          </div>
          <div class="grid grid-cols-3 gap-8 sm:gap-6 sm:grid-cols-3 mb-6">
              <div>
                  <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Resources</h2>
                  <ul class="text-gray-500 dark:text-gray-400 font-medium">
                      <li class="mb-4">
                          <a href="https://flowbite.com/" class="hover:underline">Flowbite</a>
                      </li>
                      <li>
                          <a href="https://tailwindcss.com/" class="hover:underline">Tailwind CSS</a>
                      </li>
                  </ul>
              </div>
              <div>
                  <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Follow us</h2>
                  <ul class="text-gray-500 dark:text-gray-400 font-medium">
                      <li class="mb-4">
                          <a href="https://github.com/themesberg/flowbite" class="hover:underline ">Github</a>
                      </li>
                      <li>
                          <a href="https://discord.gg/4eeurUVvTy" class="hover:underline">Discord</a>
                      </li>
                  </ul>
              </div>
              <div>
                  <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Legal</h2>
                  <ul class="text-gray-500 dark:text-gray-400 font-medium">
                      <li class="mb-4">
                          <a href="#" class="hover:underline">Privacy Policy</a>
                      </li>
                      <li>
                          <a href="#" class="hover:underline">Terms &amp; Conditions</a>
                      </li>
                  </ul>
              </div>
          </div>
      </div>
      <hr class="my-6 border-gray-200 sm:mx-auto dark:border-gray-700 lg:my-8" />
      <div class="sm:flex sm:items-center sm:justify-between">
          <span class="text-sm text-gray-500 sm:text-center dark:text-gray-400">© 2025. All Rights Reserved.</span>
      </div>
    </div>
</footer>
</body>
</html>
