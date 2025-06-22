<?php
require_once __DIR__ . '/../../src/config/config.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= APP_NAME ?></title>
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
        <h2>Create an Account</h2>
        <div id="api-message"></div>
        <form id="registerForm" class="auth-form" autocomplete="off">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Register" class="btn-primary">
        </form>
        <p class="switch-link">Already have an account?
            <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<script src="../../public/assets/js/responsive.js"></script>
<script>
document.getElementById('registerForm').onsubmit = async function(e) {
    e.preventDefault();
    const form = e.target;
    const data = {
        name: form.name.value,
        email: form.email.value,
        password: form.password.value
    };
    const msgDiv = document.getElementById('api-message');
    msgDiv.innerHTML = '';
    try {
        const res = await fetch('../../src/api/auth.php?action=register', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            msgDiv.innerHTML = '<p class="success">Account created! You can now log in.</p>';
            setTimeout(() => window.location.href = 'login.php', 1200);
        } else {
            let errorMsg = 'Registration failed. Please try again.';
            if (result.error === 'empty') errorMsg = 'Please fill in all fields.';
            else if (result.error === 'email_taken') errorMsg = 'Email already in use. Please use a different one.';
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



