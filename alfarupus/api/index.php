<?php

require_once(__DIR__ . '/vendor/autoload.php');

use \PlugRoute\PlugRoute;
use \PlugRoute\RouteContainer;
use \PlugRoute\Http\Request;
use \PlugRoute\Http\RequestCreator;

/**** CORS ****/
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
/**** CORS ****/





$route = new PlugRoute(new RouteContainer(), RequestCreator::create());

$route->notFound(function () {
    header('Location: https://rupuslogistica.com.br/alfarupus');
    exit;
});

$route->get('/', function () {
    header('Location: https://rupuslogistica.com.br');
});






//================================= START AUTORIZAÇÃO
$route->group(['prefix' => '/auth'], function ($route) {
    $route->post('/signout', function (\Controller\AuthenticationController $authenticationController) {
        $authenticationController->signOut();
    });

    $route->post('/signin', function (\Controller\AuthenticationController $authenticationController) {
        $authenticationController->signIn();
    });

    $route->post('/validate', function (\Controller\AuthenticationController $authenticationController) {
        $authenticationController->validate();
    });
});
//================================= END AUTORIZAÇÃO



//================================= START USER
$route->group(['prefix' => '/user'], function ($route) {
    $route->get('/info', function (\Controller\UserController $UserController) {
        $UserController->fetchUserInfo();
    });
});
//================================= END USER


//================================= START MODULE
$route->group(['prefix' => '/module'], function ($route) {
    $route->get('/fetch/{id}', function (\Controller\ModuleController $ModuleController, Request $request) {
        $id = $request->parameter('id');
        $ModuleController->fetchModuleById($id);
    });
    $route->get('/fetch', function (\Controller\ModuleController $ModuleController) {
        $ModuleController->fetchAllModules();
    });
    $route->post('/create', function (\Controller\ModuleController $ModuleController) {
        $ModuleController->createModule();
    });
    $route->post('/validate_answer', function (\Controller\ModuleController $ModuleController) {
        $ModuleController->validateAnswer();
    });
    $route->post('/{id}/question', function (\Controller\ModuleController $ModuleController, Request $request) {
        $id = $request->parameter('id');
        $ModuleController->fetchQuestionsByModuleId($id);
    });
});
//================================= END MODULE





$route->on();