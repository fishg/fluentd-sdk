<?php
class Utils{

    public static function createUUID(){
        return php_uname('n').'-'.Utils::currentMicroTime().'-'.rand(10000,99999);
    }
    public static function get_client_ip() {
        if(getenv('HTTP_CLIENT_IP')){
            $client_ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR')) {
            $client_ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR')) {
            $client_ip = getenv('REMOTE_ADDR');
        } else {
            $client_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:"";
        }
        return $client_ip;
    }
    public static function get_server_ip(){
        if (isset($_SERVER)) {
            if(isset($_SERVER['SERVER_ADDR'])&&$_SERVER['SERVER_ADDR']) {
                $server_ip = $_SERVER['SERVER_ADDR'];
            } else {
                $server_ip = isset($_SERVER['LOCAL_ADDR'])?$_SERVER['LOCAL_ADDR']:'';
            }
        } else {
            $server_ip = getenv('SERVER_ADDR');
        }
        return empty($server_ip)?"":$server_ip;
    }
    public static function currentMicroTime($is_ms=false)
    {
        return date("YmdHis") . explode('.',explode(' ',(string)microtime())[0])[1];
    }
    public static function serializeRequestInfo(){
        return array(
                "get"    => $_GET,
                "post"   => $_POST,
                "cookie" => $_COOKIE,
                "server" =>$_SERVER
                );
    }
}