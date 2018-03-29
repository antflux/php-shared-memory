<?php
require("./memory_cache.php");
$data=array("user"=>array("user_name"=>'mike',"user_pass"=>'test'),array("user_name"=>'yahu',"user_pass"=>'yahuu'));
    //save_cache($data,"db_user");
$cache=new memory_cache();
var_dump($cache->get('db_user')) ;