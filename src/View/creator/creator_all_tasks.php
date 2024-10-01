<?php App\Helpers\Template::partials('header_creator'); ?>

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
        <header class="d-flex justify-content-end align-items-center m-4">
            <div class="d-flex">
                <a href="/creator/dashboard" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Wróć
                </a>
            </div>
        </header>

        <!-- Sprawdzamy, czy są projekty -->
        <div v-if="isEmpty(projects)" class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Brak projektów!</strong> Nie znaleziono żadnych projektów do wyświetlenia.
        </div>

        <!-- Jeśli są projekty -->
        <div v-else>
            <div v-for="(project, index) in projects" :key="index" class="card mt-3">
                <div class="card-header bg-info mb-3">
                    <h4>Projekt: {{ project.project_name }}</h4>
                </div>
                <div class="card-body">

                    <!-- Sprawdzamy, czy są zadania w projekcie -->
                    <div v-if="isEmpty(getTasks(project))" class="alert alert-warning" role="alert">
                        Brak zadań w tym projekcie.
                    </div>

                    <!-- Jeśli są zadania -->
                    <div v-else>
                        <div class="row">
                            <div v-for="(task, taskIndex) in getTasks(project)" :key="taskIndex" class="col-md-4">
                                <a :href="'/creator/project/' + project.project_id" class="card text-white bg-primary mb-3" style="text-decoration: none;">
                                    <div class="card-header">Zadanie: {{ task.task_name }}</div>
                                    <div class="card-body p-4">
                                        <p class="card-text">Opis: {{ task.task_description }}</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@3"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                loading: true,
                pageTitle: <?php echo json_encode($data['pageTitle']); ?>,
                projects: <?php echo json_encode($data['projects']); ?> // Dane projektów i zadań
            };
        },
        methods: {
            isEmpty(obj) {
                return !Array.isArray(obj) || obj.length === 0;
            },
            getTasks(project) {
                return Array.isArray(project.tasks) ? project.tasks : Object.values(project.tasks || {});
            }
        },
        mounted() {
            // Symulacja opóźnienia ładowania
            setTimeout(() => {
                this.loading = false; // Ustawienie na false po załadowaniu strony
            }, 1000);
        }
    }).mount('#app');
</script>