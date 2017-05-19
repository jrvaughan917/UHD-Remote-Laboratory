<?php
	
	ob_start();
	session_start();
	
	if ($_POST['loginEmail'])
	{
		$_SESSION['loginEmail'] = $_POST['loginEmail'];
	}
	
	if ($_POST['loginPassword'])
	{
		$_SESSION['loginPassword'] = $_POST['loginPassword'];
	}
	
	$loginEmail = $_SESSION['loginEmail'];
	$loginPassword = $_SESSION['loginPassword'];
	
	$isLoggedIn = false;
	
	$link = mysqli_connect("/*server*/", "/*database user*/", "/*password*/", "/*database name*/"); // server, db user, pw, db name
	
	if (mysqli_connect_error())
	{
		die ("There was an error connecting to the database");
	}
	
	$query = "SELECT passwordHash FROM userTable WHERE userEmail = '".mysqli_real_escape_string($link, $loginEmail)."'";
	$result = mysqli_query($link, $query);
	if (mysqli_num_rows($result)!=0) { // Check to see if there is a result
		$row = mysqli_fetch_array($result);
		
		//if($row['passwordHash'] == $loginPassword) {
		
		if (password_verify($loginPassword, $row['passwordHash'])) {
			$query = "SELECT name FROM userTable WHERE userEmail = '".mysqli_real_escape_string($link, $loginEmail)."'";
		
			$result = mysqli_query($link, $query);
			
			$row = mysqli_fetch_array($result);
			
			$loginName = $row['userName'];
			
			echo("** Logged in as: ".$loginName." **<br>");
			
			$isLoggedIn = true;
		}
	}
	
	if ($isLoggedIn == false)
	{
		session_unset();
		header('Location:index.php'); // If user isn't logged in, send them to login page
	}
	
	if ($_SESSION['message'])
	{
		echo $_SESSION['message'];
		unset($_SESSION['message']);
	}
	
	if($_POST['dateChosen'] && $_POST['timeChosen']) {
		$timeChosenConverted;
		
		if(preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $_POST['dateChosen'])) { // Check for valid date format
			switch ($_POST['timeChosen']) {
				case "6am - 7am": $timeChosenConverted = "6:00:00"; break;
				case "7am - 8am": $timeChosenConverted = "7:00:00"; break;
				case "8am - 9am": $timeChosenConverted = "8:00:00"; break;
				case "9am - 10am": $timeChosenConverted = "9:00:00"; break;
				case "10am - 11am": $timeChosenConverted = "10:00:00"; break;
				case "11am - 12pm": $timeChosenConverted = "11:00:00"; break;
				case "12pm - 1pm": $timeChosenConverted = "12:00:00"; break;
				case "1pm - 2pm": $timeChosenConverted = "13:00:00"; break;
				case "2pm - 3pm": $timeChosenConverted = "14:00:00"; break;
				case "3pm - 4pm": $timeChosenConverted = "15:00:00"; break;
				case "4pm - 5pm": $timeChosenConverted = "16:00:00"; break;
				case "5pm - 6pm": $timeChosenConverted = "17:00:00"; break;
				case "6pm - 7pm": $timeChosenConverted = "18:00:00"; break;
				case "7pm - 8pm": $timeChosenConverted = "19:00:00"; break;
				case "8pm - 9pm": $timeChosenConverted = "20:00:00"; break;
				case "9pm - 10pm": $timeChosenConverted = "21:00:00"; break;
				
			}
			
			$insertDatetime = $_POST['dateChosen']." ".$timeChosenConverted;
			
			//$_SESSION['message'] = $_SESSION['message'].$insertDatetime;
			
			$timeSlotAvailable = true;
			
			
		
			$query = "SELECT * FROM `timeSlotSchedule` WHERE `slotStart` = '".mysqli_real_escape_string($link, $insertDatetime)."';";
			$result = mysqli_query($link, $query);
			if(mysqli_num_rows($result)!=0) // this time slot is already taken
			{
				$_SESSION['message'] = $_SESSION['message']."** Sorry, that time slot is already taken! **<br>";
				//$_SESSION['message'] = $_SESSION['message'].print_r($result, true);
				$timeSlotAvailable = false;
			}
			
  			$query = "SELECT * FROM `timeSlotSchedule` WHERE `slotUserEmail` = '".mysqli_real_escape_string($link, $_SESSION['loginEmail'])."'";
			$result = mysqli_query($link, $query);
			if(mysqli_num_rows($result)!=0) // this user already has a time slot registered
			{
				$_SESSION['message'] = $_SESSION['message']."** Sorry, you already have a time slot reserved! **<br>";
				$timeSlotAvailable = false;
			}
			
			
			
			 
			
			if ($timeSlotAvailable) 
			{			
				
				$query = "INSERT INTO `timeSlotSchedule` (`slotStart`, `slotUserEmail`, `slotEnd`) VALUES ('".mysqli_real_escape_string($link, $insertDatetime)."', '".mysqli_real_escape_string($link, $_SESSION['loginEmail'])."', '".mysqli_real_escape_string($link, $insertDatetime)."' + INTERVAL 1 HOUR)";
				
				if ($result = mysqli_query($link, $query)) // insert the new time slot into the database
				{
					$_SESSION['message'] = $_SESSION['message']."** Time slot successfully reserved! **<br>";
				} else
				{
					$_SESSION['message'] = $_SESSION['message']."** An error has occurred, time slot could not be reserved. **<br>";
				}
			}
			
			header('Location:rl_login.php'); // return to the page and display messages
		}
		
	}
	
	if ($_POST['cancelTimeSlot']) { // User clicked the "cancel time slot" button
		$query = "SELECT * FROM `timeSlotSchedule` WHERE `slotUserEmail` = '".mysqli_real_escape_string($link, $_SESSION['loginEmail'])."'";
		$result = mysqli_query($link, $query);
		if(mysqli_num_rows($result)!=0) // timeslot(s) will be deleted
		{
			$_SESSION['message'] = $_SESSION['message']."** Your time slot has been canceled! **<br>";
		}
		
		$query = "DELETE FROM `timeSlotSchedule` WHERE `slotUserEmail` = '".mysqli_real_escape_string($link, $_SESSION['loginEmail'])."'";
		$result = mysqli_query($link, $query);
		header('Location:rl_login.php');
	}
	
	$query = "DELETE FROM timeSlotSchedule WHERE slotStart <= NOW() - INTERVAL 3 DAY"; // automatically delete old timeslots
	$result = mysqli_query($link, $query);
	
	if ($_POST['newConnectionString']) { // Admin user is changing the connection string
		$query = "INSERT INTO `previousConnectionStrings` (`ConnectionString`, `DateImplemented`) VALUES ('".mysqli_real_escape_string($link, $_POST['newConnectionString'])."', NOW())";
		
		$result = mysqli_query($link, $query);
		// Add the new connection string
		
		$query = "TRUNCATE TABLE `currentConnectionString`";
		$result = mysqli_query($link, $query);
		// Delete the previous connection string
		
		$query = "INSERT INTO `currentConnectionString` (`currentConnectionString`) VALUES ('".mysqli_real_escape_string($link, $_POST['newConnectionString'])."')";
		$result = mysqli_query($link, $query);
		// Insert the new connection string
		
		
		
		$_SESSION['message'] = $_SESSION['message']."** Connection string successfully changed! **<br>";
		header('Location:rl_login.php');
	}
	
	
	//print_r($_GET);
	//print_r($_POST);
	//print_r($_SESSION);
	
	
	
