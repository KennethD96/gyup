<?php
	// Gyazo Upload-script for K96.co
	// error_reporting(E_ERROR); // Prevents PHP spewing out debug info to the client.
	// find -type f -name '*.png' | while read f; do mv "$f" "${f%.png}"; done // This command will be used to remove the .png extention from old files.
		
	// Variables
		
	$echoURL = 'http://beta.K96.co/';
	$savePath = '/var/www/beta/i/';
	$randChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		
	$SQL_enabled = true;
		
	// Body

	include('../inc/SQL_auth.php'); // $SQL_server, $SQL_db, $SQL_user, $SQL_password
	list($imgWidth, $imgHeight, $imgFormat) = getimagesize($_FILES['imagedata']['tmp_name']);
	$imgSize = $imgWidth + $imgHeight;
	$fileExist = false;

		function save($idLen) {
			global $echoURL, $savePath, $randChars, $fileExist, $imgID, $imgPath;
			
			for ( $c = 0; $c <= 32; $c++) {	
				$imgID = substr(str_shuffle(str_repeat($randChars, $idLen)), 0 , $idLen);
				$imgPath = $savePath . $imgID;
				
				if (file_exists($imgPath)) {
					$fileExist = true;
				
				} else {
					$fileExist = false;
					move_uploaded_file($_FILES['imagedata']['tmp_name'], $imgPath);
					echo $echoURL . $imgID;
						SQL($imgID);
					break;
		}}}
		
		function SQL($imgID) {
			global $imgFormat, $SQL_enabled, $SQL_server, $SQL_user, $SQL_password, $SQL_db;
			if ($SQL_enabled == true) {
				$date = date("Y-m-d H:i:s");
				$sql_connect = new mysqli($SQL_server, $SQL_user, $SQL_password, $SQL_db);
					mysqli_query($sql_connect, "INSERT INTO image ( ID, DATE, VIEWS, FORMAT ) VALUES ('" . $imgID ."', '" . $date . "', 0, '" . $imgFormat . "')" );
					mysqli_close($sql_connect);
		}}
		
	if(isset($_FILES['imagedata']['tmp_name'])) {
		if($imgFormat >= 1) { // Checks if the uploaded file is a valid image.
		if($imgSize >= 16) { // Checks that image contains at least 16 pixels.
			save(5);
				if ($fileExist == true) {
					save(6);
					if ($fileExist == true) {
						echo 'error: Could not generate image ID. Please contact administrator.'; 
	}}}}} else { include("huehue.php");} // Insert your own Easter Egg here.
?>