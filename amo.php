<?php
class Order{
	//объявление переменных
	public $name;
	public $email;
	public $phone;
	public $price;
	
	//валидатор
	public function validate() {
		//запись полученных данных в переменные
		$this->name = isset($_POST['name']) ? $_POST['name'] : null;
		$this->email = isset($_POST['email']) ? $_POST['email'] : null;
		$this->phone = isset($_POST['phone']) ? $_POST['phone'] : null;
		$this->price = isset($_POST['price']) ? $_POST['price'] : null;
		$this->valid = 1;
		// валидация имени
		if (!preg_match("/[а-яёА-ЯЁ]*/", $this->name)) {
			$this->name = $this->name.'[not_valide]';
			$this->valid = 0;
		}
		// валидация email
		if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
			$this->email = $this->email.'[not_valide]';
			$this->valid = 0;
		}
		// валидация телефона
		if (!preg_match("/7[0-9]{10}/", $this->phone)) {
			$this->phone = $this->phone.'[not_valide]';
			$this->valid = 0;
		}
		// валидация прайса
		if (!is_numeric($this->price)) {
			$this->price = $this->price.'[not_valide]';
			$this->valid = 0;
		}
		return $this;
	}
	//отправка валидных данных в amoCRM
	public function send_data_amo($data_form){
			require_once('refresh.php');
			
			$link = 'https://' . $subdomain . '.amocrm.ru/api/v4/leads/complex'; 
			// $link = 'https://' . $subdomain . '.amocrm.ru/api/v4/leads'; 
			$headers = [
				'Authorization: Bearer ' . $access_token
			];
			// $name = isset($data_form->name) ? $data_form->name : 'name';
			// $email = isset($data_form->email) ? $data_form->email : 'email';
			// $phone = isset($data_form->phone) ? $data_form->phone : 'phone';
			// $price = isset($data_form->price) ? $data_form->price : 100;
			$data = [ //complex
				[
					"price" => intval($data_form->price),
					"_embedded" => (object) [
						"contacts" => [
							0 => (object) [
								"first_name" => "$data_form->name",
								"custom_fields_values" => [
									(object) [
										"field_code" => "EMAIL",
										"values" => [
											(object) [
												"enum_code" => "WORK",
												"value" => "$data_form->email"
											]
										]
									],
									(object) [
										"field_code" => "PHONE",
										"values" => [
											(object) [
												"enum_code" => "WORK",
												"value" => "$data_form->phone"
											]
										]
									]
								]
							]
						]
					]
				]
			];
			
			
			
			// echo '<pre>';
			// print_r($data);
			// echo '<pre>';
			// print_r($contacts);
			
			$curl = curl_init(); //Сохраняем дескриптор сеанса cURL
			/** Устанавливаем необходимые опции для сеанса cURL  */
			curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
			curl_setopt($curl,CURLOPT_URL, $link);
			curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
			curl_setopt($curl,CURLOPT_HEADER, false);
			curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
			$out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
			$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);

			echo json_encode($data_form);
		}
}

$application = new Order();
$data_send = $application->validate();

if ($application->validate()->valid) {
	$application->send_data_amo($data_send);
}

