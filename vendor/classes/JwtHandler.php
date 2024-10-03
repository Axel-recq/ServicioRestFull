<?php
require './vendor/autoload.php';
use Firebase\jwt\jwt;

Class JwtHandler{
    protected $jwt_secret;
    protected $token;
    protected $issuedAt;
    protected $expire;
    protected $jwt;
    public function __construct()
    {
        // Configuración de la zona horaria por defecto
        date_default_timezone_set('America/Lima');
        $this->issuedAt = time();

        // Validez del token (3600 segundos = 1 hora)
        $this->expire = $this->issuedAt + 3600; 

        // Establece tu secreto para el token o firma
        $this->jwt_secret = "this_is_my_secret";
    }
    public function jwtEncondeData($iss, $data){
        $this->token = array(
            // Añadiendo el identificador al token (quien emite el token)
            "iss" => $iss,
            "aud" => $iss,
            // Añadiendo la marca de tiempo actual al token, para identificar cuándo fue emitido.
            "iat" => $this->issuedAt,
            // Expiración del token
            "exp" => $this->expire,
            // Carga útil
            "data" => $data
        );
        $this->jwt = JWT::encode($this->token, $this->jwt_secret, 'HS256');
        return $this->jwt;
    }
    public function jwtDecodeData($jwt_token)
    {
        try {
            $decode = JWT::decode($jwt_token, $this->jwt_secret, array('HS256'));
            return [
                "data" => $decode->data
            ];
        } catch (Exception $e) {
            return [
                "message" => $e->getMessage()
            ];
        }
    }
}
?>
