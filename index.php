<?php
$api_key = "9d79e1aa846939848e3c3bc509a72335";
$weather_data = null;
$error_message = "";

$city_input_value = "";
$location_name = "";
$country_code = "";
$current_datetime = "";
$weather_icon = "";
$temperature = "";
$weather_description = "";
$feels_like = "";
$humidity = "";
$pressure = "";
$wind_speed = "";
$visibility = "";
$sunrise_time = "";
$sunset_time = "";
$weather_background_class = "";
$temperature_class = "";
$debug_info = "";

function callWeatherAPI($city, $api_key) {

    $api_url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=" . $api_key . "&units=metric&lang=id";
    
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,                
        CURLOPT_CONNECTTIMEOUT => 10,         
        CURLOPT_FOLLOWLOCATION => true,       
        CURLOPT_SSL_VERIFYPEER => true,      
        CURLOPT_SSL_VERIFYHOST => 2,          
        CURLOPT_USERAGENT => 'Weather App/1.0 (PHP/' . PHP_VERSION . ')',
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Cache-Control: no-cache'
        ],
        CURLOPT_ENCODING => '',             
        CURLOPT_FRESH_CONNECT => true,        
        CURLOPT_FORBID_REUSE => true         
    ]);
    $response = curl_exec($curl);
    
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    $curl_errno = curl_errno($curl);
    $total_time = curl_getinfo($curl, CURLINFO_TOTAL_TIME);
    
    curl_close($curl);
    
    return [
        'response' => $response,
        'http_code' => $http_code,
        'error' => $curl_error,
        'errno' => $curl_errno,
        'total_time' => $total_time
    ];
}
function formatTime($timestamp, $timezone) {
    return date('H:i', $timestamp + $timezone);
}

function getWeatherIcon($weather_main) {
    $icons = [
        'Clear' => '☀️',
        'Clouds' => '☁️',
        'Rain' => '🌧️',
        'Drizzle' => '🌦️',
        'Thunderstorm' => '⛈️',
        'Snow' => '❄️',
        'Mist' => '🌫️',
        'Fog' => '🌫️',
        'Haze' => '🌫️'
    ];
    
    return isset($icons[$weather_main]) ? $icons[$weather_main] : '🌤️';
}
function getTemperatureClass($temp) {
    if ($temp >= 30) return 'text-danger';
    if ($temp >= 20) return 'text-warning';
    if ($temp >= 10) return 'text-info';
    return 'text-primary';
}

