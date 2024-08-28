<?php App\Helpers\Template::partials('header_guest'); ?>

<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div v-else>
        <div class="container mt-4">
            <!-- Login Form -->
            <div class="row mt-4">
                <div class="col-md-4 offset-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Logowanie</h5>
                            <?php if (isset($data['error']) && !empty($data['error'])) : ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $data['error']; ?>
                                </div>
                            <?php endif; ?>
                            <form method="post" action="/login">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Login</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Hasło</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="d-flex justify-content-between align-items-center"> <!-- Nowy div dla przycisków -->
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-box-arrow-in-right"></i> Zaloguj
                                    </button>
                                    <a href="/register" class="btn btn-danger">
                                        <i class="bi bi-person-plus"></i> Rejestracja
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            loading: true // Ustawienie początkowe na true, aby pokazać preloader
        },
        mounted() {
            // Symulacja opóźnienia ładowania
            setTimeout(() => {
                this.loading = false; // Ustawienie na false po załadowaniu strony
            }, 1000); // Możesz dostosować czas opóźnienia
        }
    });
</script>
</body>

</html>