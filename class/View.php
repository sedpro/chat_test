<?php 

// Путь до папки с шаблонами
define('VIEWS_BASEDIR', ROOT.'/views/');
 
class View{

    // получить отренедеренный шаблон с параметрами $params
    static function fetchPartial($template, $params = array()){
        extract($params);
        ob_start();
        include VIEWS_BASEDIR.$template.'.html';
        return ob_get_clean();
    }
 
    // отобразить шаблон с параметрами $params    
    static function render($template, $params = array()){
        echo self::fetchPartial($template, $params);
    }

    // вернуть шаблон с параметрами $params    
    static function get($template, $params = array()){
        return self::fetchPartial($template, $params);
    }

    // вернуть array в формате json
    static function json($data){
        echo json_encode($data);
    }

    // вернуть string
    static function string($data){
        echo $data;
    }
}