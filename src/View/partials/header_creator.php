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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/perfect-scrollbar/css/perfect-scrollbar.css">
    <script src="https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.5/dist/perfect-scrollbar.min.js"></script>
    <!-- <link rel="stylesheet" href="<?php echo getenv('BASE_URL'); ?>css/bootstrap.css"> -->
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-primary">
        <div class="container">
            <a class="navbar-brand p-0" href="<?= getenv('BASE_URL') . 'creator/dashboard'; ?>"><img src="/images/logo.png" alt="Logo" style="height: 64px;"></a>
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
                        <li><a class="dropdown-item fs-6" href="/creator/dashboard"><i class="bi bi-speedometer2 me-2 fs-5"></i> Dashboard</a></li>
                        <li><a class="dropdown-item fs-6" href="/creator/projects"><i class="bi bi-list-task me-2 fs-5"></i> Moje Projekty</a></li>
                        <li><a class="dropdown-item fs-6" href="/creator/delegate"><i class="bi bi-person-plus me-2 fs-5"></i> Przydziel Zadania</a></li>
                        <li><a class="dropdown-item fs-6" href="/creator/code"><i class="bi bi-shield-lock me-2 fs-5"></i> Stwórz Kod</a></li>
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
                        <div class="avatar-container">
                            <img src="/images/<?= $_SESSION['user_avatar'] ?>" alt="User Avatar" class="avatar" style="height:96px;width:96px">
                            <div class="status-icon">
                                <i class="bi bi-circle"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h3><?= $_SESSION['user_name'] ?></h3>
                            <h4><span class="badge bg-danger">Rola: <?= $_SESSION['user_role'] ?></span></h4>
                            <!-- <p class="text-muted font-size-sm">Bay Area, San Francisco, CA</p>
                            <button class="btn btn-primary">Follow</button>
                            <button class="btn btn-outline-primary">Message</button> -->
                        </div>
                    </div>
                    <hr class="my-4">
                    <!-- <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-globe me-2 icon-inline">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="2" y1="12" x2="22" y2="12"></line>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                </svg>Website</h6>
                            <span class="text-secondary">https://bootdey.com</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-github me-2 icon-inline">
                                    <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
                                </svg>Github</h6>
                            <span class="text-secondary">bootdey</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-twitter me-2 icon-inline text-info">
                                    <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                                </svg>Twitter</h6>
                            <span class="text-secondary">@bootdey</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-instagram me-2 icon-inline text-danger">
                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                                </svg>Instagram</h6>
                            <span class="text-secondary">bootdey</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-facebook me-2 icon-inline text-primary">
                                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                                </svg>Facebook</h6>
                            <span class="text-secondary">bootdey</span>
                        </li>
                    </ul> -->
                    <div class="text-center">
                        <div class="d-grid gap-2">
                            <a href="/creator/dashboard" class="btn btn-primary btn-lg">Dashboard</a>
                            <a href="/creator/projects" class="btn btn-primary btn-lg">Moje Projekty</a>
                            <a href="/creator/delegate" class="btn btn-primary btn-lg">Przydziel Zadania</a>
                            <a href="/logout" class="btn btn-primary btn-lg">Wyloguj się</a>
                        </div>
                    </div>
                    <!-- <ul class="list-group list-group-flush">
                        <li><a class="dropdown-item" href="/creator/dashboard">Dashboard</a></li>
                        <li><a class="dropdown-item" href="/creator/projects">Moje Projekty</a></li>
                        <li><a class="dropdown-item" href="/creator/delegate">Przydziel Zadania</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="/logout">Wyloguj się</a></li>
                    </ul> -->
                </div>
            </div>
        </div>
    </div>