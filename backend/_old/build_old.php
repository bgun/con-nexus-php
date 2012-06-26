<?php

if(isset($_GET["file"])) {
	$outputFile = $_GET["file"];
} else {
	$outputFile = "output.html";
}
ob_start();

// get querystring values
$cid  = intval($_GET['cid']);

// connect to db
include("./_db.php");
$link = mysql_connect($dbserver, $dbuser, $dbpass) or die('Cannot connect to the DB');

mysql_select_db('connexus',$link) or die('Cannot select the DB');

// get convention info
$query = "SELECT ConventionID, Name, StartDate, EndDate, Location, Website, Description, Twitter, Tagline, Icon, Map FROM Conventions WHERE ConventionID = ".$cid." ORDER BY StartDate";
$result = mysql_query($query,$link) or die('Errant query:  '.$query);
$conventions = array();
if(mysql_num_rows($result)) { 
	while($temprow = mysql_fetch_assoc($result)) { 
		$conventions[] = $temprow;
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"> 
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $conventions[0]["Name"]; ?> | Con-Nexus</title>
	<link rel="stylesheet" type="text/css" href="./css/jcon2012.min.css" />
	<link rel="stylesheet" type="text/css" href="./css/jquery.mobile.structure-1.1.0-rc.1.min.css" />
	<link rel="stylesheet" type="text/css" href="./css/con-nexus.css" />
	
	<link rel="apple-touch-icon" href="<?php echo $conventions[0]["Icon"];?>" />
	<link rel="shortcut icon"    href="<?php echo $conventions[0]["Icon"];?>" />
	
	<script src="./js/jquery-1.7.1.min.js"></script>
	<script src="./js/jquery.mobile-1.1.0-rc.1.min.js"></script>
	<script src="./js/jquery.tweet.js"></script>
	<script src="./js/con-nexus.js"></script>
	<script>
		var Details = {
			cid: '<?php echo $conventions[0]["ConventionID"]; ?>',
			name: '<?php echo $conventions[0]["Name"]; ?>'
		};
	</script>
</head>
<body>


<!-- start dashboard -->
<div data-role="page" id="dashboard" class="page">
	<div data-role="content">
		<img src="./images/cons/jcon2011-header.png" class="convention-header" />
		<div class="control-main ui-grid-b">
			<div class="ui-block-a btn-schedule"><a href="#schedule-0" data-role="button"><img alt="Schedule" src="./images/icon-schedule.png" /><h4>Schedule</h4></a></div>
			<div class="ui-block-b btn-guests"  ><a href="#guests"     data-role="button"><img alt="Guests"   src="./images/icon-guests.png"   /><h4>Guests</h4></a></div>
			<div class="ui-block-c btn-feedback"><a href="#feedback"   data-role="button" class="dashboard feedback-link"><img alt="Feedback" src="./images/icon-feedback.png" /><h4>Feedback</h4></a></div>
		</div>
		<div class="control-main ui-grid-b">
			<div class="ui-block-a btn-map"     ><a href="#map"    	   data-role="button"><img alt="Map"      src="./images/icon-map.png"     /><h4>Map</h4></a></div>
			<div class="ui-block-b btn-todo"    ><a href="#todo"       data-role="button"><img alt="My ToDo"  src="./images/icon-todo.png"    /><h4>My ToDo</h4></a></div>
			<div class="ui-block-c btn-twitter" ><a href="#twitter"    data-role="button"><img alt="Twitter"  src="./images/icon-twitter.png" /><h4>Twitter</h4></a></div>
		</div>
		<a href="#info" class="about-link" data-role="button">About <?php echo $conventions[0]["Name"]; ?></a>
	</div><!-- end content -->
</div><!-- end page -->
<!-- end dashboard -->


<!-- start about -->
<div data-role="page" id="info" class="page" data-add-back-btn="true">
	<div data-role="header" data-position="fixed">
		<h1><?php echo $conventions[0]["Name"]; ?></h1>
	</div>
	<div data-role="content">
		<div data-role="collapsible-set">
			<div data-role="collapsible" data-collapsed="false">
				<h3>About <?php echo $conventions[0]["Name"]; ?></h3>
				<p><?php echo $conventions[0]["Description"]; ?></p>
			</div>
			<div data-role="collapsible" data-collapsed="true">
				<img src="images/con-nexus-logo.png" />
				<h3>About This App</h3>
				<p>
					Con-Nexus is a web service created to generate custom mobile applications for conventions and conferences. It's still very much in the alpha stage, and I'd love to have your feedback!
				</p>
				<a href="mailto:ben@bengundersen.com" data-role="button" data-theme="a">ben@bengundersen.com</a>
			</div>
		</div>
	</div>
</div>
<!-- end about -->


<!-- start schedule -->
<?php
$query = "SELECT EventID, Title, StartDate, EndDate, Description, Location FROM Events WHERE ConventionID = ".$cid." ORDER BY StartDate";
$result = mysql_query($query,$link) or die('Errant query:  '.$query);
$events = array();
if(mysql_num_rows($result)) {
	while($temprow = mysql_fetch_assoc($result)) {
		$events[] = $temprow;
	}
}
$output = "";
$numdays = 0;
$lastday = '';
foreach($events as $e) {
	$timestamp = strtotime( $e["StartDate"] );
	$daystr  = date('Ymd',   $timestamp);
	if( $lastday != $daystr ) {
		$numdays++;
		$lastday = $daystr;
	}
}
$numdays -= 1;
$dayindex = 0;
$lastday = '';
reset($events);
foreach($events as $e) {
	$timestamp = strtotime( $e["StartDate"] );
	$daystr  = date('Ymd',   $timestamp);
	$timestr = date('g:i A', $timestamp);
	if( $lastday != $daystr ) {
		$headertitle = date("l", $timestamp);
		if($dayindex > 0) { echo '</ul></div></div>'; }
		?>
			<div data-role="page" class="schedule" id="schedule-<?php echo $dayindex; ?>">
				<div data-role="header" data-position="fixed">
					<div data-role="navbar">
						<ul>
							<li><a href="#dashboard"  data-icon="home" data-iconpos="top">Home</a></li>
							<li><a href="#schedule-0" data-theme="d" data-icon="grid" data-iconpos="top">Schedule</a></li>
							<li><a href="#guests"     data-icon="star" data-iconpos="top">Guests</a></li>
							<li><a href="#todo"       data-icon="check" data-iconpos="top">My ToDo</a></li>
						</ul>
					</div>
					<?php if($dayindex > 0) { ?>
					<a href="#schedule-<?php echo $dayindex-1; ?>" class="schedule-nav schedule-nav-prev ui-btn-left"  data-role="button" data-transition="slide" data-direction="reverse" data-icon="arrow-l" data-iconpos="notext" data-direction="reverse">Prev</a>
					<?php } ?>
					
					<h1><?php echo $headertitle; ?></h1>
					
					<?php if($dayindex < $numdays) { ?>
					<a href="#schedule-<?php echo $dayindex+1; ?>" class="schedule-nav schedule-nav-next ui-btn-right"  data-role="button" data-transition="slide" data-icon="arrow-r" data-iconpos="notext">Next</a>
					<?php } ?>
				</div>
				<div data-role="content">
					<ul data-role="listview">
		<?php
		$dayindex++;
		$lastday = $daystr;
	}
	if( !isset($lasttime) || $lasttime != $timestr ) {
		echo '<li data-role="list-divider">'.$timestr.'</li>';
		$lasttime = $timestr;
	}
	echo '<li><a href="#event-detail-'.$e["EventID"].'">'.$e["Title"].'<br /><small>in '.$e["Location"].'</small></a></li>';
}
?>
		</ul>
	</div>
</div>
<!-- end schedule -->


<!-- start guests -->
<?php
$query  = "SELECT DISTINCT G.FirstName, G.LastName, G.Bio, G.Website, G.GuestID, LC.ConventionRole FROM Guests G";
$query .= " LEFT JOIN LinkEventsGuests L ON G.GuestID = L.GuestID";
$query .= " LEFT JOIN Events E ON E.EventID = L.EventID";
$query .= " LEFT JOIN Conventions C ON C.ConventionID = E.ConventionID";
$query .= " LEFT JOIN LinkConventionsGuests LC ON LC.ConventionID = C.ConventionID";
$query .= " WHERE E.ConventionID = $cid ORDER BY FirstName";
$result = mysql_query($query,$link) or die('Errant query:  '.$query);
$guests = array();
if(mysql_num_rows($result)) { 
	while($temprow = mysql_fetch_assoc($result)) { 
		$guests[] = $temprow;
	} 
} 
?>
<div data-role="page" id="guests" class="page">
	<div data-role="header" data-position="fixed">
		<div data-role="navbar">
			<ul>
				<li><a href="#dashboard"  data-icon="home" data-iconpos="top">Home</a></li>
				<li><a href="#schedule-0" data-icon="grid" data-iconpos="top">Schedule</a></li>
				<li><a href="#guests"     data-theme="d" data-icon="star" data-iconpos="top">Guests</a></li>
				<li><a href="#todo"       data-icon="check" data-iconpos="top">My ToDo</a></li>
			</ul>
		</div>
		<h1>Guests</h1>
	</div>
	<div data-role="content">
		<ul data-role="listview">
		<?php
		$x = 0;
		foreach($guests as $g) {
			echo '<li><a href="#guest-detail-'.$guests[$x]["GuestID"].'">'.$guests[$x]["FirstName"].' '.$guests[$x]["LastName"];
			$role = $guests[$x]["ConventionRole"];
			$rolecss = strtolower(str_replace(' ', '', $role));
			if( $role > '') {
				echo ' <small>(<span class="role-'.$rolecss.'">'.$role.'</span>)</small></a>';
			}  else {
				echo '</a>';
			}
			echo '</li>';
			$x++;
		}
		?>
		</ul>
	</div>
</div>
<!-- end guests -->



<!-- start guest-detail -->
<?php
$query  = "SELECT G.GuestID, G.FirstName, G.LastName, G.Bio, G.Website, E.Title, E.StartDate, E.Location, E.EventID, LC.ConventionRole FROM Guests G";
$query .= " LEFT JOIN LinkEventsGuests L ON G.GuestID = L.GuestID";
$query .= " LEFT JOIN Events E ON E.EventID = L.EventID";
$query .= " LEFT JOIN LinkConventionsGuests LC ON LC.GuestID = G.GuestID";
$query .= " WHERE E.ConventionID = $cid";
$query .= " ORDER BY G.GuestID, E.StartDate";
$result = mysql_query($query,$link) or die('Errant query:  '.$query.'<br /><br />'.mysql_error());
$guestdetails = array();
if(mysql_num_rows($result)) { 
	while($temprow = mysql_fetch_assoc($result)) { 
		$guestdetails[] = $temprow;
	} 
} 
$lastgid = 0;
$x = 0;
foreach($guestdetails as $g) {	
	// open a new guest-detail page
	if($lastgid != $g["GuestID"]) {
		// close the previous one (except the first time through)
		if($x != 0) { echo "</ul></div></div>"; }
		?>
		<div data-role="page" id="guest-detail-<?php echo $g["GuestID"]; ?>" class="page">
			<div data-role="header" data-position="fixed">
				<a href="#" class="ui-btn-left"   data-role="button" data-icon="arrow-l" onclick="history.go(-1);">Back</a>
				<a href="#dashboard" class="ui-btn-right"  data-role="button" data-icon="home" data-iconpos="notext">Home</a>
				<h1>Guest Detail</h1>
			</div>
			<div data-role="content">
				<h3><?php echo $g["FirstName"].' '.$g["LastName"]; ?></h3>
				<p>
				<?php
					if(isset($guests[$x]["ConventionRole"])) {
						$role = $guests[$x]["ConventionRole"];
						$rolecss = strtolower(str_replace(' ', '', $role));
						if( $role > '') {
							echo ' (<span class="role-'.$rolecss.'">'.$role.'</span>)';
						}
					}
				?>
				</p>
				<p><?php echo $g["Bio"]; ?></p>
				<ul data-role="listview" data-inset="true">
					<li data-role="list-divider">Itinerary</li>
		<?php
	}
	
	// itinerary
	$datestr = date("l M-d g:i A", strtotime( $g["StartDate"]) );
	echo '<li><a href="#event-detail-'.$g["EventID"].'">'.$g["Title"].'<br /><small>'.$datestr.' in '.$g["Location"].'</small></a></li>';

	$lastgid = $g["GuestID"];
	$x++;
}
?>
</ul></div></div>
<!-- end guest-detail -->



<!-- start event-detail -->
<?php
$query  = "SELECT E.EventID, E.Title, E.StartDate, E.EndDate, E.Description, E.Location, G.FirstName, G.LastName, G.GuestID FROM Events E";
$query .= " LEFT JOIN LinkEventsGuests L ON L.EventID = E.EventID";
$query .= " LEFT JOIN Guests G ON G.GuestID = L.GuestID";
$query .= " WHERE E.ConventionID = $cid";
$query .= " ORDER BY E.EventID, G.FirstName";
$result = mysql_query($query,$link) or die('Errant query:  '.$query);
$eventdetails = array();
if(mysql_num_rows($result)) {
	while($temprow = mysql_fetch_assoc($result)) {
		$eventdetails[] = $temprow;
	}
}
$lasteid = 0;
$x = 0;
foreach($eventdetails as $e) {
	// open a new event-detail page
	if($lasteid != $e["EventID"]) {
		// close the previous one (except the first time through)
		if($x != 0) { echo '</ul><a href="#feedback" class="feedback-link" data-role="button">Feedback</a></div></div>'; }
		?>
		<div data-role="page" id="event-detail-<?php echo $e["EventID"]; ?>" class="page">
			<div data-role="header" data-position="fixed">
				<a href="#" class="ui-btn-left"   data-role="button" data-icon="arrow-l" onclick="history.go(-1);">Back</a>
				<a href="#dashboard" class="ui-btn-right"  data-role="button" data-icon="home" data-iconpos="notext">Home</a>
				<h1>Event Detail</h1>
			</div>
			<div data-role="content">
				<h3><?php echo $e["Title"]; ?></h3>
				<h5>
					<?php
						$datestr = date("l, F d, g:i A", strtotime( $e["StartDate"]) );
						echo $datestr.' in '.$e["Location"];
					?>
				</h5>
				<p><?php echo $e["Description"]; ?></p>
				<a href="#" class="todo-add" title="<?php echo $e["EventID"]; ?>" data-role="button" data-icon="plus" data-theme="a">Add to My ToDo</a>
				<ul data-role="listview" data-inset="true">
					<li data-role="list-divider">Participants</li>
		<?php
	}
	
	// participants
	echo '<li><a href="#guest-detail-'.$e["GuestID"].'">'.$e["FirstName"].' '.$e["LastName"].'</a></li>';

	$lasteid = $e["EventID"];
	$x++;
}
?>
</ul>
<a href="#feedback" data-role="button">Feedback</a>
</div>
</div>
<!-- end event-detail -->



<!-- start todo -->
<?php
$query  = "SELECT Title, StartDate, Location, EventID, ConventionID FROM Events";
$query .= " WHERE ConventionID = ".$cid." ORDER BY StartDate";
$result = mysql_query($query,$link) or die('Errant query:  '.$query);
$todo = array();
if(mysql_num_rows($result)) { 
	while($temprow = mysql_fetch_assoc($result)) { 
		$todo[] = $temprow;
	} 
} 
?>
<div data-role="page" id="todo" class="page">
	<div data-role="header" data-position="fixed">
		<div data-role="navbar">
			<ul>
				<li><a href="#dashboard"  data-icon="home" data-iconpos="top">Home</a></li>
				<li><a href="#schedule-0" data-icon="grid" data-iconpos="top">Schedule</a></li>
				<li><a href="#guests"     data-icon="star" data-iconpos="top">Guests</a></li>
				<li><a href="#todo"       data-theme="d" data-icon="check" data-iconpos="top">My ToDo</a></li>
			</ul>
		</div>
		<h1>My ToDo List</h1>
	</div>
	<div data-role="content">
		<ul data-role="listview" data-inset="true" data-split-icon="delete" data-split-theme="a">
		<?php
			$output ="";
			$lastdaysep = "";
			foreach($todo as $t) {
				$timestamp = strtotime( $t["StartDate"] );
				$daysep = date("l", $timestamp);
				if( $lastdaysep != $daysep ) {
					$output .= '<li data-role="list-divider">'.$daysep.'</li>';
					$lastdaysep = $daysep;
				}
				$datestr = date("g:i A", strtotime( $t["StartDate"]) );
				$output .= '<li class="hide todo todo-'.$t["EventID"].'"><a href="#event-detail-'.$t["EventID"].'">'.$t["Title"].'<br />';
				$output .= '<small>'.$datestr.' in '.$t["Location"].'</small></a>';
				$output .= '<a class="todo-remove" href="#" data-eventid="'.$t["EventID"].'"></a></li>';
			}
			echo $output;
		?>
		</ul>
		<a href="#" class="todo-clear" data-role="button">Remove All</a>
	</div>
</div>
<!-- end todo -->



<!-- start feedback -->
<div data-role="page" id="feedback" class="page">
	<div data-role="header" data-position="fixed">
		<a href="#" class="ui-btn-left"   data-role="button" data-icon="arrow-l" onclick="history.go(-1);">Back</a>
		<a href="#dashboard" class="ui-btn-right"  data-role="button" data-icon="home" data-iconpos="notext">Home</a>
		<h1>Feedback</h1>
	</div>
	<div data-role="content">
		<h3>General Feedback for <?php echo $conventions[0]["Name"]; ?></h3>
		<p>
			Please enter your comments below. All feedback is anonymous; if you'd like to be contacted regarding your comment or question, please leave a name and email address. Thanks, we appreciate any and all feedback!
		</p>
		<form id="feedback-form">
			<textarea class="content" name="content"></textarea>
			<input    class="meta"    name="meta" type="hidden" value="Test" />
			<a href="" data-role="button">Submit Feedback</a>
		</form>
	</div>
</div>
<!-- end feedback -->



<!-- start map -->
<div data-role="page" id="map" class="page">
	<div data-role="header" data-position="fixed">
		<a href="#" class="ui-btn-left"   		   data-role="button" data-icon="arrow-l" onclick="history.go(-1);">Back</a>
		<a href="#dashboard" class="ui-btn-right"  data-role="button" data-icon="home" data-iconpos="notext">Home</a>
		<h1>Map</h1>
	</div>
	<?php if($conventions[0]["Map"] <= '') { ?>
	<div data-role="content">
		<p>No map is available for this event.</p>
		<a href="" data-role="button" onclick="history.go(-1);">Back</a>
	</div>
	<?php } else { ?>
	<div data-role="content" style="overflow-x: scroll; padding: 0;">
		<div id="map-controls">
			<a href="" id="map-zoom-in"></a>
			<a href="" id="map-zoom-out"></a>
		</div>
		<div id="map-container">
			<img alt="Map" id="map-image" src="<?php echo $conventions[0]["Map"]; ?>" />
		</div>
	</div>
	<?php } ?>
	</div>
</div>
<!-- end map -->



<!-- start twitter -->
<div data-role="page" id="twitter" class="page" data-add-back-btn="true">
	<div data-role="header" data-position="fixed">
		<a href="#" class="ui-btn-left"   data-role="button" data-icon="arrow-l" onclick="history.go(-1);">Back</a>
		<a href="#dashboard" class="ui-btn-right"  data-role="button" data-icon="home" data-iconpos="notext">Home</a>
		<h1>Tweets</h1>
	</div>
	<div data-role="content" style="padding: 10px;">
		<h3>Recent Twitter Activity about <?php echo $conventions[0]["Name"]; ?></h3>
		<div id="list-tweets">
		</div>
	</div>
</div>
<!-- end twitter -->

</body>
</html>

<!-- clean up -->
<?php
	mysql_close($link);
	
	if(isset($_GET["file"])) {
		$outputHtml = ob_get_contents(); 
		$fh = fopen($outputFile, 'w') or die("can't open file $outputFile");
		fwrite($fh, $outputHtml);
		fclose($fh);
		ob_end_clean();
		echo "Build saved to $outputFile.";
	}
?>
