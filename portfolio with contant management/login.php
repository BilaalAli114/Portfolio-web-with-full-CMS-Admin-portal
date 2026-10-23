<?php
// login.php - Highly Secured Admin Login Gateway
declare(strict_types=1);

// Enforce secure session cookie settings before starting session
$is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $is_secure,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

define('BASE_URL', 'https://hassansheekhali.kesug.com/');

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: " . BASE_URL . "admin.php");
    exit();
}

$db_host = '';
$db_name = '';
$db_user = '';
$db_pass = '';

$error_msg = '';

// Brute-Force Protection Initialization
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_time'] = 0;
}

// Check if locked out (15-minute block after 5 failed attempts)
$max_attempts = 5;
$lockout_duration = 900; // 15 minutes in seconds

if ($_SESSION['login_attempts'] >= $max_attempts) {
    $time_left = ($_SESSION['lockout_time'] + $lockout_duration) - time();
    if ($time_left > 0) {
        $minutes_left = ceil($time_left / 60);
        $error_msg = "Too many failed login attempts. Please try again in $minutes_left minutes.";
    } else {
        // Reset after lockout expires
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_time'] = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error_msg)) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? ''; // Do not trim passwords as leading/trailing spaces can be intentional

    if ($username !== '' && $password !== '') {
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);

            // Prepared statement prevents SQL Injection
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // Secure Password Verification (Constant time comparison against timing attacks using password_verify)
            $login_success = false;
            if ($user && password_verify($password, $user['password'])) {
                $login_success = true;
                
                // Check if password hash needs re-hashing (if algorithm cost changed)
                if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update_stmt->execute([$new_hash, $user['id']]);
                }
            }

            if ($login_success) {
                // Clear brute-force counter on success
                $_SESSION['login_attempts'] = 0;
                $_SESSION['lockout_time'] = 0;

                // Prevent Session Fixation attacks
                session_regenerate_id(true);

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];

                header("Location: " . BASE_URL . "admin.php");
                exit();
            } else {
                // Increment failed attempts and use generic error message to prevent user enumeration
                $_SESSION['login_attempts']++;
                if ($_SESSION['login_attempts'] >= $max_attempts) {
                    $_SESSION['lockout_time'] = time();
                    $error_msg = "Too many failed login attempts. Account temporarily locked for 15 minutes.";
                } else {
                    $error_msg = "Invalid username or password.";
                }
            }
        } catch (PDOException $e) {
            // Log real error internally if needed, show generic message externally
            $error_msg = "A system error occurred. Please try again later.";
        }
    } else {
        $error_msg = "Please enter both username and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Prof. Hassan Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-white font-sans min-h-screen flex items-center justify-center p-4">

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 max-w-md w-full shadow-2xl">
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-400 inline-flex items-center justify-center text-2xl font-bold mb-3">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h2 class="text-2xl font-bold font-serif text-white">Admin Portal Login</h2>
            <p class="text-xs text-slate-400 mt-1">Prof. Hassan Sheikh Ali Hilowle Portfolio</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="bg-rose-500/20 border border-rose-500/50 text-rose-300 text-xs p-3.5 rounded-xl mb-6 text-center font-semibold">
                <i class="fa-solid fa-circle-exclamation mr-1.5"></i> <?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars(BASE_URL . 'login.php', ENT_QUOTES, 'UTF-8'); ?>" class="space-y-4 text-xs" autocomplete="off">
            <div>
                <label class="block font-bold uppercase text-slate-300 mb-1">Username</label>
                <input type="text" name="username" required maxlength="50" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-amber-500">
            </div>
            <div>
                <label class="block font-bold uppercase text-slate-300 mb-1">Password</label>
                <input type="password" name="password" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-amber-500">
            </div>
            <button type="submit" <?php echo ($_SESSION['login_attempts'] >= $max_attempts && time() < ($_SESSION['lockout_time'] + $lockout_duration)) ? 'disabled class="w-full bg-slate-700 text-slate-400 font-bold py-3.5 rounded-lg uppercase tracking-wider cursor-not-allowed"' : 'class="w-full bg-amber-500 hover:bg-amber-600 font-bold py-3.5 rounded-lg text-slate-950 uppercase tracking-wider transition-colors"'; ?>>
                Sign In to Dashboard
            </button>
        </form>

        <div class="text-center mt-6 pt-4 border-t border-slate-800">
            <a href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>" class="text-slate-400 hover:text-white text-xs"><i class="fa-solid fa-arrow-left mr-1"></i> Return to Portfolio Website</a>
        </div>
    </div>

</body>
</html>