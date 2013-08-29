<?php
require dirname(__FILE__)."/../src/Scribe.class.php";
class ScribeTest extends PHPUnit_Framework_TestCase {
   public function  test(){
       $scribe = new Scribe('192.168.5.103','1463');
       $log = array(
       	"category"=>"scribe.unittest",
       	"status"=>0,
		"level" =>0,
		"service"=>"scribe.php.sdk",
		"userInfo" => array(
			"id"=>123,
			"name"=>"王大爷",
			),
		"log"=>array(
			"uname"=>"zhangzhan"
			),
		//"clientIP"    =>"127.0.0.1",
       	);
       $uuid = $scribe->log($log);
       echo "uuid is:".$uuid."\n";
       $this->assertTrue($uuid);
   }
}
?>
