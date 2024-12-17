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
        selectData($mysqli, $id);
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
        echo "Method not allowed";
        break;
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
    print_r($name);

    $sql = "INSERT INTO friends(name) VALUES ('$name')";
    $result = $mysqli->query($sql);

    if ($result) {
        $data['id'] = $mysqli->insert_id; // Correct variable
        echo json_encode($data);
    } else {
        echo json_encode(array('error' => 'Error creating user'));
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
