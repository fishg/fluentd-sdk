<?php
/*
 * 猪八戒开放平台-Logger系统 php-SDK
 * 对基于Scribe的日志发送进行简化封装
 * version: 0.1
 * author: 肥西
 */

require_once dirname(__FILE__).'/Fluentd/Autoloader.php';

class Logger{
	private $host = '127.0.0.1';
	private $port = '24224';
	private $tag  = 'default.php';
	private $messages = array();
	private $logger;
	private $err="";
	private $commited=array();

	public  $is_debug = false;

	private $default = array(

			//'tag' => array(false,'default'),
	       	'status'   => array(false,0,'int'),
			'level'    => array(false,0,'int'),
			'service'  => array(false,'0'),
			'user_info' => array(false,array()),
			'logmsg'      => array(false,array()),
			'client_ip' => array(false,''),
			'logtime'     => array(false),
			'server_ip' => array(false),
			'machine'  => array(false),
       	
		);
	/**
	 * [__construct 初始化实例]
	 * @param string $host [fluentd服务器 IP 或者 hostname]
	 * @param string $port [fluentd服务端口号]
	 */
	public function __construct($tag,$host = '127.0.0.1' , $port = '24224'){
		require_once dirname(__FILE__) . '/Utils.class.php';
		$this->host = $host;
		$this->port = $port;
		$this->tag  = $tag;
		Fluent_Autoloader::register();
		$this->logger = new Fluent_Logger_FluentLogger($this->host,$this->port);

		if(php_sapi_name() != "cli")
			$this->default['client_ip']= array(false,Utils::get_client_ip());
		$this->default['server_ip']    = array(false,Utils::get_server_ip());
		$this->default['machine']     = array(false,php_uname('n'));
	}
	/**
	 * log
	 * 单条日志记录发送
	 * @param  String  $child_tag              子分类(模块标识),比如: user.login
	 * @param  String  $log                    关联数组,包含日志具体内容,格式:
	 * array(
	 * "status"=>0,                   //整数,操作状态:0表示没有异常,其他值表示异常
	 * "level" =>0,                   //整数,对应各种日志级别,
	 *                                //基本日志级别:0 info级别,10 warning级别,20 error级别
	 * "service"=>"test",             //服务标识,比如登陆功能:login
	 * "user_info" => array(          //用户信息,自定义字段
	 * 	"id"=>123,
	 * 	"name"=>"王大爷2php", 
	 * ),
	 * "logmsg"=>array(                //日志主体内容,字段自定义
	 * 	"uname"=>"zhangzhan"
	 * ),
	 * );
	 * @param  boolean $is_append_request_info 是否同时发送用户请求信息,包含get/post/cookie/server
	 * @return String or false                 成功,返回日志唯一id;失败,返回false
	 */
	public function log($child_tag, $log, $is_append_request_info=false){
		if(($uuid = $this->add($child_tag, $log, $is_append_request_info)) && $this->commit()){
			if($this->is_debug) 'commit success,uuid:'.$uuid;
			return $uuid;
		}else{
			if($this->is_debug) 'commit failed,msg:'.$this->err;
			return false;
		}
	}

	public function add($child_tag, $log,$is_append_request_info=false){
		if(empty($log['uuid'])) $log['uuid'] = Utils::createUUID();
		$this->default['logtime']            = array(false,time());
		if($is_append_request_info||$log['level']>0 )
			$log['user_request']             = Utils::serializeRequestInfo();
		if(!$this->fieldCheck($log))
			return false;
		$this->messages[] = array($child_tag, $log);
		$this->_resetErrorMsg();
		return $log['uuid'];
	}

	public function commit(){
		if(empty($this->messages)){
			$this->err = 'message is empty.';
			return false;
		}
		try {
			foreach($this->messages as $v)
				if(!$this->logger->post($this->tag.'.'.$v[0],$v[1])){
					$this->err = 'msg commit failed:'.json_encode($v);
					return false;
				}else{
					$this->commited[] = $v[1]['uuid'];
				}
			$this->_resetErrorMsg();
		}catch (Exception $e) {
            $this->err = 'unknown error occured when msg commiting:'.json_encode($this->messages).
            			 "\n success commited:".json_encode($this->commited);
            return false;
        }
        $this->messages = array();
		return true;
	}

	public function close(){
		$this->logger->close();
		return true;
	}

	public function fieldCheck(&$log){
		foreach($this->default as $k =>$v){
			if($v[0]&&(!isset($log[$k])||empty($log[$k]))){
				$this->err = 'filed:"'.$k.'" can not be empty. msg:'.json_encode($log);
				return false;
			}
			if(!$v[0]&&(!isset($log[$k])||empty($log[$k]))) $log[$k] = $v[1];
			if(isset($v[2])&&$v[2]=='int')  $log[$k] = intval($log[$k]);
		}
		$this->_resetErrorMsg();
		return true;
	}

	public function getErrorMsg(){
		return $this->err;
	}

	public function __destruct(){
		unset($this->logger);
	}

	private function _resetErrorMsg(){
		$this->err = "";
	}

}