<?php 
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "mcconner-db", "P5lI87Z04uiRWTgl", "mcconner-db");
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
} else {
	echo "Connection worked!<br>";
}

echo "videoID = " . $_POST['CheckInOut'];
$updateRentId = $_POST['CheckInOut'];

$updateRow = $mysqli->prepare("UPDATE Videos SET rented='1' WHERE id = ?");
$updateRow->bind_param("i", $updateRentId);
$updateRow->execute();
$updateRow->close();

?>