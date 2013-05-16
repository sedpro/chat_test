<?php

class MessageController extends Controller {
    
    // добавление сообщения
    function actionAdd(){
        $text = $_POST['text'];
        $text = $this->defend($text);
        $name = Registry::get('user')->name;

        // проверяем, не пустое ли это сообщение
        $empty = true;
        if ( strlen($text)>0 ) $empty = false;
        $links = array();
        foreach ($_POST['link'] as $link) {
            $link = $this->checkurl($link);
            if( $link != '' ){
                $links[] = $link;
                $empty = false;
            }

        }

        // загрузка фоток
        $files = array();
        if(isset($_FILES['photo']['tmp_name']))
        {
            for($i=0; $i < count($_FILES['photo']['tmp_name']);$i++)
            {
                $tmp_name = $_FILES['photo']['tmp_name'][$i];
                $new_name = $_FILES['photo']['name'][$i];
                if( is_uploaded_file( $tmp_name ) )
                {
                    $upload_dir = UPLOAD_DIR.$name;
                    if( ! is_dir($upload_dir) ){
                        mkdir($upload_dir, 0777);
                        chmod($upload_dir, 0777);
                    };
                    if(@copy($tmp_name, $upload_dir.'/'.$new_name))
                    {
                        $empty = false;
                        $files[] = $name.'/'.$new_name;
                    }
                }
            }
        }

        if( ! $empty ){

            $message = Message::create(array('name' => $name, 'text' => $text));

            foreach ($links as $link) {
                if ( ! preg_match('~^http://www.youtube.com[\S]+~', $link) ){
                    // простая ссылка на сайт
                    Attach::create(array('message_id' => $message->id, 'type' => 'link', 'text' => $link ));
                }else{
                    // ссылка на ролик youtube
                    // достаем ид ролика
                    // предполагаем, что ссылка будет такого вида:
                    // http://www.youtube.com/watch?v=K7Z4G4B8MJQ&feature=related
                    preg_match('~v=([^\W]+)~', $link, $matches);
                    $video_id = $matches[1];
                    Attach::create(array('message_id' => $message->id, 'type' => 'video', 'text' => $video_id ));
                }
            }

            foreach($files as $file){
                Attach::create(array('message_id' => $message->id, 'type' => 'photo', 'text' => $file ));
            }

        };

        header ("Location: /");
    }

    // получить сообщения
    function actionGet(){
        $this->Render();
    }

    // формирует json из сообщений и их аттачментов
    function Render(){ 
        $messages = Message::get_all_with_attaches();
        $output = array();
        foreach ($messages as $message){
            $item = array(
                'id'   => $message->id,
                'text' => $message->text,
                'name' => $message->name,
                'like' => $message->like,
                'time' => $message->format_time(),
                );
            if ($message->a_id){
                $item['attaches'] = array(array( 
                    'a_id' => $message->a_id,
                    'a_type' => $message->a_type,
                    'a_link' => $message->a_link
                    ));
            }
            // склеиваем аттачменты с соответствующими сообщениями. 
            if( $item['id']==$output[count($output)-1]['id']){
                $output[count($output)-1]['attaches'][] = $item['attaches'][0];
            }else{  
                $output[] = $item;
            }
        }

        $this->view->json($output);
    }

    // удалить сообщение
    function actionDelete(){
        $id = (int)$_POST['id'];
        $name = Registry::get('user')->name;
        $message = Message::find($id);
        if ( $message and $message->name==$name ){
            // удаляем и аттачменты тоже
            $attaches = Attach::all(array('conditions' => array('message_id = ?',$message->id)));
            if ($attaches) 
            {   
                foreach ($attaches as $item){
                    $attach = Attach::find($item->id);
                    // удаляем файлы с фотками
                    if($attach->type=='photo'){
                        @unlink(UPLOAD_DIR.$attach->text);
                    };
                    $attach->delete();
                }
            }
            $message->delete();
        }
        $this->Render();
    }

    // увеличивает лайк
    function actionLike(){
        $id = (int)$_POST['id'];
        $message = Message::find($id);
        $message->like += 1;
        $message->save(); 
        
        $this->view->string($message->like);
    }

    // функция для удаления опасных символов
    function pregtrim($str) {
        return preg_replace("/[^\x20-\xFF]/","",@strval($str));
    }

    function checkurl($url) {

        // режем левые символы и крайние пробелы
        $url=trim( $this->pregtrim( $url ) );

        // если пусто - выход
        if (strlen($url)==0) return '';

        //проверяем УРЛ на правильность
        if (!preg_match("~^(?:(?:https?|ftp|telnet)://(?:[a-z0-9_-]{1,32}".
        "(?::[a-z0-9_-]{1,32})?@)?)?(?:(?:[a-z0-9-]{1,128}\.)+(?:com|net|".
        "org|mil|edu|arpa|gov|biz|info|aero|inc|name|[a-z]{2})|(?!0)(?:(?".
        "!0[^.]|255)[0-9]{1,3}\.){3}(?!0|255)[0-9]{1,3})(?:/[a-z0-9.,_@%&".
        "?+=\~/-]*)?(?:#[^ '\"&<>]*)?$~i",$url,$ok))
        return ''; // если не правильно - выход

        // если нет протокала - добавить
        if (!strstr($url,"://")) $url="http://".$url;

        // заменить протокол на нижний регистр: hTtP -> http
        $url=preg_replace("~^[a-z]+~ie","strtolower('\\0')",$url);

        return $url;
    }

}