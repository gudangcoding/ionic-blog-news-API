<?php
require_once 'headers.php';
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    $name = $data->name;
    $username = $data->username;
    $password = $data->password;

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // U can do validation like unique username etc....

    $sql = $conn->query("INSERT INTO users (name, username, password) VALUES ('$name', '$username', '$hashed_password')");
    if ($sql) {
        http_response_code(201);
        echo json_encode(array('message' => 'User created'));
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Internal Server error'));
    }
}
