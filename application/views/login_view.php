<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-red: #E01020;
            --dark-red: #C02020;
            --black: #000000;
            --dark-gray: #111111;
            --card-bg: #1A1A1A;
            --white-text: #FFFFFF;
            --border-color: #333333;
        }

        body {
            background: linear-gradient(135deg, var(--dark-gray) 0%, var(--black) 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white-text);
        }

        .login-wrapper {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-logo-icon {
            font-size: 60px;
            color: var(--primary-red);
            margin-bottom: 15px;
            display: block;
        }

        .login-logo h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-logo p {
            font-size: 13px;
            color: #999;
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--white-text);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            color: var(--white-text);
            transition: all 0.3s;
            width: 100%;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(224, 16, 32, 0.1);
            outline: none;
            color: var(--white-text);
        }

        .form-control:hover {
            border-color: var(--primary-red);
        }

        .input-group {
            position: relative;
        }

        .input-group .form-control {
            padding-left: 42px;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-red);
            font-size: 15px;
            pointer-events: none;
            z-index: 2;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
            font-size: 15px;
            transition: color 0.2s;
            z-index: 10;
        }

        .password-toggle:hover {
            color: var(--primary-red);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--dark-red) 100%);
            color: #fff;
            border: none;
            padding: 13px 20px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 8px;
            width: 100%;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
            box-shadow: 0 5px 20px rgba(224, 16, 32, 0.3);
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(224, 16, 32, 0.5);
        }

        .alert {
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: rgba(224, 16, 32, 0.1);
            color: #ff6b6b;
            border: 1px solid rgba(224, 16, 32, 0.3);
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: #81c784;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .login-footer p {
            font-size: 13px;
            color: #999;
            margin: 0;
        }

        .login-footer a {
            color: var(--primary-red);
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            color: var(--dark-red);
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }

            .login-logo h1 {
                font-size: 22px;
            }

            .login-logo-icon {
                font-size: 48px;
            }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-card">
           <div class="login-logo">
    <img src="<?php echo base_url('uploads/ghanshyam-murti-logo-dark-bg.png'); ?>" alt="Ghanshyam Murti Bhandar" class="login-logo-img">
    <!-- <h1>Welcome Back</h1> -->
    <p>Sign in to your admin panel</p>
</div>
<style>
    .login-logo {
    text-align: center;
}

.login-logo-img {
    max-width: 260px;
    width: 100%;
    height: auto;
    display: block;
    margin: 0 auto 15px;
}
</style>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= site_url('admin') ?>">

                <input type="hidden"
                    name="<?= $this->security->get_csrf_token_name(); ?>"
                    value="<?= $this->security->get_csrf_hash(); ?>">

                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter your email"
                            value="<?= isset($_POST['email']) ? $_POST['email'] : '' ?>"
                            required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" id="passwordField" placeholder="Enter your password" required>
                        <i class="fas fa-eye password-toggle" id="pwdToggle" onclick="togglePwd()"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i> Sign In
                </button>
            </form>

            <div class="login-footer">
                <p>Don't have an account? <a href="<?= site_url('register') ?>">Register here</a></p>
            </div>
        </div>
    </div>
    <script>
        function togglePwd() {
            var f = document.getElementById('passwordField');
            var i = document.getElementById('pwdToggle');
            if (f.type === 'password') {
                f.type = 'text';
                i.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                f.type = 'password';
                i.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>

</html>
