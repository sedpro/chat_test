<?php 

class Router{

    // Метод получает URI. Несколько вариантов представлены для надёжности.
    static function getURI(){
        if(!empty($_SERVER['REQUEST_URI'])) {
            return trim($_SERVER['REQUEST_URI'], '/');
        }

        if(!empty($_SERVER['PATH_INFO'])) {
            return trim($_SERVER['PATH_INFO'], '/');
        }
 
        if(!empty($_SERVER['QUERY_STRING'])) {
            return trim($_SERVER['QUERY_STRING'], '/');
        }
    }

    static function run($uri){

        // главная
        if ($uri=='') $uri='main/index';

        // Разбиваем внутренний путь на сегменты.
        $segments = explode('/', $uri);
        // Первый сегмент — контроллер.
        $controller = ucfirst(array_shift($segments)).'Controller';
        // Второй — действие.
        $action = 'action'.ucfirst(array_shift($segments));
        // Остальные сегменты — параметры.
        $parameters = $segments;

        // Подключаем файл контроллера, если он имеется,
        // т.к. из-за php-activerecord нельзя использовать __autoload()
        $controllerFile = ROOT.'/class/controllers/'.$controller.'.php';
        if(file_exists($controllerFile)){
            include($controllerFile);
        }

        // Если не загружен нужный класс контроллера или в нём нет
        // нужного метода — 404 
        if(!is_callable(array($controller, $action))){
            header("HTTP/1.0 404 Not Found");
            return;
        };

        // Вызываем действие контроллера с параметрами
        $controller_object = new $controller;
        call_user_func_array(array($controller_object, $action), $parameters);
        return;

    }
}