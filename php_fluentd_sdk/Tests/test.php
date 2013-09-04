<?php
//可能需要你自己修正包含路径
require dirname(__FILE__)."/../src/Logger.class.php";
/**
 * 配置
 * 建议定义到项目统一config文件
 */
$host       = '127.0.0.1';
$port       = '24224';
$tag        = 'fluentd';

/**
 * 模块标识配置,建议每个类单独配置
 */
$tag_module = 'unittest.php';

$logger     = new Logger($tag,$host,$port);

/**
 * 日志详情
 */
$log        = array(
			"status"=>0,                   //整数,操作状态:0表示没有异常,其他值表示异常
			"level" =>0,                   //整数,对应各种日志级别,
			                               //基本日志级别:0 info级别,10 warning级别,20 error级别
			"service"=>"test",             //服务标识,比如登陆功能:login
			"user_info" => array(          //用户信息,自定义字段
				"id"=>123,
				"name"=>"王大爷2php", 
			),
			"logmsg"=>array(                //日志主体内容,字段自定义
				"uname"=>"zhangzhan"
			),
);
	
$uuid = $logger->log($tag_module, $log);
if($uuid){// 成功,返回成功记录的日志唯一 id
	echo "commit success.uuid is:".$uuid."\n";
}else{// 失败,返回 false
	echo "commit failed.msg is:".$logger->getErrorMsg()."\n";
} 