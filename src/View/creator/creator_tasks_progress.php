<?php App\Helpers\Template::partials('header_creator'); ?>

<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div class="container my-4">
        <nav aria-label="breadcrumb" class="main-breadcrumb mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="">Creator</a></li>
                <li class="breadcrumb-item"><a href="#">{{ pageTitle }}</a></li>
            </ol>
        </nav>
        <header class="d-flex justify-content-end align-items-center m-4">
            <div class="d-flex">
                <a href="/creator/dashboard" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Wróć</a>
            </div>
        </header>
        <div v-if="isEmpty(groupedTasks)" class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Brak zadań!</strong> Nie znaleziono żadnych zadań do wyświetlenia.
        </div>
        <div v-else>
            <div v-for="(tasks, projectName) in groupedTasks" :key="projectName" class="card mt-3">
                <div class="card-header bg-info mb-3">
                    <h3 class="p-2">Projekt: {{ projectName }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div v-for="task in tasks" :key="task.task_id" class="col-md-4 p-3">
                            <a :href="'/creator/project/' + task.project_id" :class="['card', 'text-white', color, 'mb-3']" style="text-decoration: none;">
                                <div class="card-header">{{ task.task_name }}</div>
                                <div class="card-body p-4">
                                    <p class="card-text">Opis zadania: {{ task.task_description }}</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@3.3.4/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                loading: true, // Początkowe ustawienie na true, aby pokazać preloader
                pageTitle: <?= json_encode($data['pageTitle']); ?>,
                groupedTasks: <?= json_encode($data['groupedTasks']); ?>,
                color: `bg-${<?= json_encode($data['color']); ?>}` // Dynamiczna klasa koloru
            };
        },
        mounted() {
            // Symulacja opóźnienia ładowania
            setTimeout(() => {
                this.loading = false; // Ustawienie na false po załadowaniu strony
            }, 1000); // Możesz dostosować czas opóźnienia
        },
        methods: {
            isEmpty(obj) {
                return Object.keys(obj).length === 0;
            }
        }
    }).mount('#app');
</script>
</body>

</html>