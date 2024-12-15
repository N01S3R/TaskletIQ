<?php

$webRoutes = [
    // Site
    '/' => [
        'GET' => 'SiteController@displaySite'
    ],
    '/login' => [
        'GET' => 'LoginController@displayLogin',
        'POST' => 'LoginController@loginUser'
    ],
    '/register' => [
        'GET' => 'RegisterController@displayRegister',
    ],
    '/register/([\w-]+)' => [
        'GET' => 'RegisterController@displayRegister',
    ],
    '/logout' => [
        'GET' => 'LoginController@displayLogout',
    ],

    // Creator
    '/creator/dashboard' => [
        'GET' => 'CreatorController@displayDashboard'
    ],
    '/creator/project/(\d+)' => [
        'GET' => 'ProjectController@displayProject'
    ],
    '/creator/projects' => [
        'GET' => 'ProjectController@displayAllProjects'
    ],
    '/creator/tasks' => [
        'GET' => 'TaskController@displayAllTasks'
    ],
    '/creator/tasks/(\d+)' => [
        'GET' => 'TaskController@displayTasksByProgress'
    ],
    '/creator/delegate' => [
        'GET' => 'CreatorController@displayDelegateForm',
    ],
    '/creator/code' => [
        'GET' => 'CreatorController@displayRegistrationCode',
    ],

    // Operator
    '/operator/dashboard' => [
        'GET' => 'OperatorController@displayDashboard'
    ],
    '/operator/project/(\d+)' => [
        'GET' => 'OperatorController@operatorProject'
    ],
    '/operator/task/([\w-]+)' => [
        'GET' => 'OperatorController@singleTask'
    ],
    '/operator/settings' => [
        'GET' => 'OperatorController@operatorSettings'
    ],
    // Admin
    '/admin/dashboard' => [
        'GET' => 'AdminController@displayDashboard'
    ],
    '/admin/users' => [
        'GET' => 'AdminController@manageUsers'
    ],
    '/admin/settings' => [
        'GET' => 'AdminController@siteSettings'
    ],
];

return $webRoutes;
