<?php App\Helpers\Template::partials('header_operator'); ?>

<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div class="container" v-else>
        <header class="d-flex justify-content-between align-items-center m-4">
            <h3 class="p-2 mb-0">{{ pageTitle }}</h3>
        </header>
        <div class="row">
            <div class="col-xl-9 col-md-12 col-12">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0">Projekty</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-4 col-md-6 col-12" v-for="(project, index) in projects" :key="project.project_id">
                                <div class="card project-card bg-primary mb-4 shadow">
                                    <a :href="'/operator/project/' + project.project_id" class="project text-decoration-none">
                                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                            <h2 class="mb-0">{{ project.project_name }}</h2>
                                        </div>
                                        <div class="card-body text-center text-white">
                                            <div class="task-status">
                                                <div class="text-info">
                                                    <strong>Zadania: </strong>{{ project.task_count }}
                                                </div>
                                                <div class="text-warning">
                                                    <strong>Rozpoczęte: </strong>{{ project.remaining_task_count }}
                                                </div>
                                                <div class="text-secondary">
                                                    <strong>W trakcie: </strong>{{ project.inprogress_task_count }}
                                                </div>
                                                <div class="text-success">
                                                    <strong>Ukończone: </strong>{{ project.completed_task_count }}
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
            <div class="col-xl-3 col-md-12 col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0">Statystyki</h3>
                    </div>
                    <div class="card-body">
                        <div class="statistic-item">
                            <p><strong>Łączna liczba projektów:</strong> <span class="text-info">{{ projects.length }}</span></p>
                        </div>
                        <div class="statistic-item">
                            <p><strong>Łączna liczba zadań:</strong> <span class="text-info">{{ totalTasks }}</span></p>
                        </div>
                        <div class="statistic-item">
                            <p><strong>Łączna liczba zadań do wykonania:</strong> <span class="text-warning">{{ totalNotCompletedTasks }}</span></p>
                        </div>
                        <div class="statistic-item">
                            <p><strong>Łączna liczba ukończonych zadań:</strong> <span class="text-success">{{ totalCompletedTasks }}</span></p>
                        </div>
                        <canvas id="tasksPieChart" class="mt-3"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@3.2.47/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                pageTitle: "<?= $data['pageTitle'] ?>",
                projects: <?= json_encode($data['projectsName']); ?>,
                loading: true
            };
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
            },
            totalNotCompletedTasks() {
                return this.projects.reduce((sum, project) => {
                    return sum + parseInt(project.remaining_task_count, 10) + parseInt(project.inprogress_task_count, 10);
                }, 0);
            }
        },
        mounted() {
            setTimeout(() => {
                this.loading = false;
                this.$nextTick(() => {
                    this.renderPieChart();
                });
            }, 1000);
        },
        methods: {
            renderPieChart() {
                const ctx = document.getElementById('tasksPieChart').getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Zadania do wykonania', 'Zadania w trakcie', 'Ukończone'],
                        datasets: [{
                            label: 'Statystyki zadań',
                            data: [
                                this.totalNotCompletedTasks, // Właściwość
                                this.totalInProgressTasks, // Właściwość
                                this.totalCompletedTasks // Właściwość
                            ],
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        }
                    }
                });
            }
        }
    }).mount('#app');
</script>
</body>

</html>