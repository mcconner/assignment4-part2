<?php 
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include 'secret.php';

$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "mcconner-db", $password, "mcconner-db");
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
} else {
	echo "Connection worked!<br>";
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
	//user clicked button to add a video
	if(isset($_POST['addVideo'])){
		//check if user entered data for video name, category, and length
		if(isset($_POST['name']))
		$videoName = $_POST['name'];
		if(isset($_POST['category']))
		$videoCategory = $_POST['category'];
		if(isset($_POST['length']))
		$videoLength = $_POST['length'];
		
		//validation for name and length 
		if(!$videoName){
			$errorMsg = "Please enter a video name";
		}else if(!is_int($videoLength)){
			$errorMsg = "Your video length is not valid.";
		}
		
		if(isset($errorMsg))
			echo $errorMsg;
		
		//preparation statement to insert data into Videos table
		$stmt = $mysqli->prepare("INSERT INTO Videos (name, category, length) VALUES (?, ?, ?)");
		$stmt->bind_param("ssi", $videoName, $videoCategory, $videoLength);
		$stmt->execute();
		$stmt->close();

	}
	//user clicked button to delete all videos
	else if(isset($_POST['deleteAll'])){
		$deleteAllData = $mysqli->prepare("DELETE FROM Videos");
		$deleteAllData->execute();
		$deleteAllData->close();
	}
	//user clicked button to delete a specific video
	else if(isset($_POST['deleteVideo'])){
		$deleteId = $_POST['deleteVideo'];
		$deleteRow = $mysqli->prepare("DELETE FROM Videos WHERE id = ?");
		$deleteRow->bind_param("i", $deleteId);
		$deleteRow->execute();
		$deleteRow->close();
	}
	//user clicked button to check-out video that is available
	else if(isset($_POST['Check-Out'])){
		$updateRentId = $_POST['Check-Out'];
		$updateRow = $mysqli->prepare("UPDATE Videos SET rented='1' WHERE id = ?");
		$updateRow->bind_param("i", $updateRentId);
		$updateRow->execute();
		$updateRow->close();
	}
	//user clicked button to check-in a video that is currently checked out 
	else if(isset($_POST['Check-In'])){
		$updateRentId = $_POST['Check-In'];
		$updateRow = $mysqli->prepare("UPDATE Videos SET rented='0' WHERE id = ?");
		$updateRow->bind_param("i", $updateRentId);
		$updateRow->execute();
		$updateRow->close();
	}
	//something unexpected happened 
	else{
		echo "Not an expected button!";
	}
}

?>



<!DOCTYPE>
<html>
<body>
<div>
	<form method="POST" action="interface.php">
	<h3>Add Video</h3>
		<fieldset>
		<p>Name:<input type="text" name="name" required></p>
		<p>Category: <input type="text" name="category"></p>
		<p>Length: <input type="number" min="0" name="length"></p>
		</fieldset>
		<p><input type="submit" name="addVideo" value="Add Video"></p>
	</form>
	<br>
</div>

<div>
<h3>Videos</h3>
<form method="POST" action="interface.php"><p><input type="submit" name="deleteAll" value="Delete All"></p></form>
<fieldset>
	<table border=1>
		<tr>
			<th>Id
			<th>Name
			<th>Category
			<th>Length
			<th>Rented
		</tr>
		
		<?php 
		$cArr = array();
		
		$ddFilter = $mysqli->prepare("SELECT DISTINCT category FROM Videos WHERE category IS NOT NULL AND category <> '' ORDER BY category ");
		$ddFilter->execute();
		$ddFilter->bind_result($cFilter);
		echo '<form method="GET" action="interface.php" name="filter" value="-SELECT-">';
		echo '<select name="filterCategory">';
		echo '<option value="All">-Select a Filter-</option>';
		while($ddFilter->fetch()){
			array_push($cArr, $cFilter);
			echo "<option value='" . $cFilter . "'>" . $cFilter . "</option>";
		}
		echo '<option value="All">All Videos</option>';
		$ddFilter->close();
		
		echo '<input type="submit" value="Apply Filter">';
		echo '</select>';
		echo '</form>';
		echo '<br>';
		?>
		
		<?php 
		
		//filter results 
		$filterBy = 'All';
		if(isset($_GET['filterCategory']))
			$filterBy = $_GET['filterCategory'];

		//display video table (ALL VIDEOS)
		if($filterBy == 'All'){
			$displayAll = $mysqli->prepare("SELECT * FROM Videos");
			$displayAll->execute();
		}else{
		//user selected a filter 
		$displayAll = $mysqli->prepare("SELECT * FROM Videos WHERE category = ?");
		$displayAll->bind_param("s", $filterBy);
		$displayAll->execute();
		}
		$displayAll->bind_result($vidId, $vidName, $vidCat, $vidLength, $vidRented);
		while($displayAll->fetch()){
			echo '<tr>';
			echo '<td>' . $vidId . '</td>';
			echo '<td>' . $vidName . '</td>';
			echo '<td>' . $vidCat . '</td>';
			echo '<td>' . $vidLength . '</td>';
			if($vidRented === 0){
				$rentStatus = "Available";
				$btnStatus = "Check-Out";
			}
			
			if($vidRented === 1){
				$rentStatus = "Checked Out";
				$btnStatus = "Check-In";
			}	
			echo '<td>' . $rentStatus . '</td>';
		?>
		
		<!--http://stackoverflow.com/questions/3317730/putting-a-php-variable-in-a-html-form-value-->
		<td><form method="POST" action="interface.php"><button type="submit" value="<?php echo htmlspecialchars($vidId);?>" name="deleteVideo">Delete</button></form></td>
		<td><form action="interface.php" method="POST"><button type="submit" value="<?php echo htmlspecialchars($vidId);?>" name="<?php echo htmlspecialchars($btnStatus);?>"><?php echo $btnStatus; ?></button></form></td>
			
		<?php 
			echo '</tr>';
		}
		$displayAll->close();
		?>
		
	</table>
</fieldset>
</div>
</body>
</html>