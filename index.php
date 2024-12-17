<?php

// Función para obtener la IP local
function getLocalIP() {
    $ip = 'Unknown';
    // Intentamos obtener la dirección IP local
    $hostname = gethostname();
    $ip = gethostbyname($hostname);
    return $ip;
}

// Función para obtener la región (si estás en AWS)
function getRegion() {
    $url = 'http://169.254.169.254/latest/meta-data/placement/region'; // URL de la metadata de AWS
    $region = 'Unknown';

    // Usamos cURL para obtener la región
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $region = curl_exec($ch);
    curl_close($ch);

    return $region ?: 'Unknown';
}

// Configuración de la base de datos MySQL
$host = "localhost";
$user = "root";
$password = "rootpassword";
$database = "api_rest";

// Conectar a la base de datos MySQL
$mysqli = new mysqli($host, $user, $password, $database);

// Verificar si la conexión fue exitosa
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Consulta a la base de datos
$query = "SELECT name FROM friends WHERE id = 1";
$result = $mysqli->query($query);

// Obtener el dato de la base de datos
$name = "";
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['name'];
}

// Cerrar la conexión
$mysqli->close();

// Obtener IP local y región
$localIP = getLocalIP();
$region = getRegion();

// Enviar el correo con el dato usando la función mail()
$to = "fimoreria@uce.edu.ec";
$subject = "Nuevo dato de la base de datos";
$message = "Se ha obtenido el nombre: " . $name . "\n\nIP Local: " . $localIP . "\nRegión: " . $region;
$headers = "From: no-reply@tu-dominio.com\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";

if (mail($to, $subject, $message, $headers)) {
    $emailStatus = "Correo enviado con éxito.";
} else {
    $emailStatus = "Error al enviar el correo.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información de la Instancia</title>
</head>
<body>
    <h4>Local IP: <?php echo $localIP; ?></h4>
    <h4>Region: <?php echo $region; ?></h4>
    <h4><?php echo $emailStatus; ?></h4>
</body>
</html>
