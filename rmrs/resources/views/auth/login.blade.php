<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — RMRS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #6C63FF;
            --primary-dark: #5A52E0;
            --primary-glow: rgba(108, 99, 255, 0.35);
            --secondary: #00D4AA;
            --bg: #0A0A1A;
            --bg-card: #111128;
            --bg-elevated: #181830;
            --border: rgba(108, 99, 255, 0.2);
            --text-primary: #E8E8F5;
            --text-secondary: #8888AA;
            --danger: #EF4444;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(108,99,255,0.12) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            border-radius: 50%;
            animation: pulse 8s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: fixed;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(0,212,170,0.08) 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            border-radius: 50%;
            animation: pulse 10s ease-in-out infinite reverse;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
        }

        /* Grid background */
        .bg-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(108,99,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(108,99,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 0;
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        /* Brand header */
        .login-brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin: 0 auto 16px;
            box-shadow: 0 0 40px var(--primary-glow);
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .brand-name {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 1px;
        }

        .brand-desc {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        /* Login card */
        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 0 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(108,99,255,0.08);
            backdrop-filter: blur(10px);
        }

        .login-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .login-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 28px;
        }

        .form-group { margin-bottom: 20px; }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: var(--text-secondary);
            font-size: 14px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: all 0.2s;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
            background: var(--bg-card);
        }

        .form-input:focus + .input-icon,
        .input-wrapper:focus-within .input-icon {
            color: var(--primary);
        }

        .toggle-pw {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 14px;
            transition: color 0.2s;
            padding: 4px;
        }
        .toggle-pw:hover { color: var(--primary); }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .checkbox-custom {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .remember-label { font-size: 13px; color: var(--text-secondary); cursor: pointer; }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            box-shadow: 0 4px 20px var(--primary-glow);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px var(--primary-glow);
        }

        .btn-login:active { transform: translateY(0); }

        .error-alert {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            color: var(--danger);
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Demo accounts */
        .demo-accounts {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .demo-title { font-size: 11px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; text-align: center; margin-bottom: 12px; }

        .demo-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }

        .demo-card {
            padding: 10px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }

        .demo-card:hover { border-color: var(--primary); background: rgba(108,99,255,0.1); }

        .demo-role { font-size: 11px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; }
        .demo-email { font-size: 10px; color: var(--text-secondary); margin-top: 2px; word-break: break-all; }

        .footer-text { text-align: center; font-size: 11px; color: var(--text-muted, #555577); margin-top: 20px; }
    </style>
</head>
<body>
<div class="bg-grid"></div>

<div class="login-container">
    <div class="login-brand">
        <div class="brand-logo"><i class="fas fa-chart-line"></i></div>
        <div class="brand-name">RMRS</div>
        <div class="brand-desc">Reject Material Reporting System</div>
    </div>

    <div class="login-card">
        <div class="login-title">Selamat Datang 👋</div>
        <div class="login-subtitle">Silakan masuk untuk melanjutkan</div>

        @if($errors->any())
            <div class="error-alert">
                <i class="fas fa-exclamation-circle"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="email@perusahaan.com" required autofocus>
                    <i class="fas fa-envelope input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                    <i class="fas fa-lock input-icon"></i>
                    <button type="button" class="toggle-pw" onclick="togglePw()">
                        <i class="fas fa-eye" id="pwIcon"></i>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember" class="checkbox-custom">
                <label for="remember" class="remember-label">Ingat saya</label>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Masuk ke Sistem
            </button>
        </form>

        <div class="demo-accounts">
            <div class="demo-title">Akun Demo — Klik untuk isi otomatis</div>
            <div class="demo-cards">
                <div class="demo-card" onclick="fillLogin('supervisor@rmrs.com')">
                    <div class="demo-role">Supervisor</div>
                    <div class="demo-email">supervisor@rmrs.com</div>
                </div>
                <div class="demo-card" onclick="fillLogin('leader@rmrs.com')">
                    <div class="demo-role">Line Leader</div>
                    <div class="demo-email">leader@rmrs.com</div>
                </div>
                <div class="demo-card" onclick="fillLogin('operator1@rmrs.com')">
                    <div class="demo-role">Operator</div>
                    <div class="demo-email">operator1@rmrs.com</div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-text">© {{ date('Y') }} RMRS — Sistem Pelaporan Material Reject</div>
</div>

<script>
function togglePw() {
    const pw = document.getElementById('password');
    const icon = document.getElementById('pwIcon');
    pw.type = pw.type === 'password' ? 'text' : 'password';
    icon.className = pw.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

function fillLogin(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password';
    document.getElementById('loginBtn').style.background = 'linear-gradient(135deg, #00D4AA, #0ea574)';
    setTimeout(() => document.getElementById('loginBtn').style.background = '', 500);
}
</script>
</body>
</html>
