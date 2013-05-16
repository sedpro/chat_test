<?php 

// определяем корневую папку
define('ROOT', dirname(__FILE__));

// подключаем и запускаем стартер
require_once(ROOT.'/class/Start.php');
Start::run();

