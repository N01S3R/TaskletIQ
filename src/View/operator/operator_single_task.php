<?php App\Helpers\Template::partials('header_operator'); ?>

<div id="app">
    <div v-if="loading" class="loader text-center">
        <h1 class="mb-3">TaskletIQ</h1>
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>

    <div class="container" v-else>
        <h2 class="p-4 text-center">{{ pageTitle }}</h2>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card rounded shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Projekt: {{ task.project_name }}</h5>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title">Nazwa zadania: {{ task.task_name }}</h4>
                        <hr>
                        <p class="card-text"><strong>Opis krótki:</strong> {{ task.task_description }}</p>
                        <p class="card-text"><strong>Opis długi:</strong> {{ task.task_description_long }}</p>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Utworzone: {{ task.task_created_at }}</small>
                        <a href="javascript:history.back()" class="btn btn-secondary">Wróć</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@3.2.37/dist/vue.global.js"></script>
<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                pageTitle: <?= json_encode($data['pageTitle']) ?>,
                loading: true,
                task: <?= json_encode($data['task']) ?>,
            };
        },
        mounted() {
            setTimeout(() => {
                this.loading = false;
            }, 1000);
        }
    }).mount('#app');
</script>

</body>

</html>