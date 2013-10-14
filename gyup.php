<?php
	// Gyazo Upload-script for K96.co
	// error_reporting(E_ERROR); // Prevents PHP spewing out debug info to the client.

	// Variables

	$echoURL = 'http://beta.K96.co/';
	$savePath = '../i/';
	$validUagent = 'GyazoKD/0.30';
	$randChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	
	include('../imageloader/inc/SQL_auth.php'); // $SQL_server, $SQL_db, $SQL_user, $SQL_password

	// Components

	$SQL_enabled = true;
	$IDgen_enabled = true;

	// Body

	$isGyKD = preg_match('/^GyazoKD/', $_SERVER['HTTP_USER_AGENT']);
	list($imgWidth, $imgHeight, $imgFormat) = getimagesize($_FILES['imagedata']['tmp_name']);
	$imgSize = $imgWidth + $imgHeight;
	$date = date("Y-m-d H:i:s");
	$fileExist = false;
	$newID = false;

	if ($imgFormat === 3) {
		$imgExt = 'png';
	} elseif ($imgFormat === 2) {
		$imgExt = 'jpg';
	} elseif ($imgFormat === 1) {
		$imgExt = 'gif';
	} else {
		$imgExt = $imgFormat; }

	if ($isGyKD === 0) { // Sets variable $valid as false if client is upgradable.
		$valid_Client = true;
	} else {
		if ($_SERVER['HTTP_USER_AGENT'] === $validUagent) {
			$valid_Client = true;
		} else {
			$valid_Client = false;
	}}

	if(strlen($_POST['id']) === 32 ) { // Generates Client ID and modifies return-header if client is applicable and don't already have ID.
		$UID = $_POST['id'];
		$hasID = true;
	} elseif($isGyKD) {
		if ($IDgen_enabled) {
			$UID = hash('md5', $_SERVER['REMOTE_ADDR'] . date("YmdHis"));
			header('X-Gyazo-Id:' . $UID);
			$newID = true; // Used by the SQL function.
			$hasID = true;
	}}

		function save($idLen) { // Generates image ID and saves the image.
			global $imgID, $imgPath, $valid_Client, $echoURL, $savePath, $randChars, $imgExt, $fileExist, $newID, $hasID, $UID, $imgFormat, $date, $SQL_enabled, $SQL_server, $SQL_user, $SQL_password, $SQL_db;
			$sql_connect = new mysqli($SQL_server, $SQL_user, $SQL_password, $SQL_db);

			if ($SQL_enabled) {
			for ( $c = 1; $c <= 32; $c++) { // Repeats the process until it finds an unused image ID (up to 32 times)
				$imgID = substr(str_shuffle(str_repeat($randChars, $idLen)), 0 , $idLen);
				$imgPath = $savePath . $imgID;
				$idCount = mysqli_fetch_assoc(mysqli_query($sql_connect, "SELECT ID, COUNT(ID) FROM images WHERE ID = '" . $imgID . "'"));

				if ($idCount['COUNT(ID)'] >= 1) {
					$fileExist = true;

				} else {
					$fileExist = false;
					move_uploaded_file($_FILES['imagedata']['tmp_name'], $imgPath . '.' . $imgExt );
					if ($valid_Client) {
						echo $echoURL . $imgID; // Returns the final image URL and breaks out of the loop.
					} else {
						echo $echoURL . 'dl/?file=gyazo&id=' . $imgID; } // Sends the client to the download page if client can be updated.

					if ($newID) { mysqli_query($sql_connect, "INSERT INTO users ( UID ) VALUES ('" . $UID . "') "); }
					if ($hasID) { mysqli_query($sql_connect, "UPDATE users SET IMAGES = CONCAT('" . $imgID . "', 'IMAGES' ) WHERE UID = '" . $UID . "')"); }
					mysqli_query($sql_connect, "INSERT INTO images ( ID, DATE, VIEWS, FORMAT ) VALUES ('" . $imgID ."', '" . $date . "', 0, '" . $imgFormat . "')" );
						mysqli_close($sql_connect);
						break;
			
			}}} else {
			for ( $c = 1; $c <= 32; $c++) {
				$imgID = substr(str_shuffle(str_repeat($randChars, $idLen)), 0 , $idLen);
				$imgPath = $savePath . $imgID;

				if (file_exists($imgPath)) {
					$fileExist = true;

				} else {
					$fileExist = false;
					move_uploaded_file($_FILES['imagedata']['tmp_name'], $imgPath);
					if ($valid_client) {
						echo $echoURL . $imgID;
					} else {
						echo $echoURL . 'dl/?file=gyazo&id=' . $imgID; }
					break;
		}}}}

	// Start

	if(isset($_FILES['imagedata']['tmp_name'])) {
		if ($imgFormat) { // Checks if the uploaded file is a valid image format.
		if ($imgSize >= 16) { // Checks that image contains at least 16 pixels.
			save(5); // First try with 5 symbols.
				if ($fileExist) {
					save(6); // Retry with 6 if 5 fails.
					if ($fileExist) {
						echo 'error: Could not generate image ID. Please contact administrator.';
	}}}}} else { include("huehue.php"); } // Insert your own Easter Egg here.
?>