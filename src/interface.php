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
	if(isset($_POST['addVideo'])){
		//check if user entered data for video name, category, and length
		if(isset($_POST['name']))
		$videoName = $_POST['name'];
		if(isset($_POST['category']))
		$videoCategory = $_POST['category'];
		if(isset($_POST['length']))
		$videoLength = $_POST['length'];
		
		$stmt = $mysqli->prepare("INSERT INTO Videos (name, category, length) VALUES (?, ?, ?)");
		$stmt->bind_param("ssi", $videoName, $videoCategory, $videoLength);
		$stmt->execute();
		$stmt->close();

	}
	else if(isset($_POST['deleteAll'])){
		$deleteAllData = $mysqli->prepare("DELETE FROM Videos");
		$deleteAllData->execute();
		$deleteAllData->close();
	}
	else if(isset($_POST['deleteVideo'])){
		echo "videoID = " . $_POST['deleteVideo'];
		$deleteId = $_POST['deleteVideo'];

		$deleteRow = $mysqli->prepare("DELETE FROM Videos WHERE id = ?");
		$deleteRow->bind_param("i", $deleteId);
		$deleteRow->execute();
		$deleteRow->close();
	}
	else if(isset($_POST['checkinout'])){
		echo "videoID = " . $_POST['checkinout'];
		$updateRentId = $_POST['checkinout'];
		
		$updateRow = $mysqli->prepare("UPDATE Videos SET rented='1' WHERE id = ?");
		$updateRow->bind_param("i", $updateRentId);
		$updateRow->execute();
		$updateRow->close();
	}
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
	<h3>Add Movie</h3>
		<fieldset>
		<p>Name:<input type="text" name="name"></p>
		<p>Category: <input type="text" name="category"></p>
		<p>Length: <input type="text" name="length"></p>
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
		
		//FILTER RESULTS
		//echo "filterCategory = " . $_GET['filterCategory'];
		$filterBy = 'All';
		if(isset($_GET['filterCategory']))
			$filterBy = $_GET['filterCategory'];
		echo "filterBy= [" . $filterBy . "]";

		//display video table (ALL VIDEOS)
		if($filterBy == 'All'){
			echo "Show All Videos";
			$displayAll = $mysqli->prepare("SELECT * FROM Videos");
			$displayAll->execute();
		}else{
			echo "Filter";
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
			if($vidRented === 0)
				$rentStatus = "Available";
			else
				$rentStatus = "Checked Out";
			echo '<td>' . $rentStatus . '</td>';
		?>
		
		<!--http://stackoverflow.com/questions/3317730/putting-a-php-variable-in-a-html-form-value-->
		<td><form method="POST" action="interface.php"><button type="submit" value="<?php echo htmlspecialchars($vidId);?>" name="deleteVideo">Delete</button></form></td>
		<td><form action="interface.php" method="POST"><button type="submit" value="<?php echo htmlspecialchars($vidId);?>" name="checkinout">Check In/Out</button></form></td>
			
		<?php 
			//echo '<td>' . "<input type='submit' value='Check In/Out'>" . '</td>';
			echo '</tr>';
		}
		$displayAll->close();
		?>
		
	</table>
</fieldset>
</div>
</body>
</html>