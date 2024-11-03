<?php App\Helpers\Template::partials('header_guest'); ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Błąd 500 - Strona nie znaleziona</h5>
                    <p class="card-text">Przepraszamy, ale żądana strona nie istnieje.</p>
                    <button onclick="goBack()" class="btn btn-primary">Wstecz</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function goBack() {
        window.history.back();
    }
</script>
</body>

</html>