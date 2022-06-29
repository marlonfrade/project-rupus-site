<?php
namespace core;

require(__DIR__.'/Environment.php');

class Config {
    CONST BASE_DIR_PROJECT = __DIR__;

    static function BASE_DIR_UPLOADS() {
        if (ENVIRONMENT == 'DEV') {
            return __DIR__ . '';
        }elseif (ENVIRONMENT == 'PROD') {
            return __DIR__ . '';
        }
    }

    static function DBS() {
        if (ENVIRONMENT == 'DEV') {
            return [
                'rupus' => [
                    //driver da conexao com o banco de dados
                    'driver' => 'mysql',
                    //host da conexao com o banco de dados
                    'host' => 'localhost:3306',
                    //nome do banco de dados
                    'db' => 'rupus',
                    //usuario do banco de dados
                    'user' => 'monkey',
                    //senha do banco de dados
                    'pass' => '321'
                ]
            ];
        }elseif (ENVIRONMENT == 'PROD') {
            return [
                'rupus' => [
                    //driver da conexao com o banco de dados
                    'driver' => 'mysql',
                    //host da conexao com o banco de dados
                    'host' => '186.202.152.11',
                    //nome do banco de dados
                    'db' => 'alfarupus',
                    //usuario do banco de dados
                    'user' => 'alfarupus',
                    //senha do banco de dados
                    'pass' => 'M0nkey_615243'
                ]
            ];
        }
    }

}
