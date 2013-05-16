<?php

class Controller{

    protected $view;

    function __construct(){
        $this->view = new View();
    }
    
    // обрабатывает полученную от пользователя строку
    function defend($str){
        $str = trim( $str );
        $str = htmlentities($str, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        if(get_magic_quotes_gpc()==1)
        {
            $str=stripslashes($str);
        }
        return $str;
    }
}