<?php

namespace Controller;

use \Core\Controller;
use \Models\ModuleModel;
use Models\UserModel;

class ModuleController extends Controller
{
    private $ModuleModel;

    function __construct()
    {
        parent::__construct();
        $this->ModuleModel = new ModuleModel();
        $this->UserModel = new UserModel();
    }

    public function fetchModuleById($id)
    {
        $res = ['error' => 0, 'data' => []];

        try {
            $this->log('Busca de modulo por id!', 'fetchModuleById');
            $col['id'] = $this->secureString($id, 'all', 'ID');
            $res['data'] = $this->ModuleModel->fetchModuleById($id);
        } catch (\Exception $e) {
            $this->log($e->getMessage(), 'fetchModuleById');
            $res['error'] = $e->getMessage();
        }

        $this->sendData($res);
        exit;
    }

    public function fetchAllModules()
    {
        $res = ['error' => 0, 'data' => []];

        try {
            $this->log('Busca todos os  modulos!', 'fetchAllModules');
            $res['data'] = $this->ModuleModel->fetchAllModules($this->dataJWT['user_id']);
        } catch (\Exception $e) {
            $this->log($e->getMessage(), 'fetchAllModules');
            $res['error'] = $e->getMessage();
        }

        $this->sendData($res);
        exit;
    }

    public function validateAnswer()
    {
        $this->log('Valida respostas!', 'validadeAnswer');
        $res = ['error' => 0, 'data' => []];
        $QAs = [];
        $user_id = $this->dataJWT['user_id'];
        foreach ($_POST as $post) {
            $post = json_decode($post[0], true);
            $this->ModuleModel->deleteAnswerByUserIdAndQuestionId($post['question'], $user_id);
            $answer = $this->ModuleModel->fetchAnswerById($post['answer']);
            $this->UserModel->saveAnswer($answer['id'], $answer['question_id'], $answer['module_id'], $answer['status'], $user_id);
            $QAs['answer' . $post['answer']] = $post;
            $QAs['answer' . $post['answer']]['result'] = $answer['status'];
        }

        try {
            $res['data'] = $QAs;
        } catch (\Exception $e) {
            $this->log($e->getMessage(), 'fetchAllModules');
            $res['error'] = $e->getMessage();
        }

        $this->sendData($res);
        exit;
    }

    public function createModule()
    {
    }

    public function fetchQuestionsByModuleId($module_id)
    {
        $res = ['error' => 0, 'data' => []];

        try {
            $this->log('Busca Questões pelo id do modulo!', 'fetchQuestionsByModuleId');
            $this->startTransaction('rupus');
            $answered = $this->ModuleModel->verifyModuleAnswered($module_id, $this->dataJWT['user_id']);
            $questions = $this->ModuleModel->fetchQuestionsByModuleId($module_id);

            foreach ($questions as $key => $question) {
                $ans = $this->ModuleModel->fetchAnswerByQuestionId($question['id']);
                $questions[$key]['answer'] = $ans;
                foreach ($answered as $value) {
                    if ($value['question_id'] == $question['id']) {
                        $questions[$key]['status'] = $value['status'];
                        $questions[$key]['answered'] = $value['answer_id'];
                    }
                }
            }

            $this->commitTransaction('rupus');
            $res['data'] = $questions;
            $this->log('Busca de perguntas', 'fetchQuestionsByModuleId');
        } catch (\Exception $e) {
            $this->rollBackTransaction('rupus');
            $this->log($e->getMessage(), 'fetchQuestionsByModuleId', 'error');
            $res['error'] = $e->getMessage();
        }

        $this->sendData($res);
        exit;
    }
}
