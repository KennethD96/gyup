<?php
	// Gyazo Upload-script for K96.co
	// error_reporting(E_ALL); // Prevents PHP spewing out debug info to the client.
	// find -type f -name '*.png' | while read f; do mv "$f" "${f%.png}"; done // This command will be used to remove the .png extension from old files.

	// Variables

	$echoURL = 'http://beta.K96.co/';
	$savePath = '/var/www/beta/i/';
	$validUagent = 'GyazoKD/0.30';
	$randChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	
	// Components
	
	$SQL_enabled = true;
	$IDgen_enabled = true;

	// Body

	include('../imageloader/inc/SQL_auth.php'); // $SQL_server, $SQL_db, $SQL_user, $SQL_password
	$isGyKD = preg_match('/^GyazoKD/', $_SERVER['HTTP_USER_AGENT']);
	list($imgWidth, $imgHeight, $imgFormat) = getimagesize($_FILES['imagedata']['tmp_name']);
	$imgSize = $imgWidth + $imgHeight;
	$date = date("Y-m-d H:i:s");
	$fileExist = false;
	$newID = false;
	
	if ($isGyKD == 0) { // Sets variable $valid as false if client is upgradable.
		$valid = true;
	} else {
		if ($_SERVER['HTTP_USER_AGENT'] == $validUagent) {
			$valid = true;
		} else {
			$valid = false;
	}}
	
	if (isset($_FILES['id'])) { // Generates Client ID and modifies return-header if client is applicable and don't already have ID.
		$UID = $_FILES['id'];
		$hasID = true; }
	elseif ($isGyKD) {
		if ($IDgen_enabled) {
			$UID = hash('md5', $_SERVER['REMOTE_ADDR'] . date("YmdHis"));
			header('X-GyazoKD-Id' . $UID);
			$newID = true; // Used by the SQL function.
			$hasID = true; }}

		function save($idLen) { // Generates image ID and saves the image.
			global $valid, $echoURL, $savePath, $randChars, $fileExist, $imgID, $imgPath;

			for ( $c = 1; $c <= 32; $c++) { // Repeats the process until it finds an unused image ID (up to 32 times)
				$imgID = substr(str_shuffle(str_repeat($randChars, $idLen)), 0 , $idLen);
				$imgPath = $savePath . $imgID;

				if (file_exists($imgPath)) {
					$fileExist = true;

				} else {
					$fileExist = false;
					move_uploaded_file($_FILES['imagedata']['tmp_name'], $imgPath);
					if ($valid == true) {
						echo $echoURL . $imgID; // Returns the final image URL and breaks out of the loop.
					} else {
						echo $echoURL . 'dl/?file=gyazo&id=' . $imgID; } // Sends the client to the download page if client can be updated.
					SQL($imgID);
					break;
		}}}

		function SQL($imgID) { // Creates image row and updates UID row (or ignores if no UID is defined).
			global $newID, $hasID, $UID, $imgFormat, $date, $SQL_enabled, $SQL_server, $SQL_user, $SQL_password, $SQL_db;
			if ($SQL_enabled) {
				$sql_connect = new mysqli($SQL_server, $SQL_user, $SQL_password, $SQL_db);
					if ($newID) { mysqli_query($sql_connect, "INSERT INTO users ( UID ) VALUES ('" . $UID . "') "); }
					if ($hasID) { mysqli_query($sql_connect, "UPDATE users SET IMAGES = CONCAT('" . $imgID . "; ', IMAGES) WHERE UID = '" . $UID . "')"); }
					mysqli_query($sql_connect, "INSERT INTO images ( ID, DATE, VIEWS, FORMAT ) VALUES ('" . $imgID ."', '" . $date . "', 0, '" . $imgFormat . "')" );
					mysqli_close($sql_connect);
		}}

	// Start

	if(isset($_FILES['imagedata']['tmp_name'])) {
		if ($imgFormat >= 1) { // Checks if the uploaded file is a valid image format.
		if ($imgSize >= 16) { // Checks that image contains at least 16 pixels.
			save(5); // First try with 5 symbols.
				if ($fileExist) {
					save(6); // Retry with 6 if 5 fails.
					if ($fileExist) {
						echo 'error: Could not generate image ID. Please contact administrator.';
	}}}}} else { include("huehue.php");} // Insert your own Easter Egg here.
?>