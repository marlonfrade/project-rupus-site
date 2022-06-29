<?php

namespace Controller;

use \Core\Controller;
use \Helpers\JWT;
use \Models\AuthenticationModel;

class AuthenticationController extends Controller
{
    private $AuthenticationModel;

    function __construct() {
        $this->AuthenticationModel = new AuthenticationModel();
    }

    public function signOut() {
        $res = ['error' => 0, 'data' => []];

        try {
            $this->classJWT = new JWT();
            $this->verifyToken();

            $this->dataJWT = $this->classJWT->getDataJWT();

            $col['user_id'] = $this->dataJWT['user_id'];

            $res['data'] = $this->AuthenticationModel->signOut($col);
            $this->log('Usuário deslogado!', 'signOut');
        } catch (\Exception $e) {
            $this->log($e->getMessage(), 'signOut', 'error');
            $res['error'] = 'Erro ao fazer logout';
        }

        $this->sendData($res);
        exit;
    }

    public function signIn() {
        $res = ['error' => 0, 'data' => []];
        try {
            $data = $this->getData();

            $col['cpf']      = $this->secureString($data['cpf'] ?? '', 'all');

            if (empty($data['device_id'])) {
                $col['device_id'] = $this->secureString(md5($_SERVER['HTTP_USER_AGENT']) ?? '');
            }else{
                $col['device_id'] = $this->secureString($data['device_id'] ?? '', 'all', 'Device Id');
            }

            $res['data'] = $this->AuthenticationModel->signIn($col);
            $this->log('Usuário logado!', 'signIn');
        } catch (\Exception $e) {
            $this->log($e->getMessage(), 'signIn', 'error');
            $res['error'] = 'Erro ao fazer login!';
        }

        $this->sendData($res);
        exit;
    }

    public function validate() {
        $this->log('Validação de token!', 'validate');
        $this->verifyToken();

        $this->sendData([
            'data' => 'JWT validada com sucesso!',
            'error' => 0
        ]);
        exit;

    }

}
