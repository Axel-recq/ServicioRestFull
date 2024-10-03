<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/vendor/classes/Database.php';
require __DIR__ . '/vendor/classes/JwtHandler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();

function msg($success, $status, $message, $extra = []) {
    return array_merge(
        ['success' => $success, 
        'status' => $status, 
        'message' => $message],
        $extra
    );
}

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

// IF REQUEST METHOD IS NOT EQUAL TO POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $returnData = msg(0, 404, 'Page Not Found!');
} 
// CHECKING EMPTY FIELDS
elseif (!isset($data->email) || !isset($data->password) || empty(trim($data->email)) || empty(trim($data->password))) {
    $fields = ['fields' => ['email', 'password']];
    $returnData = msg(0, 422, 'Please Fill in all Required Fields!', $fields);
} 
// Si no hay campos vacíos
else {
    $email = trim($data->email);
    $password = trim($data->password);

    // Verificando el formato del email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $returnData = msg(0, 442, 'Invalid Email Address!');
    } elseif (strlen($password) < 8) {
        // Si la contraseña es menor a 8 caracteres
        $returnData = msg(0, 422, 'Your password must be at least 8 characters long!');
    } else {
        // Usuario puede realizar la acción de inicio de sesión
        try {
            $fetch_user_by_email = "SELECT * FROM `users` WHERE `email` = :email";
            $query_stmt = $conn->prepare($fetch_user_by_email);
            $query_stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $query_stmt->execute();

            // Si el usuario es encontrado por el email
            if ($query_stmt->rowCount()) {
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                $check_password = password_verify($password, $row['password']);

                // Verificando la contraseña
                if ($check_password) {
                    // Si la contraseña es correcta, enviar el token de inicio de sesión
                    $jwt = new JwtHandler();
                    $token = $jwt->jwtEncodeData(
                        'http://localhost/php_auth_api/',
                        array("user_id" => $row['id'])
                    );
                    $returnData = [
                        'success' => 1,
                        'message' => 'You have successfully logged in.',
                        'token' => $token
                    ];
                } else {
                    // Si la contraseña es inválida
                    $returnData = msg(0, 422, 'Invalid Password!');
                }
            } else {
                // Si no se encuentra el usuario
                $returnData = msg(0, 422, 'User not found!');
            }
        } catch (PDOException $e) {
            $returnData = msg(0, 500, $e->getMessage());
        }
    }
}

echo json_encode($returnData);
?>
