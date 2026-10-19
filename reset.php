<?php

header("Access-Control-Allow-Origin: *");

// Permitir métodos de solicitação específicos
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

header("Access-Control-Allow-Headers: Content-Type");

// Definir o tempo de vida do cabeçalho CORS em cache (em segundos)
header("Access-Control-Max-Age: 86400");

header('Content-Type: application/json; charset=utf-8');

$token = isset($_GET["token"]) ? $_GET["token"] : '';
$current_password = isset($_GET["current_password"]) ? $_GET["current_password"] : '';

if (!is_string($token) || !is_string($current_password) || $token === '' || $current_password === '') {
    http_response_code(400);
    echo json_encode(array("results" => array(array('status' => 400))));
    exit;
}

if (isset($_GET["password"])) {
    
    $password = $_GET["password"];

}

if (isset($_GET["username"])) {
    
    $username = $_GET["username"];

}

$output = array("results" => array());

$mysqli = mysqli_connect('localhost', 'root', 'senhasenha', 'yrprey');

/* Check connection before executing the SQL query */
if ($mysqli->connect_errno) {
printf("Connect failed: %s\n", $mysqli->connect_error);
exit();
}

            $query = "SELECT id FROM users WHERE token = ? AND password = ?";
            $stmt = mysqli_prepare($mysqli, $query);
            if ($stmt === false) {
                http_response_code(500);
                echo json_encode(array("results" => array(array('status' => 500))));
                exit;
            }
            mysqli_stmt_bind_param($stmt, 'ss', $token, $current_password);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $authorized = (mysqli_stmt_num_rows($stmt) > 0);
            mysqli_stmt_close($stmt);

            if ($authorized) {

            $output = array("results" => array());

            if (!empty($username)) {
                $query  = "UPDATE users SET username = ? WHERE token = ?";
                $stmt2 = mysqli_prepare($mysqli, $query);
                if ($stmt2 !== false) {
                    mysqli_stmt_bind_param($stmt2, 'ss', $username, $token);
                    mysqli_stmt_execute($stmt2);
                    mysqli_stmt_close($stmt2);
                }
            }

            if (!empty($password)) {
                $query  = "UPDATE users SET password = ? WHERE token = ?";
                $stmt3 = mysqli_prepare($mysqli, $query);
                if ($stmt3 !== false) {
                    mysqli_stmt_bind_param($stmt3, 'ss', $password, $token);
                    mysqli_stmt_execute($stmt3);
                    mysqli_stmt_close($stmt3);
                }
            }

                $output = array("results" => array());

                $array = array(
                    'status' => 200
                );    
            }
            else {

                
                $array = array(
                    'status' => 400
                );  
            }
            
array_push($output["results"], $array);

    echo json_encode($output, 128);
    
    ?>