<?php

namespace Models;

use \Core\Model;

class ClientProviderModel extends Model
{

    public function fetchClientProviderById($columns)
    {
        try {
            $sql = 'SELECT * FROM pocos_cliente_fornecedor WHERE id = :id';
            $data = $this->prepareSql($sql, 'rupus')->bindValues($columns)->select('fetch');

            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }

    public function fetchAllClientProvider()
    {
        try {
            $sql = 'SELECT * FROM pocos_cliente_fornecedor
                        ORDER BY name ASC';
            $data = $this->prepareSql($sql, 'rupus')->select('fetchAll');

            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }


    public function fetchAllProvider()
    {
        try {
            $sql = 'SELECT * FROM pocos_cliente_fornecedor
                        WHERE tipo_cadastro = \'F\' OR tipo_cadastro = \'A\'
                            ORDER BY name ASC';
            $data = $this->prepareSql($sql, 'rupus')->select('fetchAll');

            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }

    public function fetchAllClient()
    {
        try {
            $sql = 'SELECT * FROM pocos_cliente_fornecedor
                        WHERE tipo_cadastro = \'C\' OR tipo_cadastro = \'A\'
                            ORDER BY name ASC';
            $data = $this->prepareSql($sql, 'rupus')->select('fetchAll');

            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }
}
