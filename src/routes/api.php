<?php

$apiRoutes = [
    // SITE: Operacje związane z walidacją
    '/api/validate-field' => [
        'POST' => 'ApiRegisterController@validateSignupData' // Walidacja danych rejestracyjnych
    ],
    '/register' => [
        'POST' => 'ApiRegisterController@registerUser'
    ],
    // CREATOR: Zarządzanie tokenami, linkami oraz przypisywaniem użytkowników
    '/api/code/([\w-]+)' => [
        'POST' => 'ApiCreatorController@generateToken' // Generowanie tokenu na podstawie kodu
    ],
    '/api/links' => [
        'GET' => 'ApiCreatorController@getLinks' // Pobieranie linków przypisanych do twórcy
    ],
    '/api/token/delete/(\d+)' => [
        'DELETE' => 'ApiCreatorController@deleteToken' // Usuwanie tokenu na podstawie ID
    ],
    '/api/creator/assign' => [
        'POST' => 'ApiTaskController@assignUserToTask' // Przypisywanie użytkownika do zadania
    ],
    '/api/creator/unassign' => [
        'POST' => 'ApiTaskController@unassignUserFromTask' // Usuwanie przypisania użytkownika z zadania
    ],

    // PROJECT: Operacje CRUD na projektach
    '/api/project/add' => [
        'POST' => 'ApiProjectController@createProject' // Tworzenie nowego projektu
    ],
    '/api/project/update/(\d+)' => [
        'PUT' => 'ApiProjectController@updateProject' // Aktualizacja projektu na podstawie ID
    ],
    '/api/project/delete/(\d+)' => [
        'DELETE' => 'ApiProjectController@deleteProject' // Usuwanie projektu na podstawie ID
    ],

    // TASK: Operacje CRUD na zadaniach
    '/api/task/add' => [
        'POST' => 'ApiTaskController@createTask' // Tworzenie nowego zadania
    ],
    '/api/task/update/(\d+)' => [
        'PUT' => 'ApiTaskController@updateTask' // Aktualizacja zadania na podstawie ID
    ],
    '/api/task/delete/(\d+)' => [
        'DELETE' => 'ApiTaskController@deleteTask' // Usuwanie zadania na podstawie ID
    ],

    // OPERATOR: Zarządzanie statusami i hasłami
    '/api/status' => [
        'POST' => 'ApiTaskController@changeTaskStatus' // Zmiana statusu zadania
    ],
    '/api/change-password' => [
        'POST' => 'ApiOperatorController@changePassword' // Zmiana hasła użytkownika
    ],

    // ADMIN: Zarządzanie użytkownikami
    '/api/users' => [
        'GET' => 'ApiAdminController@reloadUsers' // Pobieranie pełnej, zaktualizowanej listy użytkowników
    ],
    '/api/user/add' => [
        'POST' => 'ApiAdminController@addUser' // Dodawanie nowego użytkownika
    ],
    '/api/user/update/(\d+)' => [
        'PUT' => 'ApiAdminController@updateUser' // Aktualizacja danych użytkownika na podstawie ID
    ],
    '/api/user/delete/(\d+)' => [
        'DELETE' => 'ApiAdminController@deleteUser' // Usuwanie użytkownika na podstawie ID
    ],
];

return $apiRoutes;
