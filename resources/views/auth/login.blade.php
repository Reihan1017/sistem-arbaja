<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — TB. AR Baja Steelindo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #1c3faa, #0d1b40);
        }
        .login-card { max-width: 400px; width: 100%; }
        .login-card .card { border: none; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,.25); }
        .logo-box {
            width: 56px; height: 56px; border-radius: 10px; margin: 0 auto 10px;
            background: #1c3faa; color: #fff; text-align: center;
            font-weight: bold; font-size: 20px; line-height: 56px;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="logo-box">AR</div>
                <h5 class="fw-bold mb-0">TB. AR Baja Steelindo</h5>
                <small class="text-muted">Sistem Dokumen & Invoice</small>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger py-2 small">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label small" for="remember">Ingat saya</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
