<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$errors = [];
$csrf_token = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        log_security_event('CSRF_ATTACK_DETECTED', 'Invalid CSRF token in guru registration attempt');
        $errors[] = "Permintaan tidak valid. Silakan coba lagi.";
    } else {
        // Sanitize inputs
        $nama = sanitize_input($_POST['nama']);
        $email = sanitize_input($_POST['email']);
        $password = sanitize_input($_POST['password']);
        $role = 'guru';

        $result = register($nama, $email, $password, $role);
        if ($result['success']) {
            log_security_event('REGISTRATION_SUCCESS', 'New guru registered: ' . $email);
            header("Location: login.php?registered=1");
            exit();
        } else {
            $errors = $result['errors'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register Guru - Absensi Kelas</title>
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
<body class="bg-gradient-to-br from-blue-50 to-green-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Daftar Akun Guru Baru
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Atau
                <a href="login.php" class="font-medium text-primary hover:text-blue-500">
                    masuk ke akun Anda
                </a>
            </p>
        </div>
        <form class="mt-8 space-y-6" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="nama" class="sr-only">Nama</label>
                    <input id="nama" name="nama" type="text" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                           placeholder="Nama Lengkap" />
                </div>
                <div>
                    <label for="email" class="sr-only">Email</label>
                    <input id="email" name="email" type="email" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                           placeholder="Email" />
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                           placeholder="Password (min 6 karakter)" />
                </div>
            </div>
            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Daftar Guru
                </button>
            </div>
        </form>
        <div class="text-center">
            <a href="index.php" class="text-primary hover:text-blue-500">Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
