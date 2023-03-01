<?php
require_once 'headers.php';
require_once 'connection.php';
require_once '../autoload.php';

use Firebase\JWT\JWT;

if (isset(getallheaders()['Authorization'])) {
    try {
        $authHeader = getallheaders()['Authorization'];
        $token = explode(" ", $authHeader)[1];
        $decoded = JWT::decode($token, 'example_key', array('HS256'));

        // GET COMMENTS
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_GET['id'])) {
                $id = $conn->real_escape_string($_GET['id']);
                $data = array();
                $sql = $conn->query("SELECT * FROM comments JOIN users ON users.id = comments.user_id WHERE post_id = '$id'");
                while ($comment = $sql->fetch_assoc()) {
                    $data[] = array('text' => $comment['text'], 'author' => $comment['name']);
                }
            }

            exit(json_encode($data)); //return json data
        }

        // CREATE COMMENT
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"));
            $sql = $conn->query("INSERT INTO comments (post_id, user_id, text) VALUES ('" . $data->post_id . "', '" . $decoded->user_id . "', '" . $data->text . "')");
            if ($sql) {
                $response = array('text' => $data->text, 'author' => $decoded->name);
                exit(json_encode($response));
            } else {
                exit(json_encode(array('status' => 'error: ' . mysqli_error($conn))));
            }
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array(
            "message" => "Please authenticate."
        ));
    }
} else {
    http_response_code(401);
    echo json_encode(array(
        "message" => "Please authenticate."
    ));
}
