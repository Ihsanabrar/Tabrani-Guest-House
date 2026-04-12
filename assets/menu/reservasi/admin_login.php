<?php
session_start();
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    if ($user === 'admin' && $pass === 'tabrani123') {
        $_SESSION['admin_logged'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Tabrani Guest House</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* RESET TOTAL UNTUK HALAMAN INI SAJA */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1200px;
        }

        .login-container {
            display: flex;
            flex-wrap: wrap;
            background: #ffffff;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 20px 35px -8px rgba(0, 0, 0, 0.2);
        }

        /* LEFT SIDE - FORM */
        .login-form {
            flex: 1;
            padding: 48px 40px;
            background: white;
        }

        .login-form h1 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .login-form h1 i {
            color: #667eea;
            margin-right: 8px;
        }
        .login-form h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f172a;
            margin: 16px 0 8px;
        }
        .login-form p {
            color: #475569;
            font-size: 0.9rem;
            margin-bottom: 32px;
        }

        .error-msg {
            background: #fef2f2;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            color: #b91c1c;
            font-size: 0.85rem;
        }

        .input-group {
            margin-bottom: 24px;
        }
        .input-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .input-group label i {
            color: #667eea;
            width: 24px;
        }
        .input-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            transition: 0.2s;
        }
        .input-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* PASSWORD WRAPPER + TOGGLE - RAPI */
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 48px;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 1.2rem;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: 0.2s;
        }
        .toggle-password:hover {
            color: #667eea;
            background: #f1f5f9;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(102, 126, 234, 0.4);
        }

        .footer-note {
            text-align: center;
            margin-top: 32px;
            font-size: 0.8rem;
            color: #64748b;
        }
        .footer-note i {
            color: #667eea;
            margin-right: 6px;
        }

        /* RIGHT SIDE - INFO CARD */
        .login-info {
            flex: 1;
            background: #f8fafc;
            padding: 48px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .info-card {
            background: white;
            border-radius: 28px;
            padding: 32px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            text-align: center;
            width: 100%;
            max-width: 320px;
        }
        .info-icon {
            width: 70px;
            height: 70px;
            background: #eef2ff;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .info-icon i {
            font-size: 32px;
            color: #667eea;
        }
        .info-card h3 {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: #0f172a;
        }
        .info-card p {
            color: #475569;
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .features {
            text-align: left;
            margin: 20px 0;
            background: #f8fafc;
            padding: 16px;
            border-radius: 20px;
        }
        .features div {
            margin-bottom: 12px;
            font-size: 0.85rem;
            color: #1e293b;
        }
        .features i {
            width: 28px;
            color: #667eea;
            margin-right: 8px;
        }
        .badge {
            background: #eef2ff;
            padding: 10px;
            border-radius: 40px;
            font-size: 0.75rem;
            color: #667eea;
            font-weight: 500;
        }
        .badge i {
            margin-right: 6px;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .login-container {
                flex-direction: column;
            }
            .login-form, .login-info {
                padding: 32px 24px;
            }
            .login-info {
                background: white;
            }
        }
        @media (max-width: 480px) {
            .login-form {
                padding: 24px 20px;
            }
            .login-form h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <!-- Left Side: Form -->
            <div class="login-form">
                <h1><i class="fas fa-building"></i> Tabrani Guest House</h1>
                <h2>Admin Access</h2>
                <p>Masukkan kredensial Anda untuk mengelola pemesanan</p>

                <?php if (isset($error)): ?>
                    <div class="error-msg">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group">
                        <label><i class="fas fa-user"></i> Username</label>
                        <input type="text" name="username" placeholder="admin" required autofocus>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" name="login" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <div class="footer-note">
                    <p><i class="fas fa-shield-alt"></i> Area terbatas · Hanya untuk operator</p>
                </div>
            </div>

            <!-- Right Side: Info Card -->
            <div class="login-info">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Manajemen Pemesanan</h3>
                    <p>Kelola reservasi, konfirmasi pembayaran, dan lihat statistik penginapan dengan mudah.</p>
                    <div class="features">
                        <div><i class="fas fa-bed"></i> 8 Kamar Premium</div>
                        <div><i class="fas fa-chart-line"></i> Laporan Real-time</div>
                        <div><i class="fas fa-whatsapp"></i> Notifikasi WhatsApp</div>
                    </div>
                    <div class="badge">
                        <i class="fas fa-crown"></i> Tabrani Guest House
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        toggleBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    </script>
</body>
</html>