<?php
require_once __DIR__ . '/../../src/config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        .auth-form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .auth-form input {
            max-width: 100%;
        }
        .switch-link {
            text-align: center;
        }
        .error, .success {
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<?php include_once '../../public/includes/auth_header.php'; ?>

<div class="auth-page">
    <div class="auth-card">
        <h2>Welcome Back</h2>

        <div id="api-message"></div>

        <form id="loginForm" class="auth-form">
            <input type="email" name="email" required placeholder="Email">
            <input type="password" name="password" required placeholder="Password">
            <button type="submit" class="btn-primary">Login</button>
        </form>

        <p class="switch-link">Don't have an account?
            <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<script src="../../public/assets/js/responsive.js"></script>
<script>
document.getElementById('loginForm').onsubmit = async function(e) {
    e.preventDefault();
    const form = e.target;
    const data = {
        email: form.email.value,
        password: form.password.value
    };
    const msgDiv = document.getElementById('api-message');
    msgDiv.innerHTML = '';

    try {
        const res = await fetch('/REM/src/api/auth.php?action=login', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });

        const text = await res.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (err) {
            msgDiv.innerHTML = '<p class="error">Invalid server response:<br>' + text + '</p>';
            return;
        }

        if (result.success) {
            msgDiv.innerHTML = '<p class="success">Login successful! Redirecting...</p>';
            setTimeout(() => window.location.href = 'dashboard.php', 1000);
        } else {
            let errorMsg = 'Login failed. Please try again.';
            if (result.error === 'empty') errorMsg = 'Please fill in all fields.';
            else if (result.error === 'notfound') errorMsg = 'No account found with this email.';
            else if (result.error === 'invalid') errorMsg = 'Incorrect password. Please try again.';
            else if (result.error === 'email_taken') errorMsg = 'Email already in use.';
            else if (result.error === 'database') errorMsg = 'A database error occurred. Please try again later.';
            msgDiv.innerHTML = '<p class="error">' + errorMsg + '</p>';
        }

    } catch (err) {
        msgDiv.innerHTML = '<p class="error">Network error. Please try again.</p>';
    }
};
</script>
</body>
</html>
