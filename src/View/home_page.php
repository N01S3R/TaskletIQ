<?php App\Helpers\Template::partials('header_guest'); ?>
<!-- <?php
        echo "<pre>";
        var_dump($data);
        echo "</pre>"; ?> -->
<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div v-else>
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-8">
                    <!-- Główna treść -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Witaj w Liście Zadań</h5>
                            <p class="card-text">Zorganizuj swoje zadania i zwiększ swoją produktywność dzięki naszej intuicyjnej platformie Listy Zadań. Śledź codzienne, tygodniowe i miesięczne cele, aby nigdy nie przegapić ważnego zadania.</p>
                            <p class="card-text">Funkcje:</p>
                            <ul>
                                <li>Prosty i przyjazny dla użytkownika interfejs</li>
                                <li>Tworzenie, edycja i usuwanie zadań bez problemu</li>
                                <li>Oznaczanie zadań jako ukończone</li>
                                <li>Organizacja zadań według projektów</li>
                            </ul>
                            <p class="card-text">Zacznij już dziś i spraw, aby zarządzanie zadaniami było łatwiejsze!</p>
                            <a href="/login" class="btn btn-primary">Zaczynajmy</a>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Ostatnio zarejestrowani</h5>
                            <!-- Ostatnio zarejestrowani -->
                            <ul class="list-group">
                                <?php foreach ($data["lastRegistred"] as $user) : ?>
                                    <li class="list-group-item"><?= htmlspecialchars($user); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
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