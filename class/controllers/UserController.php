<?php

class UserController extends Controller {
    
    // выдает страницу с запросом имени пользователя
    function actionName(){
        $this->view->render('name');
    }

    // добавляет пользователя в бд и пишет куку
    function actionAdd(){
        $name = $_POST['name'];
        if ( strlen($name)<1 ) {
            $this->view->json(array('status' => 'zero'));
            exit();
        };

        // проверяем, есть ли такой юзер
        $user = User::find_by_name($name);
        if ( ! $user ){ 
            $pwd = self::generateCode();
            $user = User::create(array('name' => $name, 'pwd' => $pwd));
            setcookie('name', $name, time()+3600*24*365, '/');

            $hash = $user->get_hash();
            setcookie('hash', $hash, time()+3600*24*365, '/');
            $this->view->json(array('status' => 'created', "name" => $name));
        }else{
            // такой юзер уже есть
            $this->view->json(array('status' => 'duplicate'));           
        }
    }

    // Функция для генерации случайной строки
    static function generateCode($length=10) {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
        $code = "";
        $clen = strlen($chars) - 1;  
        while (strlen($code) < $length) {
                $code .= $chars[mt_rand(0,$clen)];  
        }
        return $code;
    }
}