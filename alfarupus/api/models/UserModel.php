<?php

namespace Models;

use \Core\Model;
use \Models\ModuleModel;

class UserModel extends Model {

    private $ModuleModel;

    function __construct() {
        $this->ModuleModel = new ModuleModel();
    }

    public function getUserDataById(int $user_id) {

        $columns = ['id' => $user_id];
        $sql = "SELECT * FROM user WHERE id = :id";

        try {
            $res = $this->prepareSql($sql, 'rupus')->bindValues($columns)->select('fetch');
            return $res;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function fetchUserInfo(int $user_id) {
        $columns = ['id' => $user_id];
        $sql = "SELECT * FROM user WHERE id = :id";

        try {
            $user = $this->prepareSql($sql, 'rupus')->bindValues($columns)->select('fetch');
            unset($user['password']);
            unset($user['token']);
            unset($user['type']);
            unset($user['iat']);
            unset($user['deleted']);
            unset($user['cpf']);
            unset($user['balance']);
            $modules = $this->ModuleModel->fetchAllModules($user_id);
            $user['qtd_modules'] = count($modules);
            $user['modules'] = [];
            foreach ($modules as $module) {
                $user['modules'][]=[
                    'module_id' => $module['module']['id'],
                    'qtd_questions' => $this->ModuleModel->fetchQtdQuestionByModuleId($module['module']['id']),
                    'qtd_questions_answered_correct' => $this->ModuleModel->fetchQtdQuestionAnsweredCorrectByModuleIdAndUserId($module['module']['id'], $columns['id'])
                ];
            }

            return $user;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function saveAnswer($answer_id, $question_id, $module_id, $status, $user_id) {
        $columns = [
            'answer_id' => $answer_id,
            'question_id' => $question_id,
            'module_id' => $module_id,
            'user_id' => $user_id,
            'status' => $status,
        ];

        $sql = "INSERT INTO answer_has_user SET answer_id = :answer_id, question_id = :question_id, module_id = :module_id, status = :status, user_id = :user_id";

        try {
            $res = $this->prepareSql($sql, 'rupus')->bindValues($columns)->insertUpdate();
            return $res;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
