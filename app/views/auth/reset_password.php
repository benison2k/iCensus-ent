<?php
// app/views/auth/reset_password.php
$base_url = '/iCensus-ent/public';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>iCensus - Reset Password</title>
<link rel="icon" type="image/png" href="<?= $base_url ?>/assets/img/iCensusLogoOnly2.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/login.css">
</head>
<body>

<div class="split-screen">
    <div class="left-side position-relative d-flex flex-column justify-content-center align-items-center text-center overflow-hidden">
        <a href="<?= $base_url ?>/login" class="home-link" title="Back to Login">
            <span class="material-icons">arrow_back</span>
        </a>
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
        <canvas id="particleCanvas"></canvas>
        <img src="<?= $base_url ?>/assets/img/iCensusLogo.png" alt="iCensus Logo" class="hero-logo mb-3">
        <p class="hero-subtitle">New Password Setup</p>
    </div>
    <div class="divider-shadow"></div>
    <div class="right-side d-flex justify-content-center align-items-center">
        <div class="login-card">
            <div class="card-header text-center mb-3">
                <h1 class="hero-title">Set New Password</h1>
                <p class="text-muted">Enter a new password for <?= htmlspecialchars($email) ?>.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-text" id="errorMessage" style="margin-bottom: 1rem;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): // Show success message and login link ?>
                <div class="error-text" style="color: green; margin-bottom: 1rem;"><?= htmlspecialchars($success) ?>. <a href="<?= $base_url ?>/login">Go to Login</a></div>
            <?php endif; ?>

            <?php if (empty($success)): // Only show form if the reset was not successful ?>
            <form id="resetForm" method="POST" action="<?= $base_url ?>/password/reset">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="input-wrapper mb-3">
                    <input type="password" name="password" id="passwordField" class="form-control" placeholder="New Password (min 6 chars)" required autofocus minlength="6">
                    <span class="material-icons password-toggle" id="togglePassword1">visibility_off</span>
                </div>
                 <div class="input-wrapper mb-3">
                    <input type="password" name="confirm_password" id="confirmPasswordField" class="form-control" placeholder="Confirm New Password" required minlength="6">
                    <span class="material-icons password-toggle" id="togglePassword2">visibility_off</span>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-2" id="resetBtn">Reset Password</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?= $base_url ?>/assets/js/particle_animation.js"></script>
<script src="<?= $base_url ?>/assets/js/reset_password.js"></script>

</body>
</html>