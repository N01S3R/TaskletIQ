<?php App\Helpers\Template::partials('header_operator'); ?>

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
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card rounded shadow-lg">
                    <div class="card-header">Projekt: {{ task.project_name }}</div>
                    <div class="card-body">
                        <h4 class="card-title">Nazwa zadania: {{ task.task_name }}</h4>
                        <hr>
                        <small class="text-muted">Opis krótki: {{ task.task_description }}</small>
                        <hr>
                        <small class="text-muted">Opis długi: {{ task.task_description_long }}</small>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Utworzone: {{ task.task_created_at }}</small>
                        <a href="javascript:history.back()" class="btn btn-primary">Wróć</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            pageTitle: <?= json_encode($data['pageTitle']) ?>,
            loading: true,
            task: <?= json_encode($data['task']) ?>,
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