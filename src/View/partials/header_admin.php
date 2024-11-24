<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $_ENV["APP_NAME"]; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $_ENV["BASE_URL"]; ?>css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lobibox/dist/css/lobibox.min.css" />
    <link rel="stylesheet" href="<?= $_ENV["BASE_URL"]; ?>css/datatables.css" />
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lobibox/dist/js/lobibox.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/perfect-scrollbar/css/perfect-scrollbar.css">
    <script src="https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.5/dist/perfect-scrollbar.min.js"></script>
    <link rel="stylesheet" href="<?= $_ENV["BASE_URL"]; ?>css/bootstrap.min.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-primary">
        <div class="container">
            <a class="navbar-brand text-white p-0" href="/"><img src="/images/logo.png" alt="User Avatar" style="height: 64px;"></a>
            <ul class="navbar-nav ml-auto bg-dark rounded">
                <li class="nav-item dropdown d-none d-md-block">
                    <a class="nav-link dropdown-toggle text-white fs-5" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-container">
                            <img src="/images/<?= $_SESSION['user_avatar'] ?>" alt="User Avatar" class="avatar">
                            <span class="online-status"></span>
                        </div>
                        <?= $_SESSION['user_name']; ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item fs-6" href="/admin/dashboard"><i class="bi bi-speedometer2 me-2 fs-5"></i> Pulpit</a></li>
                        <li><a class="dropdown-item fs-6" href="/admin/users"><i class="bi bi-list-task me-2 fs-5"></i> Użytkownicy</a></li>
                        <li><a class="dropdown-item fs-6" href="/admin/settings"><i class="bi bi-sliders me-2 fs-5"></i> Ustawienia</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item fs-6" href="/logout"><i class="bi bi-box-arrow-right me-2 fs-5"></i> Wyloguj się</a></li>
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
                        <div class="avatar-container">
                            <img src="/images/<?= $_SESSION['user_avatar'] ?>" alt="User Avatar" class="avatar" style="height:96px;width:96px">
                            <div class="status-icon">
                                <i class="bi bi-circle"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h3><?= $_SESSION['user_name'] ?></h3>
                            <h4><span class="badge bg-danger">Rola: <?= $_SESSION['user_role'] ?></span></h4>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="text-center">
                        <div class="d-grid gap-2">
                            <a href="/admin/dashboard" class="btn btn-primary btn-lg">Pulpit</a>
                            <a href="/admin/users" class="btn btn-primary btn-lg">Użytkownicy</a>
                            <a href="/admin/settings" class="btn btn-primary btn-lg">Ustawienia</a>
                            <a href="/logout" class="btn btn-primary btn-lg">Wyloguj się</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>