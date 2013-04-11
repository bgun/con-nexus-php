<?php
require_once("./lib/limonade.php");

function configure() {
  option('base_uri','');
}

function before($route) {
  //header("X-LIM-route-function: ".$route['callback']);
  header('Access-Control-Allow-Origin: *');
  layout('default_layout.php');
}

dispatch('/', 'splash');
function splash() {
  return render('splash.html.php',null);
}

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
      break;
    default:
      $error_message = false;
      break;
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

  if(isset($_GET['cid'])) {
    $cid = mysql_real_escape_string($_GET['cid']);
  } else {
		$error = 'Um, we encountered a really weird problem while submitting feedback. Like, whoa.';
  }

	$submitdate = date('Y-m-d H:i:s');
	
	$sql = "INSERT INTO feedback (Content, Meta, SubmitDate, ConventionID) VALUES ('$content', '$meta', '$submitdate', $cid)";
	if(!mysql_query($sql)) {
		$error = mysql_error();
		//$error = 'There was an database error submitting feedback. Sorry!';
	}
	
	$output = array(
		"status" => $error == '' ? "OK" : "error",
		"error"  => $error
	);

  header('Content-type: application/json');
  echo json_encode($output);
	
	mysql_close($link);
	die();
}

dispatch('/api/:con/:action/:id', 'api');
function api() {
  require_once("./model.php");
  $model = new Model();
  require_once("./_db.php");
  $model->connectDB($dbserver, $dbname, $dbuser, $dbpass);

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
			$data = $model->getGuest($id);
			break;
		case 'guests':
      $data = $model->getGuests($cid);
			$key = "GuestID";
			break;
		default:
      if($cid == null || !is_numeric($cid)) {
			  die("Invalid action.");
      } else {
        $data = $model->getConvention($cid);
      }
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

  header('Content-type: application/json');
  echo $json_resp;

  // Cleanup
  $model->closeDB();
  die();
} 

dispatch_post('/api/:con/:action', 'api_insert');
function api_insert() {
  // Valid session id required to write. TODO: security for remote calls
  if(!isset($_SESSION['id'])) {
    die('{ "error": "Access denied." }');
  } else {
    require_once("./model.php");
    $model = new Model();
    require_once("./_db.php");
    $model->connectDB($dbserver, $dbname, $dbuser, $dbpass);

    $cid = params('con');

    $obj = array();
    foreach($_POST as $key => $value) {
      $obj[$key] = $value;
    }

    switch(params('action')) {
      case 'events':
        $success = $model->addNewEvent($cid, $obj);
        break;
      case 'guests':
        $success = $model->addNewGuest($cid, $obj);
        break;
      case 'addguest':
        $success = $model->connectGuestToEvent($obj);
        break;
    }

    if($success) {
      die('{"success": true}');
    } else {
      die('{"error": "Database write error."}');
    };
  }
}

dispatch_put('/api/:con/:action/:id', 'api_update');
function api_update() {
  // Valid session id required to write. TODO: security for remote calls
  if(!isset($_SESSION['id'])) {
    die('{ "error": "Access denied." }');
  } else {
    require_once("./model.php");
    $model = new Model();
    require_once("./_db.php");
    $model->connectDB($dbserver, $dbname, $dbuser, $dbpass);

    $cid    = params('con');
    $id     = params('id');
    $action = params('action');

    // All actions require an ID except updating convention data.
    if(!is_numeric($id) && $action != 'update') {
      die('{"error": "Invalid ID."}');
    }

    $obj = array();
    foreach($_POST as $key => $value) {
      $obj[$key] = $value;
    }

    switch(params('action')) {
      case 'event':
        $success = $model->updateEvent($id,$obj);
        break;
      case 'guest':
        $success = $model->updateGuest($id,$obj);
        break;
      case 'update':
        $success = $model->touchConventionUpdatedDate($cid);
        break;
    }

    if($success) {
      die(json_encode(array(
        "success"=>true
      )));
    } else {
      die('{"error": "Database write error."}');
    };
  }
}

dispatch_delete('/api/:con/:action/:id', 'api_delete');
function api_delete() {
  // Valid session id required to write. TODO: security for remote calls
  if(!isset($_SESSION['id'])) {
    die('{ "error": "Access denied." }');
  } else {
    require_once("./model.php");
    $model = new Model();
    require_once("./_db.php");
    $model->connectDB($dbserver, $dbname, $dbuser, $dbpass);

    /*
    $id = params('id');
    if(!is_numeric($id)) {
      die('{"error": "Invalid ID."}');
    }
    */
    $obj = array();
    foreach($_POST as $key => $value) {
      $obj[$key] = $value;
    }

    switch(params('action')) {
      case 'removeguest':
        $success = $model->removeGuestFromEvent($obj);
        break;
    }

    if($success) {
      die(json_encode(array(
        "success"=>true
      )));
    } else {
      die('{"error": "Database write error."}');
    };
  }
}


/* Admin pages */

dispatch('/admin/home', 'adminHome');
function adminHome() {

  // Authenticate
  require_once("./model.php");
  $model = new Model();
  require_once("./_db.php");
  $model->connectDB($dbserver, $dbname, $dbuser, $dbpass);

  // Authenticate
  if(!isset($_SESSION['id'])) {
    $model->closeDB();
    redirect_to('/login/error');
  } else {

    $uid = $_SESSION['id'];
    $cons = $model->getConventions();
    $cons_for_user = $model->getConventionsAccessListForUser($uid);

    $output = array();
    foreach($cons as $c) {
      if(in_array($c['ConventionID'], $cons_for_user)) {
        array_push($output,$c);
      }
    }

    set('convention', array(
      'ConventionID'=>0
    ));
    set('data', $output);
    set('user', $_SESSION);

    $model->closeDB();

    return render('home.html.php');
  } 
}

dispatch('/admin/:con/:action/:id', 'adminAction');
function adminAction() {

  require_once("./model.php");
  $model = new Model();
  require_once("./_db.php");
  $model->connectDB($dbserver, $dbname, $dbuser, $dbpass);

  // Authenticate
  if(!$model->userHasConventionAccess($_SESSION['id'], params('con'))) {
    $model->closeDB();
    redirect_to('/login/error');
  } else {

    $action = params('action');
    $cid    = params('con');
    $id     = params('id');

    switch($action) {
      case 'event':
        $data = $model->getEvent($cid, $id);
        break;
      case 'events':
        $data = $model->getEvents($cid);
        foreach($data as $key=>$value) {
          $gs = $model->getGuestsForEvent($data[$key]["EventID"]);
          $data[$key]["Guests"] = $gs;
        }
        break;
      case 'feedback':
        $data = $model->getFeedback($cid);
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

    set('action',     ucfirst($action));
    set('convention', $model->getConvention($cid));
    set('data',       $data); // data for table grid
    set('guests',     $model->getGuests($cid));
    set('user',       $_SESSION);

    $model->closeDB();

    return render($action.'.html.php');
  }
}

run();
?>
