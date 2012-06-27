<?php
require_once("./lib/limonade.php");

function configure() {
  option('base_uri','/');
}

function before($route) {
  //header("X-LIM-route-function: ".$route['callback']);
  layout('default_layout.php');
}

dispatch('/',             'login');
dispatch('/login/:error', 'login');
dispatch('/logout',       'login');
function login() {
  session_destroy();
  switch(params('error')) {
    case 'autherror':
      $error_message = "Invalid login.";
      break;
    case 'error':
      $error_message = "Please enter both a username and a password.";
    default:
      $error_message = false;
  }
  if($error_message) {
    set('error', $error_message);
  }
  return render('login.html.php',null);
}

dispatch_post('/login','login_post');
function login_post() {
  require_once("./model.php"); 
  $model = new Model();
  require_once("./_db.php");
  $model->connectDB($dbserver, $dbname, $dbuser, $dbpass);

  if(!empty($_POST['username']) && !empty($_POST['password'])) {
    $u = $_POST['username'];
    $p = md5($_POST['password']);

    $user = $model->authenticateUser($u,$p);
    if($user) { // authenticated!
      session_start();
      $_SESSION['id']    = $user['UserID'];
      $_SESSION['name']  = $user['UserName'];
      $_SESSION['email'] = $user['UserEmail'];
      set('user',$user);
      redirect_to('/admin/home');
    } else {
      redirect_to('/login/autherror');
    }
  } else {
    redirect_to('/login/error');
  }
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

dispatch('/api/:con/:action/:id', 'api');
function api() {
  require_once("./model.php");
  $model = new Model();
  require_once("./_db.php");
  $model->connectDB($dbserver, $dbname, $dbuser, $dbpass);

	// jsonp callback
	$callback = isset($_GET['callback']) ? $_GET['callback'] : false;
	$action   = params('action');
  $cid      = params('con');
  $id       = params('id');

	switch($action) {
		case 'list':
      $data = $model->getConventionsList();
      $key = "ConventionID";
			break;
    case 'event':
      $data = $model->getEvent($cid, $id);
      break;
		case 'events':
      $data = $model->getEvents($cid);
      $key = "EventID";
			break;
		case 'guest':
			$data = $model->getGuest($cid, $id);
			break;
		case 'guests':
      $data = $model->getGuests($cid);
			$key = "GuestID";
			break;
		default:
			die("Invalid action.");
			break;
	}

  // Construct final object
  $count = count($data);
  if($data && isset($key)) { // key-based collection
    $output = array();
    foreach($data as $d) {
      // Each JSON object's key should be the row ID
      $output[ $d[$key] ] = $d;
    }
    $json_resp = json_encode(array(
      "count"=>$count,
      "items"=>$output
    ));
  } elseif(!isset($key) && $data) { // single item
    $json_resp = json_encode($data);
  } else {
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
  $model->closeDB();
  die();
} 

dispatch('/admin/home', 'adminHome');
function adminHome() {

  // Authenticate
  if(!isset($_SESSION['id'])) {
    die("Access denied.");
  } else {
    require_once("./model.php");
    $model = new Model();
    require_once("./_db.php");
    $model->connectDB($dbserver, $dbname, $dbuser, $dbpass);

    $uid = $_SESSION['id'];
    $cons = $model->getConventions();
    $cons_for_user = $model->getConventionsAccessListForUser($uid);

    $output = array();
    foreach($cons as $c) {
      if(in_array($c['ConventionID'], $cons_for_user)) {
        array_push($output,$c);
      }
    }

    set('data', $output);
    set('user', $_SESSION);

    $model->closeDB();

    return render('home.html.php');
  } 
}

dispatch('/admin/:con/:action/:id', 'adminAction');
function adminAction() {

  // Authenticate
  if(!isset($_SESSION['id'])) {
    die("Access denied.");
  } else {

    require_once("./model.php");
    $model = new Model();
    require_once("./_db.php");
    $model->connectDB($dbserver, $dbname, $dbuser, $dbpass); 

    $action = params('action');
    $cid    = params('con');
    $id     = params('id');

    switch($action) {
      case 'event':
        $data = $model->getEvent($cid, $id);
        break;
      case 'events':
        $data = $model->getEvents($cid);
        foreach($data as $d) {
          $d["Guests"] = $model->getGuestsForEvent($d["EventID"]);
        }
        break;
      case 'guest':
        $data = $model->getGuest($cid, $id);
        break;
      case 'guests':
        $data = $model->getGuests($cid);
        break;
      default:
        die("Invalid action.");
        break;
    }

    set('data',$data); // data for table grid
    set('convention', $model->getConvention($cid));
    set('user', $_SESSION);

    $model->closeDB();

    return render($action.'.html.php');
  }
}

run();
?>
