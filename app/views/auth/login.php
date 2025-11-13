<?php
// app/views/auth/login.php
$base_url = '/iCensus-ent/public';
$error = $data['error'] ?? '';
$usernameValue = $data['usernameValue'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>iCensus Login</title>
<link rel="icon" type="image/png" href="<?= $base_url ?>/assets/img/iCensusLogoOnly2.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/login.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/modal.css">
</head>
<body>

<div class="split-screen">
    <div class="left-side position-relative d-flex flex-column justify-content-center align-items-center text-center overflow-hidden">
        <a href="<?= $base_url ?>/home" class="home-link" title="Back to Home">
            <span class="material-icons">home</span>
        </a>
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
        <canvas id="particleCanvas"></canvas>
        <img src="<?= $base_url ?>/assets/img/iCensusLogo.png" alt="iCensus Logo" class="hero-logo mb-3">
        <p class="hero-subtitle">Accurate. Fast. Reliable.</p>
    </div>
    <div class="divider-shadow"></div>
    <div class="right-side d-flex justify-content-center align-items-center">
        <div class="login-card">
            <div class="card-header text-center mb-3">
                <h1 class="hero-title">Sign in</h1>
                <p class="text-muted">Please enter your credentials</p>
            </div>

            <?php
                if (isset($_SESSION['timeout_message'])) {
                    echo '<div class="error-text" id="timeoutMessage" style="color: #0d6efd; margin-bottom: 1rem;">' . htmlspecialchars($_SESSION['timeout_message']) . '</div>';
                    unset($_SESSION['timeout_message']);
                }
            ?>

            <form id="loginForm" method="POST" action="<?= $base_url ?>/login">
                <?= Csrf::getField(); ?>

                <div class="input-wrapper mb-3">
                    <input type="text" name="username" id="usernameInput" class="form-control <?= $error ? 'error' : '' ?>" placeholder="Username" value="<?= $usernameValue ?>" autofocus>
                    <span class="material-icons input-icon">person</span>
                </div>
                <div class="input-wrapper mb-3">
                    <input type="password" name="password" id="passwordField" class="form-control <?= $error ? 'error' : '' ?>" placeholder="Password">
                    <span class="material-icons password-toggle" id="togglePassword">visibility_off</span>
                </div>
                <div class="error-text" id="loginError" style="margin-bottom: 1rem;"><?= $error ?></div>
                <button type="submit" class="btn btn-primary w-100 mb-2" id="loginBtn">Login</button>
                <div class="text-center">
                    <a href="<?= $base_url ?>/password/forgot" class="small text-decoration-none">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="otpModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" id="closeOtpModal">&times;</span>
        <h3 style="margin-top: 0;">Two-Factor Authentication</h3>
        <p class="text-muted">A 6-digit code has been sent to your registered email.</p>
        
        <form id="otpForm" action="<?= $base_url ?>/verify-otp" method="POST" style="margin-top: 1.5rem; margin-bottom: 1rem;">
            <div class="otp-container" id="otpLoginContainer">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
            </div>
            <input type="hidden" name="otp" id="otpInput" required>
            <div class="error-text" id="otpError" style="margin-bottom: 1rem;"></div>
            <button type="submit" class="btn btn-primary w-100 mb-3" id="otpVerifyBtn">Verify Code</button>
        </form>
        
        <a href="#" id="resendOtpBtn" class="small text-decoration-none" style="display: none;">Resend Code</a>
        <span id="cooldownTimer" class="small text-muted" style="margin-top: 5px; display: none;"></span>
    </div>
</div>

<script>
    const BASE_URL = '<?= $base_url ?>';
</script>
<script src="<?= $base_url ?>/assets/js/login.js"></script>
<script src="<?= $base_url ?>/assets/js/particle_animation.js"></script>

</body>
</html>