function getWeatherBackground($weather_main) {
    $backgrounds = [
        'Clear' => 'bg-warning',
        'Clouds' => 'bg-secondary',
        'Rain' => 'bg-primary',
        'Drizzle' => 'bg-info',
        'Thunderstorm' => 'bg-dark',
        'Snow' => 'bg-light',
        'Mist' => 'bg-secondary',
        'Fog' => 'bg-secondary',
        'Haze' => 'bg-secondary'
    ];
    
    return isset($backgrounds[$weather_main]) ? $backgrounds[$weather_main] : 'bg-primary';
}
if ($_POST && isset($_POST['city'])) {
    $city = trim($_POST['city']);
    $city_input_value = htmlspecialchars($city);
    
    if (!empty($city)) {
        
        $api_result = callWeatherAPI($city, $api_key);
        
        
        if ($api_result['response'] === false || !empty($api_result['error'])) {
            $error_message = "Gagal mengakses API cuaca. ";
            
            switch ($api_result['errno']) {
                case CURLE_COULDNT_CONNECT:
                    $error_message .= "Tidak dapat terhubung ke server.";
                    break;
                case CURLE_OPERATION_TIMEOUTED:
                    $error_message .= "Request timeout.";
                    break;
                case CURLE_SSL_CONNECT_ERROR:
                    $error_message .= "SSL connection error.";
                    break;
                case CURLE_COULDNT_RESOLVE_HOST:
                    $error_message .= "Tidak dapat resolve hostname.";
                    break;
                default:
                    $error_message .= "Error: " . $api_result['error'];
            }
            } elseif ($api_result['http_code'] !== 200) {
            switch ($api_result['http_code']) {
                case 404:
                    $error_message = "Kota tidak ditemukan. Silakan periksa ejaan nama kota.";
                    break;
            }
        } else {
            
            $weather_data = json_decode($api_result['response'], true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error_message = "Error parsing data cuaca. JSON Error: " . json_last_error_msg();
                $weather_data = null;
            } elseif (isset($weather_data['cod']) && $weather_data['cod'] != 200) {
                $error_message = isset($weather_data['message']) ? 
                    "API Error: " . ucfirst($weather_data['message']) : 
                    "Kota tidak ditemukan. Silakan coba lagi.";
                $weather_data = null;
            } else {
                
                $location_name = htmlspecialchars($weather_data['name']);
                $country_code = htmlspecialchars($weather_data['sys']['country']);
                $weather_icon = getWeatherIcon($weather_data['weather'][0]['main']);
                $temperature = round($weather_data['main']['temp']);
                $weather_description = ucfirst($weather_data['weather'][0]['description']);
                $feels_like = round($weather_data['main']['feels_like']);
                $humidity = $weather_data['main']['humidity'];
                $pressure = $weather_data['main']['pressure'];
                $wind_speed = $weather_data['wind']['speed'];
                $visibility = isset($weather_data['visibility']) ? ($weather_data['visibility'] / 1000) : null;
                $sunrise_time = formatTime($weather_data['sys']['sunrise'], $weather_data['timezone']);
                $sunset_time = formatTime($weather_data['sys']['sunset'], $weather_data['timezone']);
                $weather_background_class = getWeatherBackground($weather_data['weather'][0]['main']);
                $temperature_class = getTemperatureClass($weather_data['main']['temp']);
            }
            
            if (isset($_GET['debug']) && $weather_data) {
                $debug_info = "<!-- Debug Info: Request time: " . round($api_result['total_time'], 2) . " seconds -->";
            }
        }
        } else {
        $error_message = "Silakan masukkan nama kota.";
    }
} else {
    $city_input_value = isset($_POST['city']) ? htmlspecialchars($_POST['city']) : '';
}

date_default_timezone_set('Asia/Jakarta');
$current_datetime = date('l, d F Y - H:i');

$error_message_display = htmlspecialchars($error_message);

$curl_status = function_exists('curl_init') ? "Available" : "Not Available";
$php_version = PHP_VERSION;

