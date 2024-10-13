<?php

namespace App;

use App\View;
use App\Controller\ControllerFactory;
use Doctrine\ORM\EntityManager;
use App\Config\DoctrineConfig;
use Exception;

class Routes
{
    public static function init()
    {
        try {
            $path = $_SERVER['REQUEST_URI'];
            $path = strtok($path, '?');

            $routes = (strpos($path, '/api') === 0)
                ? include 'routes/api.php'
                : include 'routes/web.php';

            $matched = false;

            // Tworzymy EntityManager
            $entityManager = DoctrineConfig::createEntityManager();

            foreach ($routes as $route => $methods) {
                if (preg_match("~^$route$~", $path, $matches)) {
                    $requestMethod = $_SERVER['REQUEST_METHOD'];

                    if (isset($routes[$route][$requestMethod])) {
                        $parts = explode('@', $routes[$route][$requestMethod]);
                        $controllerName = $parts[0];
                        $methodName = $parts[1];

                        // Tworzymy kontroler z EntityManager
                        $controller = ControllerFactory::create($controllerName, $entityManager);

                        if (method_exists($controller, $methodName)) {
                            $params = array_slice($matches, 1);
                            $params = self::processParameters($params);

                            // Obsługa danych JSON dla POST
                            if ($requestMethod === 'POST') {
                                $input = json_decode(file_get_contents('php://input'), true);
                                if (is_array($input)) {
                                    $params[] = $input;
                                } else {
                                    $params[] = $_POST;
                                }
                            }

                            // Obsługa GET
                            if ($requestMethod === 'GET') {
                                $params[] = $_GET;
                            }

                            // Obsługa PUT i DELETE
                            if (in_array($requestMethod, ['PUT', 'DELETE'])) {
                                $input = file_get_contents('php://input');
                                parse_str($input, $putDeleteParams);
                                $params[] = $putDeleteParams;
                            }

                            call_user_func_array([$controller, $methodName], $params);
                            $matched = true;
                            break;
                        }
                    }
                }
            }

            if (!$matched) {
                self::render404();
            }
        } catch (Exception $e) {
            // Log the exception
            error_log($e->getMessage());
            var_dump($e->getMessage());
            self::render500();
        }
    }

    private static function processParameters(array $params)
    {
        foreach ($params as &$param) {
            if (is_numeric($param) && strpos($param, '.') === false) {
                $param = (int) $param;
            }
        }
        return $params;
    }

    private static function render404()
    {
        View::render('404_page');
    }

    private static function render500()
    {
        View::render('500_page');
    }
}

Routes::init();
