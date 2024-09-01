<?php App\Helpers\Template::partials('header_admin'); ?>

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
    <div class="container">
        <h2 class="p-4">{{ pageTitle }}</h2>
        <div class="row">
            <!-- Pierwsza kolumna: Karty użytkowników, projektów i zadań -->
            <div class="col-xl-8 col-md-12 col-12">
                <div class="row">
                    <!-- Card: Użytkownicy -->
                    <div class="col-xl-4 col-md-6 col-12 mb-4">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <span class="h4 font-semibold text-muted text-sm d-block mb-2">Użytkownicy</span>
                                        <span class="h4 font-bold mb-0">{{ allUsers }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-success rounded-circle">
                                            <i class="bi bi-people"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2 mb-0 text-sm">
                                    <span class="text-nowrap text-xs text-muted">W tym miesiącu</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Projekty -->
                    <div class="col-xl-4 col-md-6 col-12 mb-4">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <span class="h4 font-semibold text-muted text-sm d-block mb-2">Projekty</span>
                                        <span class="h4 font-bold mb-0">{{ projects }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-warning rounded-circle">
                                            <i class="bi bi-journal"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2 mb-0 text-sm">
                                    <span class="text-nowrap text-xs text-muted">W tym miesiącu</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Zadania -->
                    <div class="col-xl-4 col-md-6 col-12 mb-4">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <span class="h4 font-semibold text-muted text-sm d-block mb-2">Zadania</span>
                                        <span class="h4 font-bold mb-0">{{ tasks }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-info rounded-circle">
                                            <i class="bi bi-check-circle"></i>
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

            <!-- Druga kolumna: Wykres słupkowy -->
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
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.2.1/dist/chart.umd.min.js"></script>
<script>
    new Vue({
        el: '#app',
        data: {
            pageTitle: "<?= htmlspecialchars($data['pageTitle']) ?>",
            allUsers: <?= json_encode($data['users']) ?>,
            projects: <?= json_encode($data['projects']) ?>,
            tasks: <?= json_encode($data['tasks']) ?>,
            loading: true,

        },
        mounted() {
            this.$nextTick(() => {
                setTimeout(() => {
                    this.loading = false;
                }, 2000);
            });
            // this.renderChart();
        },
        methods: {
            // renderChart() {
            //     const canvas = document.getElementById('projectsChart');
            //     if (canvas) {
            //         const ctx = canvas.getContext('2d');

            //         // Procesowanie danych
            //         const months = this.projectsByMonth.map(item => item.month);
            //         const counts = this.projectsByMonth.map(item => item.project_count);

            //         new Chart(ctx, {
            //             type: 'bar',
            //             data: {
            //                 labels: months,
            //                 datasets: [{
            //                     label: 'Liczba projektów',
            //                     data: counts,
            //                     backgroundColor: '#375a7f',
            //                     borderColor: '#222222',
            //                     borderWidth: 1
            //                 }]
            //             },
            //             options: {
            //                 responsive: true,
            //                 scales: {
            //                     x: {
            //                         beginAtZero: true
            //                     },
            //                     y: {
            //                         beginAtZero: true,
            //                         ticks: {
            //                             callback: function(value) {
            //                                 // Formatowanie liczby całkowitej bez przecinków
            //                                 return Number.isInteger(value) ? value : '';
            //                             }
            //                         }
            //                     }
            //                 }
            //             }
            //         });
            //     } else {
            //         console.error('Element <canvas> o ID "projectsChart" nie został znaleziony.');
            //     }
            // }
        }
    });
</script>

</body>

</html>