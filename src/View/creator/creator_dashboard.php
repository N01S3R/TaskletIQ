<?php App\Helpers\Template::partials('header_creator'); ?>
<!-- <?php
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        ?> -->
<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div class="container my-4">
        <nav aria-label="breadcrumb" class="main-breadcrumb mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Creator</a></li>
                <li class="breadcrumb-item"><a href="#">{{ pageTitle }}</a></li>
            </ol>
        </nav>
        <header class="d-flex justify-content-between align-items-center m-4">
            <!-- <div class="d-flex">
                <a href="/creator/dashboard" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Wróć
                </a>
            </div> -->
        </header>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-xl-3 col-sm-6 col-12">
                        <a href="/creator/tasks" class="card text-decoration-none">
                            <div class="card-body bg-primary text-white d-flex align-items-center rounded shadow-lg">
                                <i class="bi bi-list me-3 fs-2"></i>
                                <div>
                                    <span>Wszystkie Zadania</span>
                                    <h3>
                                        {{ processName.tasksCount }}
                                    </h3>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <a href="/creator/tasks/1" class="card text-decoration-none">
                            <div class="card h-100">
                                <div class="card-body bg-danger text-white d-flex align-items-center rounded shadow-lg">
                                    <i class="bi bi-play-circle me-3 fs-2"></i>
                                    <div>
                                        <span>Rozpoczęte Zadania</span>
                                        <h3>
                                            {{ processName.tasksStart }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <a href="/creator/tasks/2" class="card text-decoration-none">
                            <div class="card h-100">
                                <div class="card-body bg-warning text-white d-flex align-items-center rounded shadow-lg">
                                    <i class="bi bi-hourglass-split me-3 fs-2"></i>
                                    <div>
                                        <span>Zadania w Trakcie</span>
                                        <h3>
                                            {{ processName.tasksInProgress }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <a href="/creator/tasks/3" class="card text-decoration-none">
                            <div class="card h-100">
                                <div class="card-body bg-success text-white d-flex align-items-center rounded shadow-lg">
                                    <i class="bi bi-check-circle-fill me-3 fs-2"></i>
                                    <div>
                                        <span>Zakończone Zadania</span>
                                        <h3>
                                            {{ processName.tasksDone }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@3"></script>
<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                pageTitle: <?php echo json_encode($data['pageTitle']); ?>,
                processName: <?php echo json_encode($data); ?>,
                loading: true // Ustawienie początkowe na true, aby pokazać preloader
            }
        },
        mounted() {
            // Symulacja opóźnienia ładowania
            setTimeout(() => {
                this.loading = false; // Ustawienie na false po załadowaniu strony
            }, 1000); // Możesz dostosować czas opóźnienia
        }
    }).mount('#app');
</script>

</body>

</html>