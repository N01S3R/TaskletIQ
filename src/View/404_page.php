<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Panel</title>
    <link rel="stylesheet" href="https://todolist.iqhs.pl/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lobibox/dist/css/lobibox.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lobibox/dist/js/lobibox.min.js"></script>
    <link rel="stylesheet" href="https://todolist.iqhs.pl/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://todolist.iqhs.pl/css/bootstrap.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg bg-primary">
        <div class="container">
            <a class="navbar-brand text-white" href="#">User Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Right-aligned elements -->
        </div>
    </nav>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Błąd 404 - Strona nie znaleziona</h5>
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