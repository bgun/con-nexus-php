<?php
error_reporting(E_ALL);

// data file parameter
if(isset($_GET['file'])) {
  $fp = fopen($_GET['file'], 'r');
  if(!$fp) {
    die("Could not open file.");
  }
} else {
  die("Please specify a CSV text file.");
}

// convention ID parameter
if(isset($_GET['cid'])) {
  $cid = $_GET['cid'];
} else {
  die("Please specify a cid.");
}

// connect to db
require_once("_db.php");
$link = mysql_connect($dbserver, $dbuser, $dbpass) or die('Cannot connect to the DB');
mysql_select_db($dbname,$link) or die('Cannot select the DB');

function findGuest($name) {
	$sql = "SELECT GuestID, FirstName, LastName FROM guests";
	$result = mysql_query($sql);

  // Ignore spaces, periods and case
  $name = strtolower($name);
  $name = str_replace(" ","",$name);
  $name = str_replace(".","",$name);

	$guests = array();
	if(mysql_num_rows($result)) { 
		while($o = mysql_fetch_array($result)) { 
      array_push($guests, $o);
		}
	}
	foreach($guests as $g) {
		$testname = strtolower($g["FirstName"].$g["LastName"]);
    $testname = str_replace(" ","",$testname);
    $testname = str_replace(".","",$testname);
   
    if($name == $testname) {
      return $g["GuestID"];
    }
    /*
		$levtest = levenshtein($name, $testname);
		if($levtest < 2) {
			return $g->GuestID;
		}
		if($levtest < $lowlev) {
			$lowlev = $levtest;
			$lowlevname = $testname;
		}
    */
	}
	echo "<h4>Guest ".$name." not found. Create one!</h4>";// Closest match is $lowlevname [$lowlev].</h4>";
	return -1;
}

echo "<h1>Import started</h1>";

$i = 0; // line counter
while (!feof($fp)) {
  echo "1";
	$i++;

  $delimiter = "|";
  $data = fgetcsv($fp, 2048, $delimiter);
	$dateFormat = 'Y/m/d G:i:s';	
	
	if(count($data) < 6) { // need all 5 columns to be present
    print_r($data);
		die("<h3>ERROR: Row $i is malformed: ".count($data)." rows</h3>");
	}	

	$track       = mysql_real_escape_string(trim($data[0]));
	$title       = mysql_real_escape_string(trim($data[1]));
	$description = mysql_real_escape_string(trim($data[2]));
	$guests      = explode(",",mysql_real_escape_string(trim($data[3])));
	$startdate   = date($dateFormat, strtotime($data[4]));
	$location    = mysql_real_escape_string(trim($data[5]));

	$sql  = "INSERT INTO events (ConventionID, Title, Description, StartDate, Location)";
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
				$sql = "INSERT INTO linkeventsguests (EventID, GuestID) VALUES ($newEventId, $guestId)";
				mysql_query($sql) or die("Something went wrong connecting a guest ($g), around row $i");
				echo "Connected guest $g.<br />";
			} else {
				// create new guest, and connect them to event
				if(trim($g) == "") {
					echo "<h3>No guests for this event.</h3>";
				} else {
					$gNameSplit = explode(" ",$g);
					$gNameCount = count($gNameSplit);
					if($gNameCount == 2) {
						$sql = "INSERT INTO guests (FirstName, LastName) VALUES ('".$gNameSplit[0]."','".$gNameSplit[1]."')";
					} elseif($gNameCount == 3) {
						$sql = "INSERT INTO guests (FirstName, LastName) VALUES ('".$gNameSplit[0]." ".$gNameSplit[1]."','".$gNameSplit[2]."')";
					} else {
						$sql = "INSERT INTO guests (FirstName) VALUES ('$g')";
						echo "<h3>Unusual name alert: $g</h3>";
					}
					mysql_query($sql) or die("Something went wrong inserting a new guest ($g), around row $i<br />$sql");
					$newGuestId = mysql_insert_id();
					$sql = "INSERT INTO linkeventsguests (EventID, GuestID) VALUES ($newEventId, $newGuestId)";
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

echo "<h1>Done!</h1>";

mysql_close($link);
?>
