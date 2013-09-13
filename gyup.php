<?php
	// Gyazo Upload-script for K96.co
	// error_reporting(E_ALL); // Prevents PHP spewing out debug info to the client.
	// find -type f -name '*.png' | while read f; do mv "$f" "${f%.png}"; done // This command will be used to remove the .png extention from old files.

	// Variables

	$echoURL = 'http://beta.K96.co/';
	$savePath = '/var/www/beta/i/';
	$validUagent = 'GyazoKD/0.30';
	$randChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$SQL_enabled = true;

	// Body

	include('../imageloader/inc/SQL_auth.php'); // $SQL_server, $SQL_db, $SQL_user, $SQL_password
	$isGyKD = preg_match('/^GyazoKD/', $_SERVER['HTTP_USER_AGENT']);
	list($imgWidth, $imgHeight, $imgFormat) = getimagesize($_FILES['imagedata']['tmp_name']);
	$imgSize = $imgWidth + $imgHeight;
	$date = date("Y-m-d H:i:s");
	$fileExist = false;
	$newID = false;
	
	if ($isGyKD == 0) {
		$valid = true;
	} else {
		if ($_SERVER['HTTP_USER_AGENT'] == $validUagent) {
			$valid = true;
		} else {
			$valid = false;
	}}
	
	if (isset($_FILES['id'])) {
		$UID = $_FILES['id']; }
	elseif ($isGyKD) {
			$UID = hash('md5', $_SERVER['REMOTE_ADDR'] . date("YmdHis"));
			header('X-GyazoKD-Id' . $UID);
			$newID = true; }

	if (isset($_FILES['id'])) {
		$hasID = true;
	} elseif ($newID == true) {
		$hasID = true;
	}

		function save($idLen) {
			global $valid, $echoURL, $savePath, $randChars, $fileExist, $imgID, $imgPath;

			for ( $c = 0; $c <= 32; $c++) {
				$imgID = substr(str_shuffle(str_repeat($randChars, $idLen)), 0 , $idLen);
				$imgPath = $savePath . $imgID;

				if (file_exists($imgPath)) {
					$fileExist = true;

				} else {
					$fileExist = false;
					move_uploaded_file($_FILES['imagedata']['tmp_name'], $imgPath);
					if ($valid == true) {
						echo $echoURL . $imgID;
					} else {
						echo $echoURL . 'dl/?file=gyazo&id=' . $imgID; }
					SQL($imgID);
					break;
		}}}

		function SQL($imgID) {
			global $newID, $hasID, $UID, $imgFormat, $date, $SQL_enabled, $SQL_server, $SQL_user, $SQL_password, $SQL_db;
			if ($SQL_enabled) {
				$sql_connect = new mysqli($SQL_server, $SQL_user, $SQL_password, $SQL_db);
					if ($newID) { mysqli_query($sql_connect, "INSERT INTO users ( UID ) VALUES ('" . $UID . "') "); }
					if ($hasID) { mysqli_query($sql_connect, "UPDATE users SET IMAGES = CONCAT('" . $imgID . "; ', IMAGES) WHERE UID = '" . $UID . "')"); }
					mysqli_query($sql_connect, "INSERT INTO images ( ID, DATE, VIEWS, FORMAT ) VALUES ('" . $imgID ."', '" . $date . "', 0, '" . $imgFormat . "')" );
					mysqli_close($sql_connect);
		}}

	//Start

	if(isset($_FILES['imagedata']['tmp_name'])) {
		if ($imgFormat >= 1) { // Checks if the uploaded file is a valid image format.
		if ($imgSize >= 16) { // Checks that image contains at least 16 pixels.
			save(5);
				if ($fileExist) {
					save(6);
					if ($fileExist) {
						echo 'error: Could not generate image ID. Please contact administrator.';
	}}}}} else { include("huehue.php");} // Insert your own Easter Egg here.
?>