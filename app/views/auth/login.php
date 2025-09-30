<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>iCensus Login</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/login.css">
</head>
<body>

<div class="split-screen">
    <div class="left-side position-relative d-flex flex-column justify-content-center align-items-center text-center overflow-hidden">
        <a href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/" class="home-link" title="Back to Home">
            <span class="material-icons">home</span>
        </a>
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
        <canvas id="particleCanvas"></canvas>
        <img src="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/img/iCensusLogo.png" alt="iCensus Logo" class="hero-logo mb-3">
        <p class="hero-subtitle">Accurate. Fast. Reliable.</p>
    </div>
    <div class="divider-shadow"></div>
    <div class="right-side d-flex justify-content-center align-items-center">
        <div class="login-card">
            <div class="card-header text-center mb-3">
                <h1 class="hero-title">Sign in</h1>
                <p class="text-muted">Please enter your credentials</p>
            </div>
            <form method="POST" action="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/login" novalidate>
                <div class="input-wrapper mb-3">
                    <input type="text" name="username" class="form-control <?= $error ? 'error' : '' ?>" placeholder="Username" value="<?= $usernameValue ?>" autofocus>
                    <span class="material-icons input-icon">person</span>
                </div>
                <div class="input-wrapper mb-3">
                    <input type="password" name="password" id="password" class="form-control <?= $error ? 'error' : '' ?>" placeholder="Password">
                    <span class="material-icons password-toggle" id="togglePassword">visibility_off</span>
                </div>
                <?php if ($error): ?>
                    <div class="error-text"><?= $error ?></div>
                <?php endif; ?>
                <button class="btn btn-primary w-100 mb-2">Login</button>
                <div class="text-center">
                    <a href="#" class="small text-decoration-none">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    // (Your existing JavaScript from login.php goes here)
</script>
</body>
</html>