?>

<?php
if ($_FILES["fileToUpload"]["name"]) {
	// Doesn't need file_uploads = On in php.ini?
	// Need to have limit of files... 50?
	$target_dir = "uploads/".$_SESSION['loginEmail']."/";
	$target_file = $target_dir.basename($_FILES["fileToUpload"]["name"]);
	$uploadOk = 1;
	
	$uploadFileType = pathinfo($target_file,PATHINFO_EXTENSION);
	if (file_exists($target_file)) {
		$_SESSION['message'] = $_SESSION['message']."** Sorry, that file has already been uploaded. **<br>";
		$uploadOk = 0;
	}
	if(($uploadFileType != "txt") && ($uploadFileType != "lvm")) {
		$_SESSION['message'] = $_SESSION['message']."** Sorry, only .txt and .lvm files are allowed. **<br>";
		$uploadOk = 0;
	}
	if ($_FILES["fileToUpload"]["size"] > 5000000) { // Approx 5 megs
		$_SESSION['message'] = $_SESSION['message']."** Sorry, your file is too large. **<br>";
		$uploadOk = 0;
	} 
	if ($uploadOk == 1) {
		if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
			$_SESSION['message'] = $_SESSION['message']."** The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded. **<br>";
		} else {
			$_SESSION['message'] = $_SESSION['message']."** Sorry, there was an error uploading your file. **<br>";
		}
	}
	
	unset($_FILES);
	header('Location:rl_login.php'); // return to the page and display new file list
}

if ($_POST['fileDeleteName']) {
	$target_dir = "uploads/".$_SESSION['loginEmail']."/";
	$target_file = $target_dir.$_POST['fileDeleteName'];
	if (unlink($target_file)) {
		$_SESSION['message'] = $_SESSION['message']."** The file ".$_POST['fileDeleteName']." has been deleted. **<br>";
	} else {
		$_SESSION['message'] = $_SESSION['message']."** Sorry, there was an error deleting your file. **<br>";
	}
	
	header('Location:rl_login.php'); // return to the page and display new file list
}


?>

<html>

	<head>
		
	
		<title>UHD Remote Lab Account Home</title>
		
		<link rel="stylesheet" href="bootstrapStylesheet.css">
		<!--
		redundant stylesheet
		-->
		
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
			
		<style type="text/css">
			body, html {
				overflow-x: hidden;
				height: auto;
			}
		
			body {
				font-family: helvetica, sans-serif;
				font-size:170%;
			}
			
			input {
				height: 40px;
				padding: 5px 5px 10px;
				font-size:110%;
				border-radius: 5px;
				border:1px solid gray;
				width: 220px;
			}
			
			label {
				position: relative;
				top: 0px;
				width: 280px;
				float: left;
			}
			
			#wrapper {
					width: 550px;
					margin: 0 auto;
					
			}
			
			.form-element {
				margin-bottom: 10px;
			}
			
			#submitButton {
				width: 130px;
				margin-left: 280px;
			}
			
			#loginButton {
				width: 130px;
				margin-left: 180px;
			}
			
			#logoutButton {
				width: 130px;
				margin-left: 0px;
			}
			
			#remoteControlButton {
				width: 310px;
				margin-left: 0px;
			}
			
			#timeslotButton {
				width: 230px;
				margin-top: 0px;
			}
			
			#uploadButton {
				width: 150px;
				margin-top: 0px;
			}
			
			#changeConnectionString {
				width: 270px;
				margin-top: 0px;
			}
			
			#fileToUpload {
				width: 100%;
				margin-top: 0px;
			}
			
			#errorMessage {
				color: red;
				font-size: 90% !important;
				margin-bottom: 20px;
			}
			
			#successMessage {
				color: green;
				font-size: 90% !important;
				display: none;
				margin-bottom: 20px;
			}
			
			h1{
				text-align:center;
			}
			
			feedbackEmail {
				display: block;
				text-align: center;
				color: gray;
				width: 100%;
			}
			
			table {
				font-family: helvetica, sans-serif;
				font-size:100%;
				border-collapse: collapse;
				width: 100%;
			}
			
			td, th {
				border: 1px solid #dddddd;
				text-align: left;
				padding: 8px;
			}
			
			#logo {
				max-width: 100%;
				max-height:100%;
				display: block; /* Instead of display: block; */
				margin: 0 auto;
				vertical-align: middle;
				width: 35%;
				text-align: center;
			}
			
		</style>
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js">
		</script>
		
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		
		
	</head>
	
	<body>
		<div id="wrapper">
			<br>
			<img src="UHD_logo.png" id="logo" />
	
			<h1>University of Houston-Downtown<br/> Remote Laboratory</h1>
			
			<form method="post" action="index.php" id="myForm">
				<div class="form-element">
					<input type="submit" id="logoutButton" value="Logout">
				</div>
			</form>
			
			<div class="form-element">
				<h2>Smart Vibration Platform (SVP)</h2>
				
				<div>
					<img src="SVP3.png" width="300px" />
				</div>
				
				<br>
				
				<form method="post" action="rl_SVPabout.php" id="myForm">
					<div class="form-element">
						<input type="submit" id="timeslotButton" value="About this Machine">
					</div>
				</form>
				
				<br>
				
				<strong>Time Slot Schedule</strong><br>
				
				
				<?php
					
					$query = "SELECT * FROM timeSlotSchedule WHERE slotStart > NOW() - INTERVAL 3 DAY ORDER BY slotStart";
					$result = mysqli_query($link, $query);
			
					
					
					echo '<table>';
					echo '<tr><th>Start</th><th>End</th><th>User</th></tr>';
					
					while($row = mysqli_fetch_array($result)) {
						$slotStart = $row['slotStart'];
						$slotEnd = $row['slotEnd'];
						$slotUserEmail = $row['slotUserEmail'];
						
						$nameQuery = "SELECT name FROM userTable WHERE userEmail = '".mysqli_real_escape_string($link, $slotUserEmail)."'";
		
						$nameResult = mysqli_query($link, $nameQuery);
						
						$nameRow = mysqli_fetch_array($nameResult);
						
						$slotUserName = $nameRow['userName'];
						echo '<tr><td>'.$slotStart.'</td><td>'.$slotEnd.'</td><td>'.$slotUserName.'</td></tr>';
					}
						
					
					
					echo '</table>';
				?>
			
			</div>
			
			<br>
			

			<form method="post" action="rl_login.php" id="myForm">
				<div class="form-element">
					<strong>Reserve a Time Slot </strong>
					
					<div class="form-element">
						<div class="form-element">
							<div id="datepicker"></div>
						</div>
						
						<input type="hidden" id="dateChosen" name="dateChosen">

						<select name="timeChosen" id="timeChosen">
						  <option>6am - 7am</option>
						  <option>7am - 8am</option>
						  <option>8am - 9am</option>
						  <option>9am - 10am</option>
						  <option>10am - 11am</option>
						  <option>11am - 12pm</option>
						  <option>12pm - 1pm</option>
						  <option>1pm - 2pm</option>
						  <option>2pm - 3pm</option>
						  <option>3pm - 4pm</option>
						  <option>4pm - 5pm</option>
						  <option>5pm - 6pm</option>
						  <option>6pm - 7pm</option>
						  <option>7pm - 8pm</option>
						  <option>8pm - 9pm</option>
						  <option>9pm - 10pm</option>
						</select>
					</div>
				</div>
				
				<div class="form-element">
					<input type="submit" id="timeslotButton" value="Reserve My Time Slot">
				</div>
			</form>
			
			<form method="post" action="rl_login.php" id="myForm">
				<div class="form-element">
					<input type="hidden" id="cancelTimeSlot" name="cancelTimeSlot" value="cancelTimeSlot">
					<input type="submit" id="timeslotButton" value="Cancel My Time Slot">
				</div>
			</form>
			
			<br>
			
			<strong>Remote Control</strong><br>
			
			
			Time until your Remote Control Session: <span id="timeUntilSlotStart"></span><br>
			
			

			<script>
				// Set the date we're counting down to
				
				var existsSession = <?php 
					$query = "SELECT * FROM timeSlotSchedule WHERE slotUserEmail = '".mysqli_real_escape_string($link, $_SESSION['loginEmail'])."'"; // select user's time slot
					$result = mysqli_query($link, $query);
					if (mysqli_num_rows($result)!=0) { // user has a time slot scheduled
						echo "1";
					} else {
						echo "0";
						
					}
				?>;
				
				if (existsSession == 0) {
					document.write("You have no current session.");
				} else {
				
					var sessionStartTime = new Date("<?php
					
						$query = "SELECT * FROM timeSlotSchedule WHERE slotUserEmail = '".mysqli_real_escape_string($link, $_SESSION['loginEmail'])."'"; // select user's time slot
						$result = mysqli_query($link, $query);
						$row = mysqli_fetch_array($result);
						$slotStart = $row['slotStart'];
						echo $slotStart;

					?>").getTime();
					
					var sessionEndTime = new Date("<?php
					
						$query = "SELECT * FROM timeSlotSchedule WHERE slotUserEmail = '".mysqli_real_escape_string($link, $_SESSION['loginEmail'])."'"; // select user's time slot
						$result = mysqli_query($link, $query);
						$row = mysqli_fetch_array($result);
						$slotEnd = $row['slotEnd'];
						echo $slotEnd;

					?>").getTime();
					
					// TEST
					// sessionStartTime = new Date().getTime(); // allows entering the remote control screen
					// TEST

					// Update the count down every 1 second
					
					var currentDate = "<?php
						date_default_timezone_set('America/Chicago'); // This keeps track of daylight savings
						print_r(date("Y-m-d H:i:s"));
					?>";
					var addSeconds = 0;
					
					var x = setInterval(function() {
						
						// == FINDING THE CURRENT TIME ==
						//
						// UTC_TIMESTAMP doesn't take local time into account.
						// Need to use time zone America/Chicago
						//
						// All datetimes will inserted into database in the assumed Houston format for simplicity. 
						
						// Get todays date and time
						var now = new Date(currentDate).getTime() + 30000 + addSeconds; // There is a 30 second buffer between when the current time slot ends and when the next begins. This allows the server time to allow old new control to end and new control to begin
						addSeconds = addSeconds + 1000;
						
						
						// Find the distance between now an the count down date
						var distance = sessionStartTime - now;
						
						// Time calculations for days, hours, minutes and seconds
						var days = Math.floor(distance / (1000 * 60 * 60 * 24));
						var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
						var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
						var seconds = Math.floor((distance % (1000 * 60)) / 1000);
						
						// Output the result in an element with id="timeUntilSlotStart"
						document.getElementById("timeUntilSlotStart").innerHTML = "<font color='orange'>" + days + "d " + hours + "h "
						+ minutes + "m " + seconds + "s </font>";
						
						// If the count down is over, write some text 
						if ((distance < 0) && (distance > -(1000*60*60))) {
							clearInterval(x);
							document.getElementById("timeUntilSlotStart").innerHTML = "<font color='green'>Your time slot is now open!</font>";
							
							document.getElementById("remoteControlButton").disabled = false;
							document.getElementById("remoteControlButton").style = "color:default";
						}
						
						if ((distance < 0) && (distance <= -(1000*60*60))) {
							clearInterval(x);
							document.getElementById("timeUntilSlotStart").innerHTML = "<font color='red'>Your time slot has expired!</font>";
							
							document.getElementById("remoteControlButton").disabled = true;
							document.getElementById("remoteControlButton").style = "color:gray";
						}
						
					}, 1000);
				}
			</script>
			
			<form method="post" action="rl_SVPremote.php" id="myForm">
				<div class="form-element">
					<input type="submit" id="remoteControlButton" value="Enter Remote Control Session"  disabled style="color:gray">
				</div>
			</form>
			
			
			
			
			
			
			<br>
			
			
			
			
			
			
			<strong>Download Experiment Data Files </strong>
			
			

			<table>
				
				<?php
					$folder = "uploads/".$_SESSION['loginEmail']; // every email has its own file directory
					if(!is_dir($folder)) 
					{mkdir($folder, 0755);}
				
					$files1 = scandir($folder);

					$fileNumber = 2;
					$deleteRowNumber = 1;
					while ($fileNumber < count($files1))
					{
						echo "<tr>";
						echo "<td>".$files1[$fileNumber]."</td>";
						
						
						echo "<td width='20%' style='text-align:center'>
							<a href='uploads/".$_SESSION['loginEmail']."/".$files1[$fileNumber]."' download='".$files1[$fileNumber]."'>Download</a>
						</td>";
						
						echo "<td width='15%' style='text-align:center'><form action='rl_login.php' method='POST' id='delete".$deleteRowNumber."' style='margin-bottom:0px'>
							<input type = 'hidden' name='fileDeleteName' value='".$files1[$fileNumber]."'>
							<a href='' onclick=\"document.forms['delete".$deleteRowNumber."'].submit();return false;\">Delete</a>
						</form></td>";
						echo "</tr>";
						
						$deleteRowNumber++;
						
						$fileNumber++;
					}
					
					if ($fileNumber == 2)
					{ echo "<br>You have no experiment files uploaded.";}
				
				?>
				
				
				</tr>
			</table>
			
			<br>
			
			<p>
			<form action="rl_login.php" method="post" enctype="multipart/form-data"> 
				<strong>Upload Experiment Data Files </strong>
				<p>
				<input type="file" name="fileToUpload" id="fileToUpload">
				</p>
				<input type="submit" value="Upload File" name="uploadButton" id="uploadButton">
			</form>
			</p>
			
		
		<div class="form-element">
		<?php 
			$query = "SELECT * FROM `administrators` WHERE `adminUserEmail` = '".mysqli_real_escape_string($link, $_SESSION['loginEmail'])."'";
			
			$result = mysqli_query($link, $query);
			// Check to see if user is an administrator
			
			if(mysqli_num_rows($result)!=0) // this user is an administrator
			{
				echo "<form action='rl_login.php' method='post'>
				<br><strong>Current Connection String (ADMIN/FACULTY OPTION)</strong><br>";
				  
				$query = "SELECT * from `currentConnectionString`";
				$result = mysqli_query($link, $query);
				$row = mysqli_fetch_array($result);
				
				  
				echo $row['currentConnectionString'];
				echo "<br>";
				
				echo "<strong>Previous Connection Strings</strong><br>";
				$query = "SELECT * from `previousConnectionStrings` ORDER BY DateImplemented";
				$result = mysqli_query($link, $query);
				while($row = mysqli_fetch_array($result)) {
					echo $row['ConnectionString'];
					echo "        Implemented: ";
					echo $row['DateImplemented'];
					echo "<br>";
				}
				
				  
				echo "<strong>Change Connection String to</strong><br>
				<input type='text' name='newConnectionString' style='width:100%; margin-bottom:10px' ><br>
				<input type='submit' value='Change Connection String' name='changeConnectionString' id='changeConnectionString'>
				</form> ";
			}
		
		?>
		</div>

		</div>
			
		
		<script type="text/javascript">
			$( function() {
				$( "#datepicker" ).datepicker( 
				{
					altField: "#dateChosen",
					dateFormat: 'yy-mm-dd'
				}
				);
			});
			
			
			$( function() {
				$( "#timeChosen" ).selectmenu( 
					{
						change: function() 
						{
							
						} 
					}
				);
			} );
		</script>
		
		
		<br><br><br><br><br><br><br><br><br><br>
		<br><br><br><br><br><br><br><br><br><br>
		
		<div>
		<feedbackEmail>
		Website created by James R. Vaughan.<br>
		Send questions, comments, or other feedback to: <a href="mailto:uhdremotelab@gmail.com?Subject=UHD Remote Lab Feedback" target="_top">uhdremotelab@gmail.com</a>
		</feedbackEmail>
		<br><br>
		</div>
	</body>

</html>