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

			'category' => [true],
	       	'status'   => [false,0,'int'],
			'level'    => [false,0,'int'],
			'userid'   => [false,0,'int'],
			'service'  => [true],
			'log'      => [true],
			'ip'       => [true],
			'time'     => [false],
       	
		);

	public function __construct($host = '127.0.0.1' , $port = '1463'){
		require_once $GLOBALS['THRIFT_ROOT'] . '/scribe.php';
		require_once $GLOBALS['THRIFT_ROOT'] . '/transport/TSocket.php';
		require_once $GLOBALS['THRIFT_ROOT'] . '/transport/TFramedTransport.php';
		require_once $GLOBALS['THRIFT_ROOT'] . '/protocol/TBinaryProtocol.php';
		$this->host = $host;
		$this->port = $port;
		$this->default['time'] = [false,$this->currentMicroTime()];
		if(php_sapi_name() != "cli") $this->default['ip']=[false,$this->get_client_ip()];
	}

	public function log($log){
		if($uuid = $this->add($log) && $this->commit() && $this->close())
			return $uuid;
	}

	public function add($log){
		if(!$this->fieldCheck($log)) return false;
		$category = $log['category'];
		if(empty($log['uuid'])) $log['uuid'] = $this->createUUID();
		$log['machine'] = php_uname('n');
		//Todo: 判断 IP 是否需要自动获取
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

	public function createUUID(){
	    return php_uname('n').'-'.$this->currentMicroTime().'-'.rand(10000,99999);
	}
	public function get_client_ip() {
	    if(getenv('HTTP_CLIENT_IP')){
	        $client_ip = getenv('HTTP_CLIENT_IP');
	    } elseif(getenv('HTTP_X_FORWARDED_FOR')) {
	        $client_ip = getenv('HTTP_X_FORWARDED_FOR');
	    } elseif(getenv('REMOTE_ADDR')) {
	        $client_ip = getenv('REMOTE_ADDR');
	    } else {
	        $client_ip = $_SERVER['REMOTE_ADDR'];
	    }
	    return $client_ip;
	}
	public function currentMicroTime()
	{
		$time = explode ( " ", microtime () );
		$time = $time [1] . ($time [0] * 100000000);
		return $time;
	}

}