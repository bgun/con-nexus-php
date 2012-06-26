<?php
require_once("./lib/limonade.php");

function before($route) {
  header("X-LIM-route-function: ".$route['callback']);
  layout('default_layout.php');
}

dispatch('/', 'hello_world');
function hello_world() {
  $greeting = array(
    "greeting"=>"Hello, Con-Nexus!"
  );
  set('greeting',$greeting);
	return render();
}

dispatch('/feedback', 'feedback');
function feedback() {
	// connect to db
	require_once("./_db.php");
	$link = mysql_connect($dbserver, $dbuser, $dbpass) or die('Cannot connect to the DB');
	mysql_select_db($dbname,$link) or die('Cannot select the DB: '.mysql_error());
	
	$callback = isset($_GET['callback']) ? $_GET['callback'] : false;	
	$error   = '';
	$content = '';
	$meta    = '';
	if(isset($_GET['content'])) {
		$content = mysql_real_escape_string($_GET['content']);
	} else {
		$error = 'Must type something!';
	}
	
	if(isset($_GET['meta'])) {
		$meta = mysql_real_escape_string($_GET['meta']);
	} else {
		$error = 'There was an error submitting feedback. Sorry!';
	}
	$submitdate = date('Y-m-d H:i:s');
	
	$sql = "INSERT INTO feedback (Content, Meta, SubmitDate) VALUES ('$content', '$meta', '$submitdate')";
	if(!mysql_query($sql)) {
		$error = mysql_error();
		//$error = 'There was an database error submitting feedback. Sorry!';
	}
	
	$output = array(
		"status" => $error == '' ? "OK" : "error",
		"error"  => $error
	);

	if($callback) {
		header('Content-type: text/javascript');
		echo $callback . '(' . json_encode($output) . ')';
	} else {
		header('Content-type: application/json');
		echo json_encode($output);
	}
	
	mysql_close($link);
	die();
}

dispatch('/:app/:con/:action/:id', 'app');
function app() {
	// connect to db
	require_once("./_db.php");
	$link = mysql_connect($dbserver, $dbuser, $dbpass) or die('Cannot connect to the DB!');
	mysql_select_db($dbname,$link) or die('Cannot select the DB: '.mysql_error());

	// jsonp callback
	$callback = isset($_GET['callback']) ? $_GET['callback'] : false;
	$limit    = isset($_GET['limit']) ? intval($_GET['limit']) : -1;

	$action   = params('action');
  $app      = params('app');
  $cid      = params('con');
  $id       = params('id');

  $method   = $_SERVER['REQUEST_METHOD'];

  // TODO: ability to return a single convention
  if($cid > 0 && $action === null) {
    $action = "list";
  }

	switch($action) {
		case 'list':
			$query  = "SELECT ConventionID, Name, StartDate, UNIX_TIMESTAMP(StartDate) AS StartDateUT";
			$query .= ",EndDate, UNIX_TIMESTAMP(EndDate) AS EndDateUT, Description, Location, Website, Twitter";
			$query .= ",UNIX_TIMESTAMP(UpdateDate) AS UpdateTimestamp";
			$query .= " FROM conventions";
			$key = "ConventionID";
			break;
    case 'event':
 			if($cid < 0) die("Must provide a convention ID");
      $query  = "SELECT E.EventID, E.Title, E.StartDate, E.EndDate, E.Description, E.Location";
      $query .= ",GROUP_CONCAT(LEG.GuestID) AS GuestList";
      $query .= " FROM events E";
      $query .= " LEFT JOIN conventions C ON (C.ConventionID = E.ConventionID)";
      $query .= " LEFT JOIN linkeventsguests LEG ON(LEG.EventID = E.EventID)";
      $query .= " WHERE E.ConventionID = $cid AND E.EventID = $id";
      $query .= " GROUP BY EventID";
      $key = "EventID";
     break;
		case 'events':
			if($cid < 0) die("Must provide a convention ID");
			$query  = "SELECT E.EventID, E.Title, E.StartDate, E.EndDate, E.Description, E.Location";
			$query .= ",GROUP_CONCAT(LEG.GuestID) AS GuestList";
			$query .= " FROM events E";
			$query .= " LEFT JOIN conventions C ON (C.ConventionID = E.ConventionID)";
			$query .= " LEFT JOIN linkeventsguests LEG ON(LEG.EventID = E.EventID)";
			$query .= " WHERE E.ConventionID = $cid";
			$query .= " GROUP BY EventID";
			$key = "EventID";
			break;
		case 'guests':
			if($cid < 0) die("Must provide a convention ID");
			$query  = "SELECT DISTINCT G.GuestID, G.FirstName, G.LastName, G.Bio, G.Website, G.GuestID, LC.ConventionRole, LC.ConventionID";
			$query .= ",GROUP_CONCAT(DISTINCT LEG.EventID) AS EventList";
			$query .= " FROM guests G";
			$query .= " LEFT JOIN linkeventsguests L ON G.GuestID = L.GuestID";
			$query .= " LEFT JOIN events E ON E.EventID = L.EventID";
			$query .= " LEFT JOIN conventions C ON C.ConventionID = E.ConventionID";
			$query .= " LEFT JOIN linkconventionsguests LC ON LC.ConventionID = C.ConventionID";
			$query .= " LEFT JOIN linkeventsguests LEG ON G.GuestID = LEG.GuestID";
			$query .= " WHERE C.ConventionID = $cid";
			$query .= " GROUP BY GuestID";
			$key = "GuestID";
			break;
		default:
			die("Invalid action.");
			break;
	}
	if($limit > 0) $query .= " LIMIT $limit";
	$data = mysql_query($query,$link) or die('Errant query:  '.$query.'<br /><br />'.mysql_error());

  // This is a JSON API call.
  if($app === "api") {
    // Construct final object
    $output = array();
    if(mysql_num_rows($data) > 1) { // respond with collection 
      while($o = mysql_fetch_assoc($data)) {
        // Each JSON object's key should be the row ID
        $output[ $o[$key] ] = $o;
      }
      $count = count($output);
       $json_resp = json_encode(array(
        "count"=>$count,
        "items"=>$output
      ));
    } elseif(mysql_num_rows($data) === 1) { // respond with single object
      $output = mysql_fetch_assoc($data);
      $json_resp = json_encode($output);
    } else { // oops
      $json_resp = json_encode(array(
        "error"=>"No results found."
      ));
    }

    // Callback for JSONP
    if($callback) {
      header('Content-type: text/javascript');
      echo $callback . '('.$json_resp.');';
    } else {
      header('Content-type: application/json');
      echo $json_resp;
    }

    // Cleanup
  	mysql_close($link);
	  die();
  }

  // else this is an admin page  
  $output = array();
  if(mysql_num_rows($data) > 1) { 
    while($o = mysql_fetch_assoc($data)) {
      array_push($output, $o);
    }
  }
  set('data',$output); // data for table grid

  // find our convention
  $query  = "SELECT ConventionID, Name, StartDate, UNIX_TIMESTAMP(StartDate) AS StartDateUT";
  $query .= ",EndDate, UNIX_TIMESTAMP(EndDate) AS EndDateUT, Description, Location, Website, Twitter";
  $query .= ",UNIX_TIMESTAMP(UpdateDate) AS UpdateTimestamp";
  $query .= " FROM Conventions WHERE ConventionID = $cid";
  $tmpdata = mysql_query($query,$link) or die('Errant query:  '.$query.'<br /><br />'.mysql_error());
  if(mysql_num_rows($tmpdata) > 0) {
    $con_data = mysql_fetch_assoc($tmpdata);
  } else {
    die('Convention not found');
  }
  set('convention',$con_data);

  return render($action.'.html.php');
  mysql_close($link);
}

run();
?>
