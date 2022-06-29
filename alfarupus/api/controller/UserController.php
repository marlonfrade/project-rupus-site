<?php

namespace Controller;

use \Core\Controller;
use \Models\UserModel;

class UserController extends Controller
{
    private $UserModel;

    function __construct()
    {
        parent::__construct();
        $this->UserModel = new UserModel();
    }


    public function fetchUserInfo()
    {
        $res = ['error' => 0, 'data' => []];

        try {
            $this->log('Busca de dados do usuário!', 'fetchUserInfo');
            $res['data'] = $this->UserModel->fetchUserInfo($this->dataJWT['user_id']);

        } catch (\Exception $e) {
            $this->log($e->getMessage(), 'fetchAllUser');
            $res['error'] = $e->getMessage();
        }

        $this->sendData($res);
        exit;
    }

}
