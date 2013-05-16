<?php

class Message extends ActiveRecord\Model{

    public function format_time(){
        return date( 'Y-m-d H:i', strtotime($this->time));
    }

    public static function get_all_with_attaches(){
        $sql =  "SELECT m.id as id, m.name as name, m.text as text, m.like as `like`, m.time as `time`,"
                . " a.id as a_id, a.type as a_type, a.text as a_link "
                . " FROM messages m LEFT JOIN attaches a ON(m.id = a.message_id) ORDER BY time DESC, a_id ASC";
        return self::find_by_sql( $sql );
    }
}