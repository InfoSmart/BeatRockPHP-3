<?
class VirusTotal
{
	public $apiKey;
	public $baseUrl = 'https://www.virustotal.com/vtapi/v2/';

	public $fileSupport = false;

	public function __construct($apiKey)
	{
		$this->apiKey = $apiKey;
	}

	public function api($request, $method = 'GET', $params = [])
	{
		$url 				= $this->baseUrl . $request;
		$params['key'] 	= $this->apiKey;

		if($method == 'GET' AND !empty($params))
			$url .= '?' . http_build_query($params);

		$curl 	= new Curl($url, [
			'params' => [
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0
			]
		]);

		if($this->fileSupport == true)
			$curl->fileSupport = true;

		$result = ($method == 'GET') ? $curl->Get() : $curl->Post($params);

		return json_decode($result, true);
	}
}
?>