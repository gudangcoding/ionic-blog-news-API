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

        // GET ALL AND SIGNLE NOTE
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_GET['id'])) { // id used to fetch single row
                $id = $conn->real_escape_string($_GET['id']);
                $sql = $conn->query("SELECT * FROM posts WHERE id = '$id'");
                $data = $sql->fetch_assoc();
            } else {
                // fetch all rows
                $data = array();
                $sql = $conn->query("SELECT * FROM posts");
                while ($d = $sql->fetch_assoc()) {
                    $data[] = $d;
                }
            }

            exit(json_encode($data)); //return json data
        }

        // CREATE NEW NOTE
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $description = $_POST['description'];

            // UPLOAD
            $file = $_FILES['image'];
            move_uploaded_file($file['tmp_name'], '../images/' . urlencode($file['name']));
            $image = 'images/' . urlencode($file['name']);

            // SAVE TO DATABASE
            $sql = $conn->query("INSERT INTO posts (title, image, description, user_id) VALUES ('" . $title . "', '" . $image . "', '" . $description . "', '" . $decoded->user_id . "')");
            if ($sql) {
                $data = array(
                    'id' => $conn->insert_id,
                    'title' => $title,
                    'description' => $description,
                    'author' => $decoded->name,
                    'image' => $image
                );
                exit(json_encode($data));
            } else {
                exit(json_encode(array('status' => mysqli_error($conn))));
            }
        }

        // UPDATE NOTE
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            if (isset($_GET['id'])) {
                $id = $conn->real_escape_string($_GET['id']);
                $data = json_decode(file_get_contents("php://input"));
                $sql = $conn->query("UPDATE posts SET title = '" . $data->title . "', description = '" . $data->description . "' WHERE id = '$id'");
                if ($sql) {
                    exit(json_encode(array('status' => 'success')));
                } else {
                    exit(json_encode(array('status' => 'error')));
                }
            }
        }

        // DELETE NOTE
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            if (isset($_GET['id'])) {
                $id = $conn->real_escape_string($_GET['id']);
                $sql = $conn->query("DELETE FROM posts WHERE id = '$id'");

                if ($sql) {
                    exit(json_encode(array('status' => 'success')));
                } else {
                    exit(json_encode(array('status' => 'error')));
                }
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
