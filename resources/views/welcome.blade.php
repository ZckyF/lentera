<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LENTERA</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark shadow-sm" style="background-color: #1A237E;">
        <div class="container">
            <span class="navbar-brand mb-0 h1">LENTERA</span>
            <span class="navbar-text text-white">
                {{ auth()->user()->name }}
            </span>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="card border-0 shadow-sm p-5" style="border-radius: 20px;">
                    <h1 class="display-4 fw-bold" style="color: #1A237E;">
                        Selamat Datang,
                    </h1>
                    <h2 class="text-secondary mb-4">
                        {{ auth()->user()->name }}
                    </h2>
                    <p class="lead text-muted">
                        Akun Anda telah aktif. Siap untuk memulai percakapan dengan LENTERA AI?
                    </p>
                    <div class="mt-4">
                        <button class="btn btn-primary btn-lg px-5" style="background-color: #1A237E; border: none; border-radius: 10px;">
                            <i class="bi bi-chat-dots me-2"></i> Mulai Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>