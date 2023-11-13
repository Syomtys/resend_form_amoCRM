<?php
require_once('config.php');

$dataToken = file_get_contents('tokens.txt');
$dataToken = json_decode($dataToken, true);
// print_r($dataToken);
if ($dataToken["endTokenTime"] - 60 < time()) {
	$link = 'https://' . $subdomain . '.amocrm.ru/oauth2/access_token'; 
	
	$data = [
		'client_id' => $client_id,
		'client_secret' => $client_secret,
		'grant_type' => 'refresh_token',
		'refresh_token' => $dataToken['refresh_token'],
		'redirect_uri' => $redirect_uri,
	];
	
	$curl = curl_init(); 
	
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
	curl_setopt($curl,CURLOPT_URL, $link);
	curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
	curl_setopt($curl,CURLOPT_HEADER, false);
	curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
	$out = curl_exec($curl);
	
	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	
	$code = (int)$code;
	$errors = [
		400 => 'Bad request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not found',
		500 => 'Internal server error',
		502 => 'Bad gateway',
		503 => 'Service unavailable',
	];
	
	try
	{
		if ($code < 200 || $code > 204) {
			throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
		}
	}
	catch(\Exception $e)
	{
		die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
	}
	
	// print_r($out);
	
	$f = fopen('tokens.txt', 'w');
	fwrite($f, $out);
	fclose($f);
	$js_decode = json_decode($out, true);
	
	$access_token = $js_decode['access_token'];
	
} else {
	$access_token = $dataToken['access_token'];
}

