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
	private $messages = array();
	private $logger;
	private $err="";
	private $commited=array();
	private $default = array(

			'category' => array(false,'default'),
	       	'status'   => array(false,0,'int'),
			'level'    => array(false,0,'int'),
			'service'  => array(false,'0'),
			'user_info' => array(false,array()),
			'logmsg'      => array(false,array()),
			'client_ip' => array(false,''),
			'revtime'     => array(false),
			'server_ip' => array(false),
			'machine'  => array(false),
       	
		);
	/**
	 * [__construct 初始化实例]
	 * @param string $host [fluentd服务器 IP 或者 hostname]
	 * @param string $port [fluentd服务端口号]
	 */
	public function __construct($host = '127.0.0.1' , $port = '24224'){
		require_once dirname(__FILE__) . '/Utils.class.php';
		$this->host = $host;
		$this->port = $port;
		Fluent_Autoloader::register();
		$this->logger = new Fluent_Logger_FluentLogger($this->host,$this->port);

		if(php_sapi_name() != "cli")
			$this->default['client_ip']= array(false,Utils::get_client_ip());
		$this->default['server_ip']    = array(false,Utils::get_server_ip());
		$this->default['machine']     = array(false,php_uname('n'));
	}
	/**
	 * [log description]
	 * @param  [type]  $log                    [description]
	 * @param  boolean $is_append_request_info [description]
	 * @return [type]                          [description]
	 */
	public function log($log,$is_append_request_info=false){
		if(($uuid = $this->add($log,$is_append_request_info)) && $this->commit())
			return $uuid;
		else
			return false;
	}

	public function add($log,$is_append_request_info=false){
		if(empty($log['uuid'])) $log['uuid'] = Utils::createUUID();
		$this->default['revtime']            = array(false,time());
		if($is_append_request_info||$log['level']>0 )
			$log['user_request']             = Utils::serializeRequestInfo();
		if(!$this->fieldCheck($log))
			return false;
		$this->messages[] = $log;
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
				if(!$this->logger->post($v['category'],$v)){
					$this->err = 'msg commit failed:'.json_encode($v);
					return false;
				}else{
					$this->commited[] = $v['uuid'];
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