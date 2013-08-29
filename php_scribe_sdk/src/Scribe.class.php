<?php
if(!isset($GLOBALS['THRIFT_ROOT'])||empty($GLOBALS['THRIFT_ROOT'])) $GLOBALS['THRIFT_ROOT'] = dirname(__FILE__).'/includes';
class Scribe{
	private $host = '127.0.0.1';
	private $port = '1463';
	private $persist = true;
	private $messages = array();
	private $transport;
	private $scribe_client;
	private $isopen = false;
	private $default = array(

			'category' => [false,'default'],
	       	'status'   => [false,0,'int'],
			'level'    => [false,0,'int'],
			'service'  => [false,'0'],
			'userInfo' => [false,array()],
			'log'      => [false,array()],
			'clientIP' => [false,''],
			'time'     => [false],
			'serverIP' => [false],
			'machine'  => [false],
       	
		);

	public function __construct($host = '127.0.0.1' , $port = '1463'){
		require_once $GLOBALS['THRIFT_ROOT'] . '/scribe.php';
		require_once $GLOBALS['THRIFT_ROOT'] . '/transport/TSocket.php';
		require_once $GLOBALS['THRIFT_ROOT'] . '/transport/TFramedTransport.php';
		require_once $GLOBALS['THRIFT_ROOT'] . '/protocol/TBinaryProtocol.php';
		require_once dirname(__FILE__) . '/Utils.class.php';
		$this->host = $host;
		$this->port = $port;
		$this->default['time'] = [false,Utils::currentMicroTime(true)];
		if(php_sapi_name() != "cli") $this->default['clientIP']=[false,Utils::get_client_ip()];
		$this->default['serverIP'] = [false,Utils::get_server_ip()];
		$this->default['machine'] = [false,php_uname('n')];
	}

	public function log($log,$is_append_request_info=false){
		if($uuid = $this->add($log,$is_append_request_info) && $this->commit() && $this->close())
			return $uuid;
	}

	public function add($log,$is_append_request_info=false){
		if(!$this->fieldCheck($log)) return false;
		$category = $log['category'];
		if(empty($log['uuid'])) $log['uuid'] = Utils::createUUID();
		if($is_append_request_info||$log['level']>0 ) $log['userRequest'] = Utils::serializeRequestInfo();
		$messages = array(
			'category'  => $category,
			'messages'  => json_encode($log),
			);
		$this->messages[] = new LogEntry($log);
		return $log['uuid'];
	}

	public function commit(){
		if(empty($messages)) return false;
		if(!$this->isopen) $this->connect();
		$this->scribe_client->Log($this->messages);
		return true;
	}

	public function close(){
		$this->transport->close();
		return true;
	}
	
	public function connect(){
		try {
			$socket = new TSocket($this->host, $this->port, $this->persist);
			$this->transport = new TFramedTransport($socket);
			$protocol = new TBinaryProtocol($this->transport, false, false);
			$this->scribe_client = new scribeClient($protocol, $protocol);
			$this->transport->open();
			$this->isopen = true;
			return true;
		} catch ( TException $e ) {
			return false;
		}
	}

	public function fieldCheck(&$log){
		foreach($this->default as $k =>$v){
			if($v[0]&&(!isset($log[$k])||empty($log[$k])))  return false;
			if(!$v[0]&&(!isset($log[$k])||empty($log[$k]))) $log[$k] = $v[1];
			if(isset($v[2])&&$v[2]=='int')  $log[$k] = intval($log[$k]);
		}
		return true;
	}

}