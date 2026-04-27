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
    <!-- Fonts: Montserrat & Cormorant Garamond -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: #0a0806;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        /* Subtle gold pattern overlay */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(circle at 10% 20%, rgba(212, 175, 55, 0.03) 0%, transparent 50%);
            pointer-events: none;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1300px;
            z-index: 2;
        }

        .login-container {
            display: flex;
            flex-wrap: wrap;
            background: #0f0e0c;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(212, 175, 55, 0.2);
            backdrop-filter: blur(2px);
        }

        /* LEFT SIDE - FORM */
        .login-form {
            flex: 1;
            padding: 56px 48px;
            background: #11100e;
        }

        .brand-head {
            margin-bottom: 32px;
            border-left: 3px solid #d4af37;
            padding-left: 20px;
        }

        .brand-head h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: 1px;
            color: #d4af37;
            margin-bottom: 8px;
        }

        .brand-head h1 i {
            font-size: 1.8rem;
            margin-right: 10px;
            color: #d4af37;
        }

        .brand-head p {
            font-size: 0.85rem;
            color: #a18f6e;
            letter-spacing: 1px;
            font-weight: 300;
            margin-top: 6px;
        }

        .login-form h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 600;
            color: #ece8df;
            margin: 20px 0 8px;
            letter-spacing: -0.5px;
        }

        .login-form h2::after {
            content: "";
            display: block;
            width: 50px;
            height: 2px;
            background: #d4af37;
            margin-top: 12px;
        }

        .login-form p {
            color: #9b8e74;
            font-size: 0.9rem;
            margin-bottom: 32px;
            font-weight: 300;
        }

        .error-msg {
            background: rgba(180, 30, 30, 0.15);
            border-left: 3px solid #d4af37;
            padding: 14px 18px;
            margin-bottom: 28px;
            color: #f0cf8b;
            font-size: 0.85rem;
            font-weight: 400;
            backdrop-filter: blur(4px);
        }

        .error-msg i {
            color: #d4af37;
            margin-right: 8px;
        }

        .input-group {
            margin-bottom: 28px;
        }

        .input-group label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 500;
            color: #c6b27a;
            margin-bottom: 10px;
        }

        .input-group label i {
            width: 24px;
            color: #d4af37;
            font-size: 0.8rem;
        }

        .input-group input {
            width: 100%;
            padding: 14px 18px;
            background: #1c1916;
            border: 1px solid #2c2822;
            border-radius: 12px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.95rem;
            color: #f0e6d2;
            transition: all 0.25s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
            background: #201e1a;
        }

        /* Password wrapper */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 50px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #9b8e74;
            cursor: pointer;
            font-size: 1.1rem;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: 0.2s;
        }

        .toggle-password:hover {
            color: #d4af37;
            background: #2a241e;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(95deg, #d4af37 0%, #b88b1a 100%);
            border: none;
            padding: 14px 20px;
            border-radius: 40px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 1px;
            color: #0a0806;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .login-btn:hover {
            background: linear-gradient(95deg, #e2bc44 0%, #c49b2a 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 22px -8px rgba(212, 175, 55, 0.3);
        }

        .footer-note {
            text-align: center;
            margin-top: 38px;
            font-size: 0.7rem;
            color: #7c6e52;
            border-top: 1px solid #23201c;
            padding-top: 24px;
        }

        .footer-note i {
            color: #d4af37;
            margin-right: 6px;
        }

        /* RIGHT SIDE - LUXURY INFO PANEL */
        .login-info {
            flex: 1;
            background: #0c0b09;
            padding: 56px 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            border-left: 1px solid rgba(212, 175, 55, 0.2);
        }

        .info-card {
            background: #13110e;
            border-radius: 28px;
            padding: 40px 32px;
            text-align: center;
            width: 100%;
            max-width: 340px;
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(212, 175, 55, 0.25);
        }

        .info-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(145deg, #1e1b16, #0c0a07);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 1px solid #d4af37;
            box-shadow: 0 0 12px rgba(212, 175, 55, 0.2);
        }

        .info-icon i {
            font-size: 36px;
            color: #d4af37;
        }

        .info-card h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.7rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: #ecd89e;
        }

        .info-card p {
            color: #9b8d6f;
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 28px;
            font-weight: 300;
        }

        .features {
            text-align: left;
            margin: 22px 0 28px;
            background: #191715;
            padding: 18px 20px;
            border-radius: 20px;
        }

        .features div {
            margin-bottom: 14px;
            font-size: 0.8rem;
            font-weight: 400;
            color: #ddceaa;
        }

        .features i {
            width: 28px;
            color: #d4af37;
            margin-right: 12px;
            text-align: center;
        }

        .badge {
            background: #1f1b16;
            padding: 8px 16px;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 500;
            color: #d4af37;
            display: inline-block;
            letter-spacing: 1px;
            border: 1px solid rgba(212, 175, 55, 0.4);
        }

        .badge i {
            margin-right: 6px;
            font-size: 0.7rem;
        }

        /* RESPONSIVE */
        @media (max-width: 1000px) {
            .login-form, .login-info {
                padding: 40px 32px;
            }
        }

        @media (max-width: 850px) {
            .login-container {
                flex-direction: column;
            }
            .login-info {
                border-left: none;
                border-top: 1px solid rgba(212, 175, 55, 0.2);
            }
            .brand-head h1 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .login-form {
                padding: 32px 20px;
            }
            .login-form h2 {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <!-- Left Side: Form Elegant -->
            <div class="login-form">
                <div class="brand-head">
                    <h1><i class="fas fa-crown"></i> Tabrani Guest House</h1>
                    <p>• Menginap Sesuai Waktu Anda •</p>
                </div>
                <h2>Admin Access</h2>
                <p>Masukkan kredensial Anda untuk mengelola pemesanan</p>

                <?php if (isset($error)): ?>
                    <div class="error-msg">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group">
                        <label><i class="fas fa-user-circle"></i> USERNAME</label>
                        <input type="text" name="username" placeholder="admin" required autofocus>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-key"></i> PASSWORD</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" name="login" class="login-btn">
                        <i class="fas fa-arrow-right-to-bracket"></i> LOGIN
                    </button>
                </form>

                <div class="footer-note">
                    <p><i class="fas fa-shield-alt"></i> Area terbatas · Hanya untuk manajemen resmi</p>
                </div>
            </div>

            <!-- Right Side: Info Elegan -->
            <div class="login-info">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Manajemen Reservasi</h3>
                    <p>Kelola pemesanan, konfirmasi pembayaran, dan pantau okupansi secara eksklusif.</p>
                    <div class="features">
                        <div><i class="fas fa-chart-simple"></i> Laporan Real-time</div>
                        <div><i class="fab fa-whatsapp"></i> Notifikasi Terintegrasi</div>
                        <div><i class="fas fa-concierge-bell"></i> Layanan 24 Jam</div>
                    </div>
                    <div class="badge">
                        <i class="fas fa-gem"></i> TABRANI SIGNATURE
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