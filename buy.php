<?php

header("Access-Control-Allow-Origin: *");

// Permitir métodos de solicitação específicos
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

header("Access-Control-Allow-Headers: Content-Type");

// Definir o tempo de vida do cabeçalho CORS em cache (em segundos)
header("Access-Control-Max-Age: 86400");

header('Content-Type: application/json; charset=utf-8');

//$username = $_GET["nome"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém o JSON enviado pelo cliente e converte em um objeto PHP
    $jsonPayload = file_get_contents('php://input');
    $data = json_decode($jsonPayload);

    // Verifica se o JSON foi decodificado corretamente
    if ($data === null) {
        // Se houve um erro ao decodificar o JSON, envie uma resposta de erro
        http_response_code(400); // Requisição inválida
        echo json_encode(array('error' => 'JSON inválido'));
        exit;
    }

    // Agora você pode acessar as propriedades do objeto como desejar
    $token = isset($data->token) && is_string($data->token) ? $data->token : '';
    $requestedPrice = isset($data->price) && is_numeric($data->price) ? (float)$data->price : null;

    if ($token === '' || $requestedPrice === null || $requestedPrice < 0) {
        http_response_code(400);
        echo json_encode(array('error' => 'Dados inválidos'));
        exit;
    }
    
}

$output = array("results" => array());

$mysqli = mysqli_connect('localhost', 'root', 'senhasenha', 'yrprey');

/* Check connection before executing the SQL query */
if ($mysqli->connect_errno) {
printf("Connect failed: %s\n", $mysqli->connect_error);
exit();
}
$token = isset($token) && is_string($token) ? $token : '';
$requestedPrice = isset($requestedPrice) && is_numeric($requestedPrice) ? (float)$requestedPrice : null;
$status = 400;
$msg = 'Requisição inválida';
$saldo = -1;
$price = null;
$userFound = false;

$userStmt = $mysqli->prepare('SELECT saldo FROM users WHERE token = ? LIMIT 1');
if ($userStmt !== false) {
    $dbSaldo = null;
    $userStmt->bind_param('s', $token);
    $userStmt->execute();
    $userStmt->bind_result($dbSaldo);
    if ($userStmt->fetch()) {
        $userFound = true;
        $saldo = (float)$dbSaldo;
    }
}

if ($userFound) {
    $priceStmt = $mysqli->prepare('SELECT price FROM produtos WHERE price = ? LIMIT 1');
    if ($priceStmt !== false) {
        $dbPrice = null;
        $priceStmt->bind_param('d', $requestedPrice);
        $priceStmt->execute();
        $priceStmt->bind_result($dbPrice);
        if ($priceStmt->fetch()) {
            $price = (float)$dbPrice;
        }
    }
}

if (!$userFound) {
    $status = 400;
    $msg = 'Usuário não encontrado!';
    $saldo = -1;
    $price = 1;
} elseif ($price === null || $price <= 0) {
    $status = 400;
    $msg = 'Preço inválido!';
    $saldo = -1;
    $price = 1;
} elseif ($saldo < $price) {
    $status = 400;
    $msg = 'Você não possui saldo!';
}

$token = mysqli_real_escape_string($mysqli, $token);
if ($saldo >= $price) {

            $query  = "UPDATE users SET saldo=saldo-$price WHERE token = '$token' AND saldo >= $price";
            $debitado = mysqli_query($mysqli, $query);  

            if ($debitado === TRUE && mysqli_affected_rows($mysqli) > 0) {

                $status = 200;
                $msg = "Compra realizada com sucesso!";

                $q  = "INSERT INTO historico (`id`, `token`, `valor`) VALUES (NULL, '$token', '$price')";
            
                $exec = mysqli_query($mysqli, $q    );

            }
            else {

                $status = 400;
                $msg = "Você não possui saldo!";

            }
                
            $output = array("results" => array());
}
        
            $array = array(
                'status' => $status,
                'msg' => $msg
            );    

array_push($output["results"], $array);

    echo json_encode($output, 128);
    
    ?>