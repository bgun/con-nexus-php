<?php
// read data file
$fp = fopen('data/jcon2012_2012-03-25.tsv', 'r');

// connect to db
include("_db.php");
$link = mysql_connect($dbserver, $dbuser, $dbpass) or die('Cannot connect to the DB');
mysql_select_db('connexus',$link) or die('Cannot select the DB');

function findGuest($name) {
	$sql = "SELECT GuestID, FirstName, LastName FROM Guests";
	$result = mysql_query($sql);
	$guests = array();
	$lowlev = 100;
	$lowlevname = "";
	if(mysql_num_rows($result)) { 
		while($o = mysql_fetch_object($result)) { 
			$guests[] = $o;
		}
	}
	foreach($guests as $g) {
		$testname = trim($g->FirstName).' '.trim($g->LastName);
		$levtest = levenshtein($name, $testname);
		if($levtest < 2) {
			return $g->GuestID;
		}
		if($levtest < $lowlev) {
			$lowlev = $levtest;
			$lowlevname = $testname;
		}
	}
	echo "<h4>Guest not found. Closest match is $lowlevname [$lowlev].</h4>";
	return -1;
}

$i = 0;
while (!feof($fp)) {
	$i++;
    $line = fgets($fp, 2048);
    $delimiter = "\t";
    $data = str_getcsv($line, $delimiter);
	$dateFormat = 'Y-m-d G:i:s';	
	
	if(count($data) < 6) {
		die("<h3>ERROR: Row $i is malformed.</h3>");
	}
	
	$cid         = 9; // JordanCon 2012
	$title       = mysql_real_escape_string(trim($data[1]));
	$description = mysql_real_escape_string(trim($data[2]));
	$guests      = str_getcsv(mysql_real_escape_string(trim($data[3])), ',');
	$startdate   = date($dateFormat, strtotime($data[4]));
	$location    = mysql_real_escape_string(trim($data[5]));
	
	$sql  = "INSERT INTO Events (ConventionID, Title, Description, StartDate, Location)";
	$sql .= " VALUES ($cid, '$title', '$description', '$startdate', '$location')";
	
	if(mysql_query($sql)) {
		echo "<strong>Success!</strong> $sql<br />";
		
		// get ID of newly created event
		$newEventId = mysql_insert_id();
		$newGuestId = 0;
		
		foreach($guests as $g) {
			// if guest exists get their ID, otherwise create new guest and get ID
			$g = trim($g);
			$guestId = findGuest($g);
			if($guestId > 0) {
				// connect guest to event
				$sql = "INSERT INTO LinkEventsGuests (EventID, GuestID) VALUES ($newEventId, $guestId)";
				mysql_query($sql) or die("Something went wrong connecting a guest ($g), around row $i");
				echo "Connected guest $g.<br />";
			} else {
				// create new guest, and connect them to event
				if(trim($g) == "") {
					echo "<h3>No guests for this event.</h3>";
				} else {
					$gNameSplit = str_getcsv($g, ' ');
					$gNameCount = count($gNameSplit);
					if($gNameCount == 2) {
						$sql = "INSERT INTO Guests (FirstName, LastName) VALUES ('".$gNameSplit[0]."','".$gNameSplit[1]."')";
					} elseif($gNameCount == 3) {
						$sql = "INSERT INTO Guests (FirstName, LastName) VALUES ('".$gNameSplit[0]." ".$gNameSplit[1]."','".$gNameSplit[2]."')";
					} else {
						$sql = "INSERT INTO Guests (FirstName) VALUES ('$g')";
						echo "<h3>Unusual name alert: $g</h3>";
					}
					mysql_query($sql) or die("Something went wrong inserting a new guest ($g), around row $i<br />$sql");
					$newGuestId = mysql_insert_id();
					$sql = "INSERT INTO LinkEventsGuests (EventID, GuestID) VALUES ($newEventId, $newGuestId)";
					mysql_query($sql) or die("Something went wrong connecting a new guest ($g [$newGuestId]), around row $i");
					echo "Created and connected guest $g.<br />";
				}
			}
		}
	} else {
		$error = mysql_error();
		die("<h3>ERROR: SQL query failed at row $i:</h3>$sql");
	}
	
	echo "<hr />";
}                              
fclose($fp);

mysql_close($link);
?>
