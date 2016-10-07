<?php
class Ip_location extends CI_Model {
	protected $errors = array();
	protected $service = 'api.ipinfodb.com';
	protected $version = 'v3';
	protected $apiKey = '';

	public function __construct(){}

	public function __destruct(){}

	public function setKey($key){
		if(!empty($key)) $this->apiKey = $key;
	}

	public function getError(){
		return implode("\n", $this->errors);
	}

	public function getCountry($host){
		return $this->getResult($host, 'ip-country');
	}

	public function getCity($host){
		return $this->getResult($host, 'ip-city');
	}

	public function curl_get_file_contents($URL)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT_MS, 5000);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
            else return FALSE;
    }

	private function getResult($host, $name){
		$ip = @gethostbyname($host);

		if(preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/', $ip)){
			$URL = 'http://' . $this->service . '/' . $this->version . '/' . $name . '/?key=' . $this->apiKey . '&ip=' . $ip . '&format=xml';
			$xml = $this->curl_get_file_contents($URL);
			
			try { 
				$result = array();
				$xml = simplexml_load_string($xml);
				if(!empty($xml))
				{
					foreach($xml AS $field => $value) {
   						$result[(string)$field] = (string)$value;
					}
					return $result;
				}
			}
			catch(Exception $e){
				$this->errors[] = $e->getMessage();
				return;
			}
		}

		$this->errors[] = '"' . $host . '" is not a valid IP address or hostname.';
		return;
	}
}
?>