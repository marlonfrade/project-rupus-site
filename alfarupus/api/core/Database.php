<?php
namespace Core;

use \Core\Config;

class Database {

    //instancias do banco de dados
    public static function getInstances() {
        $pdo = [];
        try {

            foreach (Config::DBS() as $dbAccessName => $info) {
                $db = new \PDO($info['driver'] . ":dbname=" . $info['db'] . ";host=" . $info['host'],
                                                              $info['user'],
                                                              $info['pass']);
                $db->exec("SET NAMES 'utf8'");
                $db->exec('SET character_set_connection=utf8');
                $db->exec('SET character_set_client=utf8');
                $db->exec('SET character_set_results=utf8');
                $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo[$dbAccessName] = $db;
            }
            unset($db);

            return $pdo;
        } catch (\Exception $e) {
            echo 'error: ' . $e->getMessage();
            exit;
        }
    }

}