<?php
namespace Jifno;

require_once 'Jifno/Exception.php';

class Client
{
    public static $key;
    
    public static $URL = 'http://api.jifno.com/v1';
    
    protected $_curl;
    
    protected $_debug;
    
    protected $_key;
    
    public function __construct($key = null)
    {
        $this->_curl = curl_init();
        $this->_key;
    }
    
    protected function _getKey()
    {
        return ($this->_key) ? $this->_key : self::$key;
    }
    
    public function call($resource, $method, $params, $debug = false)
    {
        $this->_debug = $debug;
        $payload = '{"key": "' . $this->_getKey() . '", "message": ' . $params . '}';
        
        curl_setopt($this->_curl, CURLOPT_URL, self::$URL);
        curl_setopt($this->_curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($this->_curl, CURLOPT_VERBOSE, $debug);
        curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
        
        switch ($method) {
            case 'create':
                curl_setopt($this->_curl, CURLOPT_POST, true);
                break;
            default:
                throw new \Jifno\Exception('Invalid method: ' . $method);
                break;
        }
        
        $start = microtime(true);
        $this->_log('Call to ' . self::$URL . ': ' . $params);
        if ($debug) {
            $curl_buffer = fopen('php://memory', 'w+');
            curl_setopt($this->_curl, CURLOPT_STDERR, $curl_buffer);
        }
        
        $response_body = curl_exec($this->_curl);
        $info = curl_getinfo($this->_curl);
        $time = microtime(true) - $start;
        if ($debug) {
            rewind($curl_buffer);
            $this->_log(stream_get_contents($curl_buffer));
            fclose($curl_buffer);
        }
        
        $this->_log('Completed in ' . number_format($time * 1000, 2) . 'ms');
        $this->_log('Got response: ' . $response_body);
        
        if(curl_error($this->_curl)) {
            throw new \Jifno\Exception("API call to " . self::$URL . " failed: " . curl_error($this->_curl));
        }
        $result = json_decode($response_body, true);
        if ($result === null) {
            throw new \Jifno\Exception('We were unable to decode the JSON response from the Jifno API: ' . $response_body);
        }
        if(floor($info['http_code'] / 100) >= 4) {
            throw new \Jifno\Exception("{$info['http_code']}, " . $result['error']);
        }
        return $result;
    }
    
    protected function _log($msg) {
        if ($this->_debug) {
            error_log($msg);
        }
    }
}