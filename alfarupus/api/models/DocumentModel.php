<?php

namespace Models;

use \Core\Model;

class DocumentModel extends Model
{

    public function fetchAllDocumentModel()
    {
        try {
            $sql = 'SELECT * FROM pocos_modelo_documento
                        ORDER BY documento ASC';
            $data = $this->prepareSql($sql, 'rupus')->select('fetchAll');

            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }

}
