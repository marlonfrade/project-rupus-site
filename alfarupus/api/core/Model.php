<?php

namespace Core;

use \Core\Database;

class Model
{
    private static $dbs;
    private static $transacao;
    private $sql = null;

    public function __construct()
    {
        self::$dbs = Database::getInstances();
    }

    public function insertUpdate($debug = false)
    {
        if ($this->sql == null) {
            throw new \Exception('Sql não definida!');
        }

        try {
            $this->sql->execute();
            if ($debug === true) {
                echo '<pre>';
                print_r($this->sql->debugDumpParams());
                exit;
            }
            $this->sql = null;
            return $this;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function select($fetchType, $debug = false)
    {
        if ($this->sql == null) {
            throw new \Exception('Sql não definida!');
        }

        try {
            $this->sql->execute();
            if ($debug == true) {
                echo '<pre>';
                print_r($this->sql->debugDumpParams());
                exit;
            }
            if ($fetchType == 'fetchAll') {
                $result = $this->sql->fetchAll(\PDO::FETCH_ASSOC);
            } elseif ($fetchType == 'fetch') {
                $result = $this->sql->fetch(\PDO::FETCH_ASSOC);
                if ($result == false) {
                    $result = [];
                }
            } else {
                throw new \Exception('Fetch Type não informado!');
            }
            $this->sql = null;
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function delete()
    {
        if ($this->sql == null) {
            throw new \Exception('Sql não definida!');
        }

        try {
            $this->sql->execute();
            $this->sql = null;
            return $this;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    public function disableKeyVerification($dbname)
    {
        try {
            $sql = self::$dbs[$dbname]->prepare('SET FOREIGN_KEY_CHECKS=0;');
            $sql->execute();
            return $this;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function enableKeyVerification($dbname)
    {
        try {
            $sql = self::$dbs[$dbname]->prepare('SET FOREIGN_KEY_CHECKS=0;');
            $sql->execute();
            return $this;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function lastInsertId($dbname)
    {
        return self::$dbs[$dbname]->lastInsertId();
    }

    public function prepareSql($query, $dbname)
    {
        try {
            $this->sql = self::$dbs[$dbname]->prepare($query);
            return $this;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function bindValues($params, $debug = false)
    {
        $actualKey = '';
        try {
            foreach ($params as $key => $value) {
                $actualKey = $key;
                $this->sql->bindValue(':' . $key, $value);
            }
            if ($debug === true) {
                echo '<pre>';
                print_r($this->sql);
                exit;
            }
            return $this;
        } catch (\Exception $e) {
            throw new \Exception("Key: " . $actualKey . $e->getMessage());
        }
    }

    public static function createBindParams($array, $ignorekey = [])
    {
        $query = '';
        foreach ($array as $key => $value) {
            if (in_array($key, $ignorekey) == false) {
                $query .= '`' . $key . '`' . ' = :' . $key . ', ';
            }
        }

        $query = substr($query, 0, -2) . ' '; //remove os dois ultimos caracters, uma virgula e um espaco ", " e depois aciociona um espaço " "

        return $query;
    }

    public function startTransaction($dbname)
    {
        try {
            if (isset(self::$dbs[$dbname]) && empty(self::$transacao[$dbname])) {
                self::$dbs[$dbname]->beginTransaction();
                self::$transacao[$dbname] = true;
            }
        } catch (\Exception $e) {
            throw new \Exception('Erro ao iniciar uma transação!' . $e->getMessage());
        }
    }

    public function commitTransaction($dbname)
    {
        try {
            if (!empty(self::$dbs[$dbname]) && self::$transacao[$dbname] == true) {
                self::$dbs[$dbname]->commit();
                self::$transacao[$dbname] = false;
            }
        } catch (\Exception $e) {
            throw new \Exception('Erro ao fazer commit da transação!' . $e->getMessage());
        }
    }

    public function rollBackTransaction($dbname)
    {
        try {
            if (!empty(self::$dbs[$dbname]) && self::$transacao[$dbname] == true) {
                self::$dbs[$dbname]->rollBack();
                self::$transacao[$dbname] = false;
            }
        } catch (\Exception $e) {
            throw new \Exception('Erro ao fazer rollback da transação!' . $e->getMessage());
        }
    }


    public function verifyQueryPermission($query, $column = 'user_id')
    {

        if ($_SESSION['type'] != 'Administrador') {
            $columns[$column] = $_SESSION['user_id'];
            return  array(
                $query .= ' AND user_id = :user_id',
                $columns[$column] = $_SESSION['user_id']
            );
        } else {
            return  array(
                $query,
                $columns[] = ''
            );
        }
    }

    public function getData()
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        return $data;
    }
}