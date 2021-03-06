<?php

	ignore_user_abort(true); //continua l'esecuzione anche dopo la chiusura della connessione

	$content = file_get_contents("php://input"); //ottengo e decodifico la stringa JSON proveniente da Telegram
	$update = json_decode($content, true);

	if(!$update)
	{
	  exit;
	}

	define("TOKEN","botToken"); //creo una variabile statica contenente il TOKEN

	$conn = dbConnect(); //mi connetto al database

	//salvo i dati che mi interessano
	$chatId = $update["message"]["chat"]["id"];
	$name = $update["message"]["from"]["first_name"];
	$lastname = $update["message"]["from"]["last_name"];
	$username = $update["message"]["from"]["username"];
	$text = $update["message"]["text"];

	//tolgo gli spazi all'inizio e alla fine del messaggio e trasformo tutti i caratteri in minuscolo
	$text = trim($text);
	$text = strtolower($text);

	//COMANDI
	if(strpos($text, "/start") !== false || strpos($text, "ciao") !== false){
		$array = array("chat_id" => $chatId, "text" => "Ciao $name! Per iniziare a chattare con me usa la tastiera personalizzata qui sotto!\r\nAttualmente sono in grado di fornirti l'orario mensile delle palestre e la loro dislocazione nell'istituto.", "reply_markup" => array("keyboard" => array(array(array("text" =>"Lista delle palestre")),array(array("text" =>"Orario mensile delle palestre")),array(array("text" =>"Orario classi, professori o laboratori")),array(array("text" =>"Planimetria")),array(array("text" =>"Disposizione classi"))),"resize_keyboard" => true));
		$jsonArray = json_encode($array);
		$ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/sendMessage');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonArray);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_exec($ch);
		curl_close($ch);
	}

	else if (strpos($text, "lista delle palestre") !== false){
		$array = array("chat_id" => $chatId, "text" => "Lista delle palestre:\r\n-Palestra 1: Laboratori di informatica (grande)\r\n-Palestra 2: Laboratori di informatica (piccola)\r\n-Palestra 3: Sotto laboratori di fisica (grande)\r\n-Palestra 4: Sotto laboratori di fisica (piccola)\r\n-Palestra 5: Satellite (grande)\r\n-Palestra 6: Satellite (piccola)");
		$jsonArray = json_encode($array);
		$ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/sendMessage');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonArray);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_exec($ch);
		curl_close($ch);
	}

	else if (strpos($text, "orario mensile delle palestre") !== false){
		$array = array("chat_id" => $chatId, "photo" => "AgADBAADZb45GykXZAfFLwpkQKmwMcpd-hkABPzOLVTI56smbysBAAEC", "caption" => "Orario dalla settimana 18/09 - 23/09");
		$jsonArray = json_encode($array);
		$ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/sendPhoto');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonArray);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_exec($ch);
		curl_close($ch);
	}

	else if (strpos($text, "orario") !== false){
		$class = substr($text, 6);
		$class = trim($class);

		@$url = "http://www.iiscastelli.gov.it/orariotd/";
		$html = file_get_contents($url);
		$dom = new DOMDocument;
		@$dom->loadHTML($html);
	 	$links = $dom->getElementsByTagName('a');

		foreach ($links as $link) {
			$value = $link->nodeValue;
			$value = trim($value);
			$value = strtolower($value);
			if ($class == $value) {
				$pageUrl = $link->getAttribute('href');
				break;
			}
		}

		if ($pageUrl == false)
			$response = "*Nessuna classe, professore o laboratorio trovato!*\r\nIl formato di invio è: per le classi `Orario 4BI`, per i professori `Orario Cognome Nome` e per i laboratori `Orario nomeLaboratorio numeroLaboratorio`.";

		else {
			$class = strtoupper($class);
			$response = "[Orario $class](http://www.iiscastelli.gov.it/orariotd/$pageUrl)";
		}

		$array = array("chat_id" => $chatId, "text" => $response, "parse_mode" => "Markdown");
		$jsonArray = json_encode($array);
		$ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/sendMessage');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonArray);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_exec($ch);
		curl_close($ch);
	}

	else if (strpos($text, "planimetria") !== false){
		$planimetria = substr($text, 11);
		$planimetria = trim($planimetria);

		if($planimetria == "seminterrato")
			$array = array("chat_id" => $chatId, "document" => "BQADBAADobEAAjAXZAdcNWjH3-YX6gI", "caption" => "Planimetria piano seminterrato");

		else if($planimetria == "rialzato")
			$array = array("chat_id" => $chatId, "document" => "BQADBAADerEAArUeZAdmjAMJsII-iQI", "caption" => "Planimetria piano rialzato", "text");

		else if($planimetria == "primo")
			$array = array("chat_id" => $chatId, "document" => "BQADBAAD4rIAAgUdZAfdqRqWCYC4oQI", "caption" => "Planimetria piano primo");

		else if($planimetria == "secondo")
			$array = array("chat_id" => $chatId, "document" => "BQADBAADjrIAApIaZAcd9-Gg-mh91QI", "caption" => "Planimetria piano secondo");

		else{
			$array = array("chat_id" => $chatId, "text" => "*Nessun piano trovato!*\r\nIl formato di invio è `Planimetria piano` dove `piano` deve corrispondere a `seminterrato`, `rialzato`, `primo` e `secondo`.", "parse_mode" => "Markdown");
			$jsonArray = json_encode($array);
			$ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/sendMessage');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonArray);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_exec($ch);
			curl_close($ch);
		}
		$jsonArray = json_encode($array);
		$ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/sendDocument');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonArray);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_exec($ch);
		curl_close($ch);
	}

	else if (strpos($text, "disposizione classi") !== false){
		$array = array("chat_id" => $chatId, "document" => "BQADBAADcrIAAq0ZZAdSChClLD-jgAI", "caption" => "Disposizione classi 2017/2018");
		$jsonArray = json_encode($array);
		$ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/sendDocument');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonArray);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_exec($ch);
		curl_close($ch);
	}

	else {
		$array = array("chat_id" => $chatId, "text" => "Il messaggio inviato non corrisponde a nessun comando 😢 Per piacere, usa la tastiera personalizzata qui sotto!");
		$jsonArray = json_encode($array);
		$ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/sendMessage');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonArray);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_exec($ch);
		curl_close($ch);
	}

	//controllo se l'utente è nel database, nel caso non sia così lo aggiungo alla tabella
	if(!getData ($chatId, $conn))
		setData ($chatId, $name, $lastname, $username, $conn);

	mysqli_close($conn); //chiudo la connessione al database

	//FUNZIONI
	function dbConnect () {
		$servername = host_database;
	    $username = username_database;
	    $password = password_database;
	    $dbname = name_database;

	    $conn = mysqli_connect($servername, $username, $password, $dbname);

	    if (!$conn) {
	        die("Connessione fallita: " . mysqli_connect_error());
	    }

	    echo "<b>Connesso con successo</b></br>";

	    return $conn;
	}

	function setData ($chatId, $name, $lastname, $username, $conn) {
		$sql = "INSERT INTO `user`(`chatId`, `firstName`, `lastName`, `username`) VALUES ('$chatId', '$name', '$lastname', '$username')";

		if (mysqli_query($conn, $sql)) {
    		echo "<b>Voce registrata con successo!</b></br>";
		}

		else {
    		echo "Errore: " . $sql . "</br>" . mysqli_error($conn);
		}
	}

	function getData ($chatId, $conn) {
		$sql = "SELECT `id` FROM `user` WHERE `chatId` = '$chatId'";

		$result = mysqli_query($conn, $sql);

		if ($result === false)
			exit;

		else if (mysqli_num_rows($result) > 0)
			$sent = true;

		else
			$sent = false;
		
		return $sent;
	}
?>