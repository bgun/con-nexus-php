<?php
class Model {

  var $link;

  function connectDB($dbserver, $dbname, $dbuser, $dbpass) {
 	  $this->link = mysql_connect($dbserver, $dbuser, $dbpass) or die('Cannot connect to sthe DB!');
	  mysql_select_db($dbname,$this->link) or die('Cannot select the DB: '.mysql_error());
  }

  function closeDB() {
    mysql_close($this->link);
  }

  function authenticateUser($username, $password) { 
    $query = "SELECT UserID, UserEmail FROM users WHERE UserName = '$username' AND Password = '$password'";
    $result = mysql_query($query, $this->link);
    if(mysql_num_rows($result) === 1) {
      return mysql_fetch_assoc($result);
    } else {
      return false;
    }
  }

  function getConvention($cid) {
    $query  = "SELECT ConventionID, Name, StartDate, UNIX_TIMESTAMP(StartDate) AS StartDateUT";
    $query .= ",EndDate, UNIX_TIMESTAMP(EndDate) AS EndDateUT, Description, Location, Website, Twitter";
    $query .= ",UNIX_TIMESTAMP(UpdateDate) AS UpdateTimestamp";
    $query .= " FROM conventions WHERE ConventionID = $cid";
	  $result = mysql_query($query,$this->link) or die('Errant query:  '.$query.'<br /><br />'.mysql_error());
    if(mysql_num_rows($result) === 1) {
      return mysql_fetch_assoc($result);
    } else {
      return false;
    }
  }

  function getConventions() {
    $query  = "SELECT ConventionID, Name, StartDate, UNIX_TIMESTAMP(StartDate) AS StartDateUT";
    $query .= ",EndDate, UNIX_TIMESTAMP(EndDate) AS EndDateUT, Description, Location, Website, Twitter";
    $query .= ",UNIX_TIMESTAMP(UpdateDate) AS UpdateTimestamp";
    $query .= " FROM conventions";
	  $result = mysql_query($query,$this->link) or die('Errant query:  '.$query.'<br /><br />'.mysql_error());
    $output = array();
    if(mysql_num_rows($result) > 0) { 
      while($o = mysql_fetch_assoc($result)) {
        array_push($output, $o);
      }
      return $output;
    } else {
      return false;
    }
  }

  function getEvent($cid, $id) {
    if(!is_numeric($id) || $id < 0) die("Invalid ID. Error 1");
    $query  = "SELECT E.EventID, E.Title, E.StartDate, E.EndDate, E.Description, E.Location";
    $query .= ",GROUP_CONCAT(LEG.GuestID) AS GuestList";
    $query .= " FROM events E";
    $query .= " LEFT JOIN conventions C ON (C.ConventionID = E.ConventionID)";
    $query .= " LEFT JOIN linkeventsguests LEG ON(LEG.EventID = E.EventID)";
    $query .= " WHERE E.ConventionID = $cid AND E.EventID = $id";
    $query .= " GROUP BY EventID";
	  $result = mysql_query($query,$this->link) or die('Errant query:  '.$query.'<br /><br />'.mysql_error());
    if(mysql_num_rows($result) === 1) {
      return mysql_fetch_assoc($result);
    } else {
      return false;
    }
  }

  function getEvents($cid) {
    if(!is_numeric($cid)) die("Invalid ID. Error 0");
    $query  = "SELECT E.EventID, E.Title, E.StartDate, E.EndDate, E.Description, E.Location";
    $query .= ",GROUP_CONCAT(LEG.GuestID) AS GuestList";
    $query .= " FROM events E";
    $query .= " LEFT JOIN conventions C ON (C.ConventionID = E.ConventionID)";
    $query .= " LEFT JOIN linkeventsguests LEG ON(LEG.EventID = E.EventID)";
    $query .= " WHERE E.ConventionID = $cid";
    $query .= " GROUP BY EventID";
	  $result = mysql_query($query,$this->link) or die('Errant query:  '.$query.'<br /><br />'.mysql_error());
    $output = array();
    if(mysql_num_rows($result) > 0) { 
      while($o = mysql_fetch_assoc($result)) {
        array_push($output, $o);
      }
      return $output;
    } else {
      return false;
    }
  }

  function getGuest($cid, $id) {
    if(!is_numeric($id) || $id < 0) die("Invalid ID. Error 1");
    $query  = "SELECT DISTINCT G.GuestID, G.FirstName, G.LastName, G.Bio, G.Website, G.GuestID, LC.ConventionRole, LC.ConventionID";
    $query .= ",GROUP_CONCAT(DISTINCT LEG.EventID) AS EventList";
    $query .= " FROM guests G";
    $query .= " LEFT JOIN linkeventsguests L ON G.GuestID = L.GuestID";
    $query .= " LEFT JOIN events E ON E.EventID = L.EventID";
    $query .= " LEFT JOIN conventions C ON C.ConventionID = E.ConventionID";
    $query .= " LEFT JOIN linkconventionsguests LC ON LC.ConventionID = C.ConventionID";
    $query .= " LEFT JOIN linkeventsguests LEG ON G.GuestID = LEG.GuestID";
    $query .= " WHERE C.ConventionID = $cid AND E.EventID = $id";
    $query .= " GROUP BY GuestID";
	  $result = mysql_query($query,$this->link) or die('Errant query:  '.$query.'<br /><br />'.mysql_error());
    if(mysql_num_rows($result) === 1) {
      return mysql_fetch_assoc($result);
    } else {
      return false;
    }
  }

  function getGuests($cid) {
    if(!is_numeric($cid)) die("Invalid ID. Error 0");
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
	  $result = mysql_query($query,$this->link) or die('Errant query:  '.$query.'<br /><br />'.mysql_error());
    $output = array();
    if(mysql_num_rows($result) > 0) { 
      while($o = mysql_fetch_assoc($result)) {
        array_push($output, $o);
      }
      return $output;
    } else {
      return false;
    }
  }

}
?>
