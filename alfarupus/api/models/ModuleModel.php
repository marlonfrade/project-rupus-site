<?php

namespace Models;

use \Core\Model;

class ModuleModel extends Model
{

    public function createModule($columns)
    {
        try {
            $sql = 'INSERT INTO module SET ' . $this->createBindParams($columns);
            $module_id = $this->prepareSql($sql, 'rupus')->bindValues($columns)->insertUpdate()->lastInsertId('rupus');

            return $module_id;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }

    public function fetchModuleById(int $id)
    {
        $columns = ['id' => $id];
        try {
            $sql = 'SELECT * FROM module WHERE id = :id';
            $data = $this->prepareSql($sql, 'rupus')->bindValues($columns)->select('fetch');

            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }

    public function fetchAllModuleItemByModuleId($module_id)
    {
        $columns['module_id'] = $module_id;
        try {
            $sql = 'SELECT * FROM module_item WHERE module_id = :module_id';
            $data = $this->prepareSql($sql, 'rupus')->bindValues($columns)->select('fetchAll');

            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }

    public function fetchAllModules(int $user_id)
    {
        $data = [];
        try {
            $sql = 'SELECT * FROM module ORDER BY id ASC';
            $modules = $this->prepareSql($sql, 'rupus')->select('fetchAll');
            foreach ($modules as $key => $module) {
                $data[$key]['module'] = $module;
                $data[$key]['module']['status'] = $this->validadeModule($module['id'], $user_id);
                $data[$key]['module_item'] = $this->fetchAllModuleItemByModuleId($module['id']);
            }
            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }

    public function fetchQtdQuestionByModuleId(int $module_id)
    {
        $columns['module_id'] = $module_id;
        try {
            $sql = 'SELECT count(id) as qtd FROM question WHERE module_id = :module_id';
            $res = $this->prepareSql($sql, 'rupus')->bindValues($columns)->select('fetch');

            return $res['qtd'];
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }
    public function fetchQtdQuestionAnsweredCorrectByModuleIdAndUserId(int $module_id, int $user_id)
    {
        $columns['module_id'] = $module_id;
        $columns['user_id'] = $user_id;
        try {
            $sql = 'SELECT count(module_id) as qtd FROM answer_has_user WHERE module_id = :module_id AND user_id = :user_id AND status = 1';
            $res = $this->prepareSql($sql, 'rupus')->bindValues($columns)->select('fetch');

            return $res['qtd'];
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }
    public function deleteAnswerByUserIdAndQuestionId(int $question_id, int $user_id)
    {
        $columns['question_id'] = $question_id;
        $columns['user_id'] = $user_id;
        try {
            $sql = 'DELETE FROM answer_has_user WHERE question_id = :question_id AND user_id = :user_id';
            $this->prepareSql($sql, 'rupus')->bindValues($columns)->delete();

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }



    public function fetchAnswerByQuestionId($question_id)
    {
        $columns['question_id'] = $question_id;

        try {
            $sql = 'SELECT id, answer, question_id FROM answer WHERE question_id = :question_id';
            $data = $this->prepareSql($sql, 'rupus')->bindValues($columns)->select('fetchAll');
            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }

    public function fetchQuestionsByModuleId($module_id)
    {
        $columns['module_id'] = $module_id;
        try {
            $sql = 'SELECT * FROM question WHERE module_id = :module_id';
            $data = $this->prepareSql($sql, 'rupus')->bindValues($columns)->select('fetchAll');

            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }

    public function verifyModuleAnswered($module_id, $user_id)
    {
        $columns['module_id'] = $module_id;
        $columns['user_id'] = $user_id;

        try {
            $sql = 'SELECT * FROM answer_has_user WHERE module_id = :module_id AND user_id = :user_id';
            $data = $this->prepareSql($sql, 'rupus')->bindValues($columns)->select('fetchAll');

            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }

    public function fetchAnswerById($answer_id)
    {
        $columns['answer_id'] = $answer_id;
        try {
            $sql = 'SELECT question.id AS question_id, module.id AS module_id, answer.*
                        FROM answer
                            INNER JOIN question ON (question.id = answer.question_id)
                            INNER JOIN module ON (module.id = question.module_id)
                        WHERE answer.id = :answer_id';
            $data = $this->prepareSql($sql, 'rupus')->bindValues($columns)->select('fetch');

            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getmessage());
        }
    }

    public function validadeModule($module_id, $user_id)
    {
        $qtd_questions = $this->fetchQtdQuestionByModuleId($module_id);
        $qtd_questions_answered_correct = $this->fetchQtdQuestionAnsweredCorrectByModuleIdAndUserId($module_id, $user_id);
        if (!empty($qtd_questions) && !empty($qtd_questions_answered_correct)  && $qtd_questions == $qtd_questions_answered_correct) {
            return true;
        }
        return false;
    }



}
