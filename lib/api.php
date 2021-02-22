<?php

class SuzuriForWpApi {
	public $apiKey;
	public $userId;
	public $userName;
	public $itemId;
	public $limit = 10;
	public $materialId;
	public $offset;
	const SUZURI_API_URL = 'https://suzuri.jp/api/v1/';

	static function init() {
      return new self();
  }

  function __construct(string $api_key, string $user_name) {
      $this->apiKey = $api_key;
      $this->userName = $user_name;
  }

  public function get_userdata() {
  	$endpoint = self::SUZURI_API_URL . 'user/';

  	$context = array(
			'http' => array(
				'method' => 'GET' ,
				'header' => array(
					'Authorization: Bearer ' . $this->apiKey,
				),
			),
		);

    $str_json = $this->get_proc($request_url, $context);
    return json_decode($str_json,true);
  }

  public function get_choice_products($choice_id, $params) {
    $endpoint = self::SUZURI_API_URL . 'choices/'.$choice_id.'/products';

    $request_url = $endpoint . '?' . http_build_query($params);

    $context = array(
      'http' => array(
        'method' => 'GET' ,
        'header' => array(
          'Authorization: Bearer ' . $this->apiKey,
        ),
      ),
    );

    $str_json = $this->get_proc($request_url, $context);
    return json_decode($str_json,true);
  }

  public function get_products($option) {
  	$endpoint = self::SUZURI_API_URL . 'products/';

  	$params = array(
    	"userName" => $this->userName
    );

    $params = array_merge($params, $option);
    $request_url = $endpoint . '?' . http_build_query($params);

		$context = array(
			'http' => array(
				'method' => 'GET' ,
				'header' => array(
					'Authorization: Bearer ' . $this->apiKey,
				),
			),
		);

    $str_json = $this->get_proc($request_url, $context);
    return json_decode($str_json,true);
  }

  private function get_proc($request_url, $context) {
    $curl = curl_init();
    curl_setopt( $curl, CURLOPT_URL, $request_url);
    curl_setopt( $curl, CURLOPT_HEADER, 1);
    curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $context['http']['method']);
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt( $curl, CURLOPT_HTTPHEADER, $context['http']['header']);
    curl_setopt( $curl, CURLOPT_TIMEOUT, 5);
    $res1 = curl_exec($curl);
    $res2 = curl_getinfo($curl);

    $errno = curl_errno($curl);
	  $error = curl_error($curl);

    curl_close($curl);

    if (CURLE_OK !== $errno) {
	    throw new RuntimeException($error, $errno);
	  }

    $str_json = substr($res1, $res2['header_size']) ;
    return  $str_json;
	}
}