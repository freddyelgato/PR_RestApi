<?php

$host = "db";  
$user = "root";
$password = "";
$database = "api_rest";
$mysqli = new mysqli("db", "root", "rootpassword", "api_rest");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD']; 

$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
$searchId = explode('/', $path);
$id = ($path !== '/') ? end($searchId) : null;

switch ($method) {
    // Select
    case 'GET':
        if ($path === '/region') {
            getInstanceRegion();
        } else {
            selectData($mysqli, $id);
        }
        break;
    // Insert
    case 'POST':
        insertData($mysqli);
        break;
    // Update
    case 'PUT':
        updateData($mysqli, $id);
        break;
    // Delete
    case 'DELETE':
        deleteData($mysqli, $id);
        break;
    default:
        echo json_encode(array("error" => "Method not allowed"));
        break;
}

function getInstanceRegion() {
    $metadataUrl = "http://169.254.169.254/latest/meta-data/placement/region";

    // Usar cURL para obtener la región
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $metadataUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $region = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(array('error' => 'Error obteniendo la región: ' . curl_error($ch)));
    } else {
        echo json_encode(array('region' => $region));
    }

    curl_close($ch);
}

function selectData($mysqli, $id) {
    $sql = ($id === null) ? "SELECT * FROM friends" : "SELECT * FROM friends WHERE id=$id";
    $result = $mysqli->query($sql);

    if ($result) {
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    }
}

function insertData($mysqli) {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'];

    $sql = "INSERT INTO friends(name) VALUES ('$name')";
    $result = $mysqli->query($sql);

    if ($result) {
        $data['id'] = $mysqli->insert_id;

        // Configuración para el correo
        $to = "fimoreria@uce.edu.ec"; // Cambia por el correo destinatario
        $subject = "Nuevo amigo agregado";
        $message = "Se ha agregado un nuevo amigo con el nombre: $name y ID: " . $data['id'];
        $headers = "From: fayaguana@uce.edu.ec\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

        if (mail($to, $subject, $message, $headers)) {
            echo json_encode(array('message' => 'Usuario creado y correo enviado', 'data' => $data));
        } else {
            echo json_encode(array('message' => 'Usuario creado, pero no se pudo enviar el correo', 'data' => $data));
        }
    } else {
        echo json_encode(array('error' => 'Error creando el usuario'));
    }
}

function deleteData($mysqli, $id) {
    $sql = "DELETE FROM friends WHERE id = $id";
    $result = $mysqli->query($sql);

    if ($result) {
        echo json_encode(array('message' => 'User DELETED'));
    } else {
        echo json_encode(array('error' => 'Error deleting user'));
    }
}

function updateData($mysqli, $id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'];

    $sql = "UPDATE friends SET name = '$name' WHERE id = $id";
    $result = $mysqli->query($sql);

    if ($result) {
        echo json_encode(array('message' => 'User UPDATED', 'id' => $id, 'name' => $name));
    } else {
        echo json_encode(array('error' => 'Error updating data', 'id' => $id));
    }
}

?>
