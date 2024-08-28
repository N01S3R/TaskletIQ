<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo getenv('BASE_URL'); ?>css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lobibox/dist/css/lobibox.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lobibox/dist/js/lobibox.min.js"></script>
    <link rel="stylesheet" href="<?php echo getenv('BASE_URL'); ?>css/bootstrap.min.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-primary">
        <div class="container">
            <a class="navbar-brand text-white p-0" href="/"><img src="/images/logo.png" alt="User Avatar" style="height: 64px;"></a>
            <ul class="navbar-nav ml-auto bg-dark rounded">
                <li class="nav-item dropdown d-none d-md-block">
                    <a class="nav-link dropdown-toggle text-white fs-5" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-container">
                            <img src="/images/gosc.png" alt="User Avatar" class="avatar">
                        </div>
                        Cześ, gość
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item fs-6" href="/"><i class="bi bi-house-door me-2 fs-5"></i> Strona główna</a></li>
                        <li><a class="dropdown-item fs-6" href="/login"><i class="bi bi-box-arrow-in-right me-2 fs-5"></i> Logowanie</a></li>
                        <li><a class="dropdown-item fs-6" href="/register"><i class="bi bi-person-plus me-2 fs-5"></i> Rejestracja</a></li>
                    </ul>
                </li>
            </ul>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/images/gosc.png" alt="Admin" class="rounded-circle p-1 bg-primary" width="110">
                        <div class="mt-3">
                            <h4>Gość</h4>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="text-center">
                        <div class="d-grid gap-2">
                            <a href="/" class="btn btn-primary btn-lg">Strona główna</a>
                            <a href="/login" class="btn btn-primary btn-lg">Logowanie</a>
                            <a href="/register" class="btn btn-primary btn-lg">Rejestracja</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>