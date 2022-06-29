<?php

namespace Models;

use \Core\Model;
use \Helpers\JWT;

class AuthenticationModel extends Model {

    private $classJWT;

    public function __construct() {
        $this->classJWT = new JWT();
        parent::__construct(); //chama o construct da classe Model
    }


    public function signIn($columns) {
        $device_id = $columns['device_id'];
        unset($columns['device_id']);

        $whereSql = ' cpf = :cpf';
        if (empty($columns['cpf'])) {
            $whereSql = 'email = :email AND password = :password ';
            unset($columns['cpf']);
        }

        $sql = "SELECT * FROM user WHERE $whereSql AND deleted = 'N'";

        try {
            $dataSignIn = $this->prepareSql($sql, 'rupus')->bindValues($columns)->select('fetch');

            if (!empty($dataSignIn)) {

                $col['id'] = $dataSignIn['id'];
                $col['token'] = $device_id;
                $col['iat'] = strtotime(date('Y-m-d H:i:s'));

                $token = $this->setUserToken($col);

                if ($token === true) {
                    $payload = [
                        'iss' => 'localhost',
                        'aud' => $device_id,
                        'sub' => $dataSignIn['id'],
                        'name' => $dataSignIn['name'],
                        'email' => $dataSignIn['email'],
                        'iat' => $col['iat']
                    ];

                    $JWT = $this->classJWT->setPayloadData($payload)->generatePayload()->generateHeader()->generateSignature()->create()->JWT;

                    $ret = [
                        'JWT' => $JWT,
                        'user_id' => $dataSignIn['id'],
                        'name' => $dataSignIn['name'],
                        'email' => $dataSignIn['email']
                    ];

                    return $ret;
                }

            } else {
                throw new \Exception('Email ou senha inválidos!', 1);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    public function setUserToken($columns) {

        $sql = "UPDATE user SET token = :token, iat = :iat WHERE id = :id AND deleted = 'N'";

        try {
            $this->prepareSql($sql, 'rupus')->bindValues($columns)->insertUpdate();
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    public function signOut($columns) {

        $sql = "UPDATE user SET token = null, iat = null WHERE id = :user_id";

        try {
            $this->prepareSql($sql, 'rupus')->bindValues($columns)->insertUpdate();
            return 'Usuário deslogado com sucesso!';
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


}
