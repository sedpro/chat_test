<?php

class User extends ActiveRecord\Model{

    public function get_hash(){
        return md5( $this->pwd . "f#@V)Huzd~&fa%Hgfds" );
    }

    public function check_hash($hash){
        return ( $hash == $this->get_hash() ); 
    }

}