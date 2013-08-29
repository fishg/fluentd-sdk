<?php
require dirname(__FILE__)."/../Scribe.class.php";
class ScribeTest extends PHPUnit_Framework_TestCase {
   public function  test(){
       $scribe = new Scribe('192.168.5.103','1463');
       $log = array(
       	"category"=>"scribe.unittest",
       	"status"=>0,
		"level" =>0,
		"service"=>"scribe.php.sdk",
		"userid"=>123,
		"log"=>array(
			"uname"=>"zhangzhan"
			),
		"ip"    =>"127.0.0.1",
		"time"  =>microtime(true),//optional 
       	);
       $this->assertTrue($scribe->log($log));
   }
}
?>
