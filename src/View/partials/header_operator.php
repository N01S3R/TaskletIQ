<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $_ENV["APP_NAME"]; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $_ENV["BASE_URL"]; ?>css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lobibox/dist/css/lobibox.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lobibox/dist/js/lobibox.min.js"></script>
    <link rel="stylesheet" href="<?= $_ENV["BASE_URL"]; ?>css/bootstrap.min.css">
    <!-- Include Vue.js library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.min.js"></script>
    <script src="https://unpkg.com/vue@next"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-primary">
        <div class="container">
            <a class="navbar-brand text-white p-0" href="<?= $_ENV["BASE_URL"]; ?>operator/dashboard"><img src="/images/logo.png" alt="User Avatar" class="avatar" style="height: 64px;"></a>
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
                        <li><a class="dropdown-item fs-6" href="/operator/dashboard"><i class="bi bi-list-task me-2 fs-5"></i> Pulpit</a></li>
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
            <!-- Profile Section -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/images/<?= $_SESSION['user_avatar'] ?>" alt="Admin" class="rounded-circle p-1 bg-primary" width="110">
                        <div class="mt-3">
                            <h4><?= $_SESSION['user_name'] ?></h4>
                            <p class="text-secondary mb-1">Full Stack Developer</p>
                            <p class="text-muted font-size-sm">Bay Area, San Francisco, CA</p>
                            <button class="btn btn-primary">Follow</button>
                            <button class="btn btn-outline-primary">Message</button>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="text-center">
                        <div class="d-grid gap-2">
                            <a href="/operator/dashboard" class="btn btn-primary btn-lg">Dashboard</a>
                            <a href="/logout" class="btn btn-primary btn-lg">Wyloguj się</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>