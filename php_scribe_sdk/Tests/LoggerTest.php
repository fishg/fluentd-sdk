<?php
require dirname(__FILE__)."/../src/Logger.class.php";
class ScribeTest extends PHPUnit_Framework_TestCase {
   public function  testLog(){
       $logger = new Logger('10.211.55.6','24224');
       $log = array(
       	"category"=>"fluentd.unittest",
       	"status"=>0,
		"level" =>0,
		"service"=>"fluentd.php.sdk",
		"user_info" => array(
			"id"=>123,
			"name"=>"王大爷2",
			),
		"logmsg"=>array(
			"uname"=>"zhangzhan"
			),
		//"clientIP"    =>"127.0.0.1",
       	);
       for($i=0;$i<200000;$i++){
       $uuid = $logger->log($log);
       if($uuid){
           echo "commit success.uuid is:".$uuid."\n";
	   }else{
	   	   echo "commit failed.msg is:".$logger->getErrorMsg()."\n";
	   }
	}
       $this->assertTrue($uuid!=false);
   }
}
?>