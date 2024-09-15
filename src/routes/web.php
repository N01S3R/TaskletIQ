<?php

$webRoutes = [
    // Site
    '/' => [
        'GET' => 'SiteController@index'
    ],
    '/login' => [
        'GET' => 'LoginController@index',
        'POST' => 'LoginController@login'
    ],
    '/register' => [
        'GET' => 'RegisterController@index',
        'POST' => 'RegisterController@register'
    ],
    '/logout' => [
        'GET' => 'SiteController@logout',
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
        'GET' => 'CreatorController@displayAllTasks'
    ],
    '/creator/tasks/(\d+)' => [
        'GET' => 'CreatorController@displayTasksByProgress'
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
    '/operator/project/([\w-]+)' => [
        'GET' => 'OperatorController@project'
    ],
    '/operator/task/([\w-]+)' => [
        'GET' => 'OperatorController@singleTask'
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
