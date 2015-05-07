<?php 
error_reporting(E_ALL);
ini_set('display_errors', 'On');

//check if user entered data for video name, category, and length
if(isset($_POST['name']))
	$videoName = $_POST['name'];
if(isset($_POST['category']))
	$videoCategory = $_POST['category'];
if(isset($_POST['length']))
	$videoLength = $_POST['length'];

//Connect to the database
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "mcconner-db", "P5lI87Z04uiRWTgl", "mcconner-db");
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: " . $mysqli->connect_errno . " " . $mysqli->connect_error;
} else {
	echo "Connection worked!<br>";
}

//Preparation statements for adding a video 
$stmt = $mysqli->prepare("INSERT INTO Videos (name, category, length) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $videoName, $videoCategory, $videoLength);
$stmt->execute();
$stmt->close();

?>