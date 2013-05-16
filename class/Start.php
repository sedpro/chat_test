<?php

class Start{

    public static function run(){

        // папка для картинок.
        define('UPLOAD_DIR', ROOT.'/upload/');

        // подключаем необходимые файлы
        require_once(ROOT.'/class/Router.php');
        require_once(ROOT.'/class/Controller.php');
        require_once(ROOT.'/class/View.php');
        require_once(ROOT.'/class/Registry.php');
        require_once(ROOT.'/class/php-activerecord/ActiveRecord.php');

        // Active Record
        // формат строки подключения:
        // $cfg->set_connections(array( 'development' => 'mysql://username:password@localhost/database_name'));
        ActiveRecord\Config::initialize(function($cfg)
        {
            $cfg->set_model_directory('class/models');
            $cfg->set_connections(array(
                'production' => 'mysql://chat:chat@localhost/chat')
            );
            $cfg->set_default_connection('production');
        });

        // получаем путь uri
        $uri = Router::getURI();

        // проверка на наличие у пользователя имени
        $user_name = $_COOKIE['name'];
        if ($user_name){
            $user = User::find_by_name($user_name);
            // хеш - чтобы нельзя было подделать куку с именем
            if( $user and $user->check_hash( $_COOKIE['hash'] ) )
            {
                Registry::set('user', $user);
            };
        }

        // запускаем роутер
        Router::run($uri);

    }
}