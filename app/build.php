<?php

if(isset($_GET["key"]) && isset($_GET["cid"])) {
  $cid  = intval($_GET['cid']);
  $key = $_GET["key"];
	$outputFile = $key.".html";
} else {
  die("Key and CID required to build.");
}

ob_start();

// connect to db
include("_db.php");
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
	<title>Con-Nexus Events</title>
	<link rel="stylesheet" type="text/css" href="./css/".$key."_jqm.min.css" />
  <link rel="stylesheet" type="text/css" href="./css/".$key."_. />
	<link rel="stylesheet" type="text/css" href="./css/jquery.mobile.structure-1.1.0-rc.1.min.css" />
	<link rel="stylesheet" type="text/css" href="./css/con-nexus.css" />
	
	<link rel="apple-touch-icon" href=".<?php echo $conventions[0]["Icon"];?>" />
	<link rel="shortcut icon"    href=".<?php echo $conventions[0]["Icon"];?>" />
	
	<script src="./js/jquery-1.7.1.min.js"></script>
	<script src="./js/underscore-min.js"></script>
	<script src="./js/jsrender.js"></script>
	<script src="./js/jquery.tweet.js"></script>
	<script src="./js/con-nexus.js"></script>
	<script src="./js/jquery.mobile-1.1.0.min.js"></script>
	
	<script src="./js/data/data-events.js"></script>
	<script src="./js/data/data-guests.js"></script>
	
	<script>
		var Convention = {
			ConventionID: '<?php echo $conventions[0]["ConventionID"]; ?>',
			Name: '<?php echo $conventions[0]["Name"]; ?>'
		};
	</script>
</head>
<body>


<!-- start dashboard -->
<div data-role="page" id="dashboard" class="page">
	<div data-role="content">
		<img src="./images/cons/jcon2011-header.png" class="convention-header" />
		<div class="control-main ui-grid-b">
			<div class="ui-block-a btn-schedule"><a href="#schedule-pg1" data-role="button"><img alt="Schedule" src="./images/icon-schedule.png" /><h4>Schedule</h4></a></div>
			<div class="ui-block-b btn-guests"  ><a href="#guests"       data-role="button"><img alt="Guests"   src="./images/icon-guests.png"   /><h4>Guests</h4></a></div>
			<div class="ui-block-c btn-feedback"><a href="#feedback"     data-role="button" class="dashboard feedback-link"><img alt="Feedback" src="./images/icon-feedback.png" /><h4>Feedback</h4></a></div>
		</div>
		<div class="control-main ui-grid-b">
			<div class="ui-block-a btn-map"     ><a href="#map"    	     data-role="button"><img alt="Map" src="./images/icon-map.png"           /><h4>Map</h4></a></div>
			<div class="ui-block-b btn-todo"    ><a href="#todo"         data-role="button" class="todo-link"><img alt="My ToDo"  src="./images/icon-todo.png"     /><h4>My ToDo</h4></a></div>
			<div class="ui-block-c btn-twitter" ><a href="#twitter"      data-role="button"><img alt="Twitter"  src="./images/icon-twitter.png"  /><h4>Twitter</h4></a></div>
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
<script id="schedule-template" type="x-jquery-tmpl">
<div data-role="page" class="schedule" id="schedule-pg{{:index}}">
	<div data-role="header" data-position="fixed">
		<div data-role="navbar">
			<ul>
				<li><a href="#dashboard"              data-icon="home"  data-iconpos="top">Home</a></li>
				<li><a href="#schedule-pg{{:index}}"  data-icon="grid"  data-iconpos="top" data-theme="d">Schedule</a></li>
				<li><a href="#guests"                 data-icon="star"  data-iconpos="top">Guests</a></li>
				<li><a href="#todo" class="todo-link" data-icon="check" data-iconpos="top">My ToDo</a></li>
			</ul>
		</div>
		{{if previndex}}
		<a href="#schedule-pg{{:previndex}}" class="schedule-nav schedule-nav-prev ui-btn-left"  data-role="button" data-transition="slide" data-direction="reverse" data-icon="arrow-l" data-iconpos="notext" data-direction="reverse">Prev</a>		
		{{/if}}
		<h1>{{:dayofweek}}</h1>
		{{if nextindex}}
		<a href="#schedule-pg{{:nextindex}}" class="schedule-nav schedule-nav-next ui-btn-right"  data-role="button" data-transition="slide" data-icon="arrow-r" data-iconpos="notext">Next</a>
		{{/if}}
	</div>
	<div data-role="content">
		<ul data-role="listview">
		{{for events}}
			{{if divider}}
			<li data-role="list-divider">{{:time}}</li>
			{{else}}
			<li><a href="#event-detail" class="event-detail-link" data-eventid="{{:EventID}}">{{:Title}}<br /><small>in {{:Location}}</small></a></li>
			{{/if}}
		{{/for}}
		</ul>
	</div>
</div>
</script>
<!-- end schedule -->


<!-- start guests -->
<div data-role="page" id="guests" class="page">
	<div data-role="header" data-position="fixed" data-backbtn="false">
		<div data-role="navbar">
			<ul>
				<li><a href="#dashboard"               data-icon="home" data-iconpos="top">Home</a></li>
				<li><a href="#schedule-pg1"            data-icon="grid" data-iconpos="top">Schedule</a></li>
				<li><a href="#guests"                  data-theme="d" data-icon="star" data-iconpos="top">Guests</a></li>
				<li><a href="#todo"  class="todo-link" data-icon="check" data-iconpos="top">My ToDo</a></li>
			</ul>
		</div>
		<h1>Guests</h1>
	</div>
	<div data-role="content">
		<ul class="guests-list" data-role="listview">
		</ul>
	</div>
</div>
<script id="guests-template" type="x-jquery-tmpl">
	<li><a href="#guest-detail" class="guest-detail-link" data-guestid="{{:GuestID}}">{{:FirstName}} {{:LastName}} <span>{{:ConventionRole}}</span></a></li>
</script>
<!-- end guests -->



<!-- start guest-detail -->
<script id="guest-detail-template" type="x-jquery-tmpl">
<div data-role="page" id="guest-detail" class="page">
	<div data-role="header" data-position="fixed">
		<a href="#" class="ui-btn-left"   data-role="button" data-icon="arrow-l" onclick="history.go(-1);">Back</a>
		<a href="#dashboard" class="ui-btn-right"  data-role="button" data-icon="home" data-iconpos="notext">Home</a>
		<h1>Guest Detail</h1>
	</div>
	<div id="guest-detail-content" data-role="content">
		<h3>{{:FirstName}} {{:LastName}}</h3>
		<p>{{:ConventionRole}}</p>
		<p>{{:Bio}}</p>
		{{if GuestEvents}}
		<ul data-role="listview" data-inset="true">
			<li data-role="list-divider">Itinerary</li>
			{{for GuestEvents}}
			<li><a href="#event-detail" class="event-detail-link" data-eventid="{{:EventID}}">{{:Title}}<br /><small>{{:DayAndTime}} in {{:Location}}</small></a></li>
			{{/for}}
		</ul>
		{{/if}}
	</div>
</div>
</script>
<!-- end guest-detail -->



<!-- start event-detail -->
<script id="event-detail-template" type="x-jquery-tmpl">
<div data-role="page" id="event-detail" class="page">
	<div data-role="header" data-position="fixed">
		<a href="#" class="ui-btn-left"   data-role="button" data-icon="arrow-l" onclick="history.go(-1);">Back</a>
		<a href="#dashboard" class="ui-btn-right"  data-role="button" data-icon="home" data-iconpos="notext">Home</a>
		<h1>Event Detail</h1>
	</div>
	<div id="event-detail-content" data-role="content">
		<h3>{{:Title}}</h3>
		<h5>{{:DayAndTime}} in {{:Location}}</h5>
		<p>{{>Description}}</p>
		<a href="#" class="todo-add" data-eventid="{{:EventID}}" data-role="button" data-icon="plus" data-theme="a">Add to My ToDo</a>
		{{if EventGuests}}
		<ul data-role="listview" data-inset="true">
			<li data-role="list-divider">Participants</li>
			{{for EventGuests}}
			<li><a href="#guest-detail" class="guest-detail-link" data-guestid="{{:GuestID}}">{{:FirstName}} {{:LastName}}</a></li>
			{{/for}}
		</ul>
		{{/if}}
		<a href="#feedback" class="feedback-link" data-role="button">Feedback</a>
	</div>
</div>
</script>
<!-- end event-detail -->



<!-- start todo -->
<script id="todo-template" type="x-jquery-tmpl">
<div data-role="page" id="todo" class="page">
	<div data-role="header" data-position="fixed">
		<div data-role="navbar">
			<ul>
				<li><a href="#dashboard"              data-icon="home" data-iconpos="top">Home</a></li>
				<li><a href="#schedule-pg1"           data-icon="grid" data-iconpos="top">Schedule</a></li>
				<li><a href="#guests"                 data-icon="star" data-iconpos="top">Guests</a></li>
				<li><a href="#todo" class="todo-link" data-theme="d" data-icon="check" data-iconpos="top">My ToDo</a></li>
			</ul>
		</div>
		<h1>My ToDo List</h1>
	</div>
	<div class="todo-content" data-role="content">
		{{if items}}
		<ul class="todo-list" data-role="listview" data-inset="true" data-split-icon="delete" data-split-theme="a">
			{{for items}}
			<li class="todo-item-{{:EventID}}">
				<a href="#event-detail" class="event-detail-link" data-eventid="{{:EventID}}">{{:Title}}<br /><small>{{:DayAndTime}} in {{:Location}}</small></a>
				<a class="todo-remove" href="#" data-eventid="{{:EventID}}"></a>
			</li>
			{{/for}}
		</ul>
		<a href="#" class="todo-clear" data-role="button">Remove All</a>
		{{else}}
		<p>Your ToDo list is empty.</p>
		{{/if}}
	</div>
</div>
</script>
<!-- end todo -->



<!-- start feedback -->
<div data-role="page" id="feedback" class="page" data-add-back-btn="true">
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
			<a href="#" data-role="button">Submit Feedback</a>
		</form>
	</div>
</div>
<!-- end feedback -->



<!-- start map -->
<div data-role="page" id="map" class="page" data-add-back-btn="true">
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
		<a href="#"          class="ui-btn-left"   data-role="button" data-icon="arrow-l" onclick="history.go(-1);">Back</a>
		<a href="#dashboard" class="ui-btn-right"  data-role="button" data-icon="home" data-iconpos="notext">Home</a>
		<h1>Tweets</h1>
	</div>
	<div data-role="content" style="padding: 10px;">
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

	$outputHtml = ob_get_contents(); 
	$fh = fopen($outputFile, 'w') or die("can't open file $outputFile");
	fwrite($fh, $outputHtml);
	fclose($fh);
	ob_end_clean();
	echo "Build saved to $outputFile.";
?>
