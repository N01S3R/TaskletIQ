<?php

$apiRoutes = [
    // Site
    '/api/validate-field' => [
        'POST' => 'RegisterController@validateSignupData'
    ],
    // Creator
    '/api/project/add' => [
        'POST' => 'ProjectController@createProject'
    ],
    '/api/project/update/(\d+)' => [
        'PUT' => 'ProjectController@updateProject'
    ],
    '/api/project/delete/(\d+)' => [
        'DELETE' => 'ProjectController@deleteProject'
    ],
    '/api/task/add' => [
        'POST' => 'TaskController@createTask'
    ],
    '/api/task/update/(\d+)' => [
        'PUT' => 'TaskController@updateTask'
    ],
    '/api/task/delete/(\d+)' => [
        'DELETE' => 'TaskController@deleteTask'
    ],
    '/api/code/([\w-]+)' => [
        'POST' => 'CreatorController@generateToken'
    ],
    '/api/links' => [
        'GET' => 'CreatorController@getLinks'
    ],
    '/api/token/delete/(\d+)' => [
        'DELETE' => 'CreatorController@deleteToken'
    ],
    '/api/creator/assign' => [
        'POST' => 'TaskController@assignUserToTask'
    ],
    '/api/creator/unassign' => [
        'POST' => 'TaskController@unassignUserFromTask'
    ],
    // Operator
    '/api/status' => [
        'POST' => 'OperatorController@changeTaskStatus'
    ],
    // Admin
    '/api/users' => [
        'POST' => 'AdminController@allUsers',
        'GET' => 'AdminController@reloadUsers'
    ],
    '/api/user/add' => [
        'POST' => 'AdminController@addUser'
    ],
    '/api/user/update/(\d+)' => [
        'PUT' => 'AdminController@updateUser'
    ],
    '/api/user/delete/(\d+)' => [
        'DELETE' => 'AdminController@deleteUser'
    ],
];

return $apiRoutes;
