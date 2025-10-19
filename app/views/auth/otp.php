<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enter OTP</title>
    <link rel="icon" type="image/png" href="/iCensus-ent/public/assets/img/iCensusLogoOnly2.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="/iCensus-ent/public/assets/css/login.css">
</head>
<body>
    <div class="split-screen">
        <div class="left-side position-relative d-flex flex-column justify-content-center align-items-center text-center overflow-hidden">
            <a href="/iCensus-ent/public/home" class="home-link" title="Back to Home">
                <span class="material-icons">home</span>
            </a>
            <div class="shape shape1"></div>
            <div class="shape shape2"></div>
            <div class="shape shape3"></div>
            <canvas id="particleCanvas"></canvas>
            <img src="/iCensus-ent/public/assets/img/iCensusLogo.png" alt="iCensus Logo" class="hero-logo mb-3">
            <p class="hero-subtitle">Accurate. Fast. Reliable.</p>
        </div>
        <div class="right-side d-flex justify-content-center align-items-center">
            <div class="login-card">
                <div class="card-header text-center mb-3">
                    <h1 class="hero-title">Enter OTP</h1>
                    <p class="text-muted">An OTP has been sent to your email.</p>
                </div>
                <form method="POST" action="/iCensus-ent/public/otp/verify">
                    <div class="input-wrapper mb-3">
                        <input type="text" name="otp" class="form-control" placeholder="6-digit code" required autofocus>
                    </div>
                    <?php if (isset($error)): ?>
                        <div class="error-text"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary w-100 mb-2">Verify</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>