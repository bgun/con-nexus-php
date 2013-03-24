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
    $query = "SELECT UserID, UserName, UserEmail FROM users WHERE UserName = '$username' AND Password = '$password'";
    $result = mysql_query($query, $this->link);
    if(mysql_num_rows($result) === 1) {
      return mysql_fetch_assoc($result);
    } else {
      return false;
    }
  }

  function userHasConventionAccess($uid, $cid) {
    $query = "SELECT UserID, ConventionID FROM linkusersconventions WHERE UserID = $uid AND ConventionID = $cid";
    $result = mysql_query($query, $this->link);
    if(mysql_num_rows($result) === 1) {
      return true;
    } else {
      return false;
    }
  }

  function touchConventionUpdatedDate($cid) {
    $query = "UPDATE conventions SET UpdateDate = NOW() WHERE ConventionID = $cid";
    return mysql_query($query);
  }

  function getConvention($cid) {
    $query  = "SELECT ConventionID, Name, StartDate, UNIX_TIMESTAMP(StartDate) AS StartDateUT";
    $query .= ",EndDate, UNIX_TIMESTAMP(EndDate) AS EndDateUT, Description, Location, Website, Twitter, UpdateDate";
    $query .= ",UNIX_TIMESTAMP(UpdateDate) AS UpdateUT";
    $query .= " FROM conventions WHERE ConventionID = $cid";
	  $result = mysql_query($query,$this->link);
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
	  $result = mysql_query($query,$this->link) or die('Errant query in getConventions. '.mysql_error());
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

  function getConventionsAccessListForUser($uid) {
    // return a list of convention IDs that this user may administer
    $query  = "SELECT U.UserID, C.ConventionID";
    $query .= " FROM conventions C";
    $query .= " LEFT JOIN linkusersconventions LC ON LC.ConventionID = C.ConventionID";
    $query .= " LEFT JOIN users U ON U.UserID = LC.UserID";
    $query .= " WHERE U.UserID = $uid";
    $result = mysql_query($query,$this->link) or die('Errant query: '.$query.'<br />br />'.mysql_error()); 
    if(mysql_num_rows($result) > 0) {
      $output = array();
      while($o = mysql_fetch_assoc($result)) {
        array_push($output, $o['ConventionID']);
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
    $query .= " LEFT JOIN guests G ON(LEG.GuestID = G.GuestID)";
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
    $query .= " GROUP BY EventID ORDER BY E.StartDate";
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

  function getFeedback($cid) {
    if(!is_numeric($cid) || $cid < 0) die("Invalid ID. Error 1");
    $query  = "SELECT Content, Meta, SubmitDate FROM feedback WHERE ConventionID = $cid ORDER BY SubmitDate DESC";
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

  function getGuest($gid) {
    if(!is_numeric($gid) || $gid < 0) die("Invalid ID. Error 1");
    $query  = "SELECT G.GuestID, G.FirstName, G.LastName, G.Bio, G.Role, G.Website,";
    $query .= " GROUP_CONCAT(CAST(LEG.EventID AS CHAR)) AS EventList";
    $query .= " FROM guests G";
    $query .= " LEFT JOIN linkeventsguests LEG ON LEG.GuestID = G.GuestID";
    $query .= " WHERE G.GuestID = $gid";
    $query .= " GROUP BY G.GuestID";
	  $result = mysql_query($query,$this->link) or die('Errant query:  '.$query.'<br /><br />'.mysql_error());
    if(mysql_num_rows($result) === 1) {
      return mysql_fetch_assoc($result);
    } else {
      return false;
    }
  }

  function getGuests($cid) {
    if(!is_numeric($cid)) die("Invalid ID. Error 0");
    $query  = "SELECT G.GuestID, G.FirstName, G.LastName, G.Bio, G.Website, G.Role,";
    $query .= " GROUP_CONCAT(CAST(LEG.EventID AS CHAR)) AS EventList";
    $query .= " FROM guests G";
    $query .= " LEFT JOIN linkeventsguests LEG ON LEG.GuestID = G.GuestID";
    $query .= " WHERE G.ConventionID = $cid";
    $query .= " GROUP BY G.GuestID ORDER BY G.FirstName";
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

  function getGuestsForEvent($eid) {
    $query  = "SELECT LEG.GuestID, G.FirstName, G.LastName FROM linkeventsguests LEG";
    $query .= " LEFT JOIN guests G on G.GuestID = LEG.GuestID";
    $query .= " WHERE LEG.EventID = $eid";
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

  // Insert functions (POST)

  function addNewEvent($cid, $obj) {
    $event_title = mysql_real_escape_string($obj["Title"]);
    $event_start = mysql_real_escape_string($obj["StartDate"]);
    $event_desc  = mysql_real_escape_string($obj["Description"]);
    $event_loc   = mysql_real_escape_string($obj["Location"]);

    $query  = "INSERT INTO events (ConventionID, Title, StartDate, Description, Location) VALUES (";
    $query .= "$cid,'$event_title','$event_start','$event_desc','$event_loc')";

    return mysql_query($query);
  }

  function addNewGuest($cid, $obj) {
    $guest_first   = mysql_real_escape_string($obj["FirstName"]);
    $guest_last    = mysql_real_escape_string($obj["LastName"]);
    $guest_bio     = mysql_real_escape_string($obj["Bio"]);
    $guest_role    = mysql_real_escape_string($obj["Role"]);
    $guest_website = mysql_real_escape_string($obj["Website"]);

    $query  = "INSERT INTO guests (FirstName, LastName, Bio, Role, Website, ConventionID) VALUES (";
    $query .= "'$guest_first','$guest_last','$guest_bio','$guest_role','$guest_website',$cid)";

    return mysql_query($query);
  }

  function connectGuestToEvent($obj) {
    $gid = mysql_real_escape_string($obj['GuestID']);
    $eid = mysql_real_escape_string($obj['EventID']);
    $query = "INSERT INTO linkeventsguests (GuestID, EventID) VALUES ($gid, $eid)";
    return mysql_query($query);
  }

  // Update functions (PUT)

  function updateEvent($eid, $obj) {
    $event_title = mysql_real_escape_string($obj["Title"]);
    $event_start = mysql_real_escape_string($obj["StartDate"]);
    $event_desc  = mysql_real_escape_string($obj["Description"]);
    $event_loc   = mysql_real_escape_string($obj["Location"]);

    $query  = "UPDATE events SET";
    $query .= " Title       = '$event_title',";
    $query .= " StartDate   = '$event_start',";
    $query .= " Description = '$event_desc',";
    $query .= " Location    = '$event_loc'";
    $query .= " WHERE EventID = $eid";

    return mysql_query($query);
  }

  function updateGuest($gid, $obj) {
    $guest_first   = mysql_real_escape_string($obj["FirstName"]);
    $guest_last    = mysql_real_escape_string($obj["LastName"]);
    $guest_bio     = mysql_real_escape_string($obj["Bio"]);
    $guest_role    = mysql_real_escape_string($obj["Role"]);
    $guest_website = mysql_real_escape_string($obj["Website"]);

    $query  = "UPDATE guests SET";
    $query .= " FirstName = '$guest_first',";
    $query .= " LastName  = '$guest_last',";
    $query .= " Bio       = '$guest_bio',";
    $query .= " Role      = '$guest_role',";
    $query .= " Website   = '$guest_website'";
    $query .= " WHERE GuestID = $gid";

    return mysql_query($query);
  }

  // Delete functions (DELETE)

  function removeGuestFromEvent($obj) {
    $gid = mysql_real_escape_string($obj['GuestID']);
    $eid = mysql_real_escape_string($obj['EventID']);
    $query = "DELETE FROM linkeventsguests WHERE GuestID = $gid AND EventID = $eid";
    return mysql_query($query);
  }
}
?>