?>

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
                        <!-- Quick Search -->
                        <div class="mt-3 text-center">
                            <small class="text-muted">Coba cepat:</small>
                            <form method="POST" class="d-inline">
                                <button type="submit" name="city" value="Padang" class="btn btn-outline-primary btn-sm mx-1 mt-2">Padang</button>
                                <button type="submit" name="city" value="Sijunjung" class="btn btn-outline-primary btn-sm mx-1 mt-2">Sijunjung</button>
                                <button type="submit" name="city" value="Pariaman" class="btn btn-outline-primary btn-sm mx-1 mt-2">Pariaman</button>
                                <button type="submit" name="city" value="Bandung" class="btn btn-outline-primary btn-sm mx-1 mt-2">Bandung</button>
                                <button type="submit" name="city" value="Yogyakarta" class="btn btn-outline-primary btn-sm mx-1 mt-2">Yogyakarta</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Error Message -->
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Oops!</strong> <?= $error_message_display ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <!-- Weather Display -->
                <?php if ($weather_data): ?>
                    <div class="card shadow-lg border-0 weather-card <?= $weather_background_class ?> text-white">
                        <div class="card-body p-4">
                            <!-- Location Header -->
                            <div class="row align-items-center mb-4">
                                <div class="col">
                                    <h2 class="h3 mb-1">
                                        <i class="bi bi-geo-alt"></i>
                                        <?= $location_name ?>, <?= $country_code ?>
                                    </h2>
                                    <small class="opacity-75">
                                        <i class="bi bi-clock"></i>
                                        <?= $current_datetime ?>
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <div class="weather-icon display-1">
                                        <?= $weather_icon ?>
                                    </div>
                                </div>
                            </div>
                             <!-- Main Weather Info -->
                            <div class="row align-items-center mb-4">
                                <div class="col">
                                    <div class="display-1 fw-light mb-0">
                                        <?= $temperature ?>°C
                                    </div>
                                    <p class="h5 mb-0 opacity-75">
                                        <?= $weather_description ?>
                                    </p>
                                    <small class="opacity-75">
                                        Terasa seperti <?= $feels_like ?>°C
                                    </small>
                                </div>
                            </div>
                             <!-- Weather Details -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card bg-white bg-opacity-20 border-0">
                                        <div class="card-body text-center">
                                            <i class="bi bi-droplet display-6 mb-2"></i>
                                            <h6 class="card-title">Kelembaban</h6>
                                            <p class="card-text h4"><?= $humidity ?>%</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-white bg-opacity-20 border-0">
                                        <div class="card-body text-center">
                                            <i class="bi bi-speedometer2 display-6 mb-2"></i>
                                            <h6 class="card-title">Tekanan</h6>
                                            <p class="card-text h4"><?= $pressure ?> hPa</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-white bg-opacity-20 border-0">
                                        <div class="card-body text-center">
                                            <i class="bi bi-wind display-6 mb-2"></i>
                                            <h6 class="card-title">Kecepatan Angin</h6>
                                            <p class="card-text h4"><?= $wind_speed ?> m/s</p>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($visibility !== null): ?>
                                <div class="col-md-6">
                                    <div class="card bg-white bg-opacity-20 border-0">
                                        <div class="card-body text-center">
                                            <i class="bi bi-eye display-6 mb-2"></i>
                                            <h6 class="card-title">Jarak Pandang</h6>
                                            <p class="card-text h4"><?= $visibility ?> km</p>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                             <!-- Sunrise & Sunset -->
                            <div class="row mt-4">
                                <div class="col-6 text-center">
                                    <i class="bi bi-sunrise h3"></i>
                                    <p class="mb-0">Matahari Terbit</p>
                                    <strong><?= $sunrise_time ?></strong>
                                </div>
                                <div class="col-6 text-center">
                                    <i class="bi bi-sunset h3"></i>
                                    <p class="mb-0">Matahari Terbenam</p>
                                    <strong><?= $sunset_time ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Welcome Card -->
                    <div class="card shadow-lg border-0">
                        <div class="card-body text-center p-5">
                            <i class="bi bi-cloud-sun display-1 text-primary mb-4"></i>
                            <h3 class="h4 mb-3">Selamat datang di Cuacaku!</h3>
                            <p class="text-muted mb-4">
                                Masukkan nama kota di atas untuk melihat informasi cuaca terkini.
                                Anda juga dapat menggunakan tombol cepat untuk kota-kota populer.
                            </p>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <i class="bi bi-thermometer-half text-danger h4"></i>
                                        <p class="mb-0 small">Suhu Real-time</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <i class="bi bi-droplet text-info h4"></i>
                                        <p class="mb-0 small">Kelembaban & Tekanan</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <i class="bi bi-wind text-primary h4"></i>
                                        <p class="mb-0 small">Kondisi Angin</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                 <!-- Footer -->
                <footer class="text-center mt-5">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <p class="text-muted mb-2">
                                <i class="bi bi-info-circle"></i>
                                Data cuaca dari 
                                <a href="https://openweathermap.org/" target="_blank" class="text-decoration-none">
                                    OpenWeatherMap <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>
     <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts after 7 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 7000);

        // Add loading state to search button
        document.querySelector('form').addEventListener('submit', function(e) {
            var btn = this.querySelector('button[type="submit"]');
            var originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mencari...';
            btn.disabled = true;
            
            // Re-enable button setelah 30 detik (timeout)
            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 30000);
        });

        // Check if cURL is available (for debugging)
        <?php if (isset($_GET['debug'])): ?>
        console.log('cURL Status: <?= $curl_status ?>');
        console.log('PHP Version: <?= $php_version ?>');
        <?php endif; ?>
    </script>
</body>
</html>



