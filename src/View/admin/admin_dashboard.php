<?php App\Helpers\Template::partials('header_admin'); ?>

<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div class="container">
        <h2 class="p-4">{{ pageTitle }}</h2>
        <div class="row">
            <!-- Karty użytkowników, projektów i zadań -->
            <div class="col-xl-8 col-md-12 col-12">
                <div class="row">
                    <div class="col-xl-4 col-md-6 col-12 mb-4" v-for="(count, label, index) in cards" :key="index">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <span class="h4 font-semibold text-muted text-sm d-block mb-2">{{ label }}</span>
                                        <span class="h4 font-bold mb-0">{{ count }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <button :class="'btn rounded-circle ' + buttonClasses[index]">
                                            <i :class="icons[index]"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2 mb-0 text-sm">
                                    <span class="text-nowrap text-xs text-muted">W tym miesiącu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wykres słupkowy -->
            <div class="col-xl-4 col-md-12 col-12">
                <div class="card shadow border-0 mb-4">
                    <div class="card-body">
                        <canvas id="projectsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@3.2.47/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.2.1/dist/chart.umd.min.js"></script>

<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                pageTitle: "<?= htmlspecialchars($data['pageTitle']) ?>",
                allUsers: <?= json_encode($data['users']) ?>,
                projects: <?= json_encode($data['projects']) ?>,
                tasks: <?= json_encode($data['tasks']) ?>,
                loading: true,
                cards: {
                    'Użytkownicy': <?= json_encode($data['users']) ?>,
                    'Projekty': <?= json_encode($data['projects']) ?>,
                    'Zadania': <?= json_encode($data['tasks']) ?>
                },
                buttonClasses: ['btn-success', 'btn-warning', 'btn-info'],
                icons: ['bi bi-people', 'bi bi-journal', 'bi bi-check-circle'],
                projectCounts: <?= json_encode($data['projectsByMonth']) ?>,
                months: ['Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień']
            };
        },
        mounted() {
            setTimeout(() => {
                this.loading = false;
            }, 2000);

            this.renderChart();
        },
        methods: {
            renderChart() {
                const projectCountsArray = Object.values(this.projectCounts);
                const ctx = document.getElementById('projectsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.months,
                        datasets: [{
                            label: 'Ilość projektów',
                            data: projectCountsArray,
                            backgroundColor: 'rgba(255, 228, 196, 0.5)',
                            borderColor: 'rgba(255, 228, 196, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.2)',
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.2)',
                                }
                            }
                        }
                    }
                });
            }
        }
    }).mount('#app');
</script>

</body>

</html>