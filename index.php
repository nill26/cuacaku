<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Cuacaku</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?= $debug_info ?>
    
    <div class="container my-5">
        <!-- Header -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <header class="text-center mb-5">
                    <h1 class="display-4 fw-bold text-primary mb-3">
                        <i class="bi bi-cloud-sun"></i> Cuacaku
                    </h1>
                    <p class="lead text-muted">Dapatkan informasi cuaca terkini untuk kota Anda</p>
                </header>
                <!-- Form Pencarian -->
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="city" class="form-control" 
                                       placeholder="Masukkan nama kota..." 
                                       value="<?= $city_input_value ?>" 
                                       required>
                                <button class="btn btn-primary btn-lg" type="submit">
                                    <i class="bi bi-arrow-right"></i> Cari
                                </button>
                            </div>
                        </form>
