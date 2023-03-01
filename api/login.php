<?php
require_once 'headers.php';
require_once 'connection.php';
require_once '../autoload.php';

use \Firebase\JWT\JWT;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$data = json_decode(file_get_contents("php://input"));

	$uname = $data->username;
	$pass = $data->password;

	$sql = $conn->query("SELECT * FROM users WHERE username = '$uname'");

	if ($sql->num_rows > 0) {
		$user = $sql->fetch_assoc();

		if (password_verify($pass, $user['password'])) {

			$key = "example_key";
			$payload = array(
				'user_id' => $user['id'],
				'username' => $user['username'],
				'name' => $user['name'],
			);

			$token = JWT::encode($payload, $key);
			http_response_code(200);
			exit(json_encode(['token' => $token]));
		} else {
			http_response_code(401);
			exit(json_encode(array('message' => 'Unauthenticate')));
		}
	} else {
		http_response_code(401);
		exit(json_encode(array('message' => 'Unauthenticate')));
	}
}
