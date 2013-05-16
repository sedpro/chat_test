<?php

class MainController extends Controller {
    
    function actionIndex($url = null){

        $content = $this->view->get('main', 
            array('page' => $url)
            );

        $this->view->render('template', 
            array('content' => $content)
            );
        
    }

}