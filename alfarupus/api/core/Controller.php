<?php

namespace Core;

use \Helpers\JWT;
use \Models\UserModel;
use \Core\Model;

class Controller extends Model
{
    public $classJWT;
    public $dataJWT;

    function __construct()
    {
        try {
            $this->classJWT = new JWT();
            $this->verifyToken();

            $this->dataJWT = $this->classJWT->getDataJWT();
        } catch (\Exception $e) {
            $res = [
                'data' => [],
                'error' => $e->getMessage()
            ];
            $this->sendData($res);
            exit;
        }
    }

    //pega os dados da requisião e retorna formatado em array
    public function verifyToken()
    {
        try {
            $JWT = $this->getJWTFromHeader();

            $UserModel = new UserModel();

            //instancia a classe JWT pro controler da autorização, por que lá o controller nao inicia o construtor desta classe com o "parent::__construct();"
            if (empty($this->classJWT)) {
                $this->classJWT = new JWT();
            }

            if (empty($JWT)) {
                throw new \Exception('A JWT não foi enviada!', 1);
            } else {
                $this->classJWT->openJWT($JWT);
            }

            $user_id = $this->classJWT->sub;

            $userData = $UserModel->getUserDataById($user_id);

            $res = $this->classJWT->validateJWT($userData);
        } catch (\Exception $e) {

            $res = [
                'data' => [],
                'error' => $e->getMessage()
            ];

            $this->sendData($res);
            exit;
        }
    }

    //pega a token JWT do headers da requisição
    public function getJWTFromHeader()
    {
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $autorization = $_SERVER['HTTP_AUTHORIZATION'];
            $bearer = explode(' ', $autorization)[0];

            if ($bearer != 'Bearer') {
                throw new \Exception('Autorization Inválida', 1);
            }

            $JWT = explode(' ', $autorization)[1];

            return $JWT;
        } else {
            throw new \Exception('A autorização não foi enviada!', 1);
        }
    }

    //pega os dados da requisião e retorna formatado em array
    public function getData()
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        return $data;
    }

    //envia a resposta da requisição
    public function sendData($res, $format = 'JSON')
    {
        switch ($format) {
            case 'JSON':
                header('Content-type: application/json');
                echo json_encode($res);
                break;
        }
    }


    //função pra validar uma string/array de strings
    public function secureString($data, $secureType = 'all', $required = null)
    {

        if (!empty($required) && empty($data)) {

            if (gettype($data) == 'array') {
                throw new \Exception('Pelo menos um dos campos não foi informado!', 1);
            } else {
                throw new \Exception('O campo "' . $required . '" não foi informado!', 1);
            }
        }

        if (gettype($data) == 'array') {

            foreach ($data as $key => $value) {

                switch ($secureType) {
                    case 'all':
                        $data[$key] = addslashes(strip_tags(trim($value))); //executa todas as verificações abaixo com excessão do email
                        break;
                    case 'script':
                        $data[$key] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $value); //remove tags scripts
                        break;
                    case 'html':
                        $data[$key] = strip_tags($value); //remove tags html
                        break;
                    case 'strings':
                        $data[$key] = trim($value); //remove espaços no começo e no final das strings
                        break;
                    case 'email':
                        $data[$key] = filter_var(trim($value), FILTER_VALIDATE_EMAIL); //valida um email
                        if (empty($data[$key])) {
                            throw new \Exception('O E-mail informado é inválido!', 1);
                        }
                        break;
                }
                if (empty($data[$key])) {
                    $data[$key] = NULL;
                }
            }
        } elseif (gettype($data) == 'string') {

            switch ($secureType) {
                case 'all':
                    $data = addslashes(strip_tags(trim($data))); //executa todas as verificações abaixo com excessão do email
                    break;
                case 'script':
                    $data = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $data); //remove tags scripts
                    break;
                case 'html':
                    $data = strip_tags($data); //remove tags html
                    break;
                case 'strings':
                    $data = trim($data); //remove espaços no começo e no final das strings
                    break;
                case 'email':
                    $data = filter_var($data, FILTER_VALIDATE_EMAIL); //valida um email
                    if (empty($data) && !empty($required)) {
                        throw new \Exception('O E-mail informado é inválido!', 1);
                    }
                    break;
            }
            if (empty($data)) {
                $data = NULL;
            }
        }

        return $data;
    }

    // verifica se o acesso é de um dispositivo movel
    public function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|iPhone|iphone|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", strtolower($_SERVER['HTTP_USER_AGENT']));
    }


    public function log($msg, $call_function, $type = 'log')
    {
        $classModel = new Model();

        $params['request'] = json_encode($_REQUEST ?? ['error ao pegar $_REQUEST']);

        $params['get'] = $_GET ?? [];
        $params['post'] = $_POST ?? [];
        $params['request_body'] = $this->getData();
        $params['http_authorization'] = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $params['request_uri'] = $_SERVER['REQUEST_URI'] ?? '';
        $params['http_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $columns['type'] = $type;
        $columns['data'] = json_encode($params);
        $columns['message'] = $msg;
        $columns['client_ip'] = $_SERVER['REMOTE_ADDR'];
        $columns['user_id'] = $this->dataJWT['user_id'] ?? NULL;
        $columns['call_function'] = $call_function;


        $query = "INSERT INTO log SET call_function = :call_function, type = :type, data = :data, message = :message, client_ip = :client_ip, user_id = :user_id";

        try {
            $classModel->prepareSql($query, 'rupus')->bindValues($columns)->insertUpdate();
            $id = $classModel->lastInsertId('rupus');

            return $id;
        } catch (\Exception $e) {
            $this->sendData([
                'error' => $e->getMessage(),
                'data' => []
            ]);
            exit;
        }
    }
}
