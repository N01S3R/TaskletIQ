<?php App\Helpers\Template::partials('header_operator'); ?>

<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div class="container">
        <header class="d-flex justify-content-between align-items-center m-4">
            <h3 class="p-2 mb-0">{{ pageTitle }}</h3>
        </header>
        <div class="row">
            <div class="col-xl-9 col-md-12 col-12">
                <div class="row">
                    <div class="col-xl-4 col-md-6 col-12" v-for="(project, index) in projects" :key="index">
                        <div class="card project-card bg-primary mb-4">
                            <a :href="'/operator/project/' + project.project_id" class="project text-decoration-none">
                                <div class="card-body text-center text-white">
                                    <!-- Wyświetlanie nazwy projektu -->
                                    <h3>{{ project.project_name }}</h3>
                                    <!-- Wyświetlanie liczby zadań -->
                                    <div class="text-info">Zadania: {{ project.task_count }}</div>
                                    <!-- Wyświetlanie liczby zadań do wykonania i ukończonych -->
                                    <div class="text-info">Do wykonania: {{ project.remaining_task_count }}</div>
                                    <div class="text-info">W trakcie: {{ project.inprogress_task_count }}</div>
                                    <div class="text-info">Ukończone: {{ project.completed_task_count }}</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-12 col-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Statystyki</h3>
                        <!-- Wyświetlanie statystyk -->
                        <p>Łączna liczba projektów: {{ projects.length }}</p>
                        <p>Łączna liczba zadań: {{ totalTasks }}</p>
                        <p>Łączna liczba zadań do wykonania: {{ totalRemainingTasks }}</p>
                        <p>Łączna liczba ukończonych zadań: {{ totalCompletedTasks }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    new Vue({
        el: '#app',
        data: {
            pageTitle: "<?= $data['pageTitle'] ?>",
            projects: <?= json_encode($data['projectsName']); ?>,
            loading: true
        },
        computed: {
            totalTasks() {
                return this.projects.reduce((sum, project) => sum + parseInt(project.task_count, 10), 0);
            },
            totalRemainingTasks() {
                return this.projects.reduce((sum, project) => sum + parseInt(project.remaining_task_count, 10), 0);
            },
            totalInProgressTasks() {
                return this.projects.reduce((sum, project) => sum + parseInt(project.inprogress_task_count, 10), 0);
            },
            totalCompletedTasks() {
                return this.projects.reduce((sum, project) => sum + parseInt(project.completed_task_count, 10), 0);
            }
        },
        mounted() {
            setTimeout(() => {
                this.loading = false;
            }, 1000);
        }
    });
</script>
</body>

</html>