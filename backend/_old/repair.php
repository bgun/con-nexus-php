<?php

require_once("../_db.php");

$link = mysql_connect($dbserver, $dbuser, $dbpass) or die('Cannot connect to sthe DB!');
mysql_select_db($dbname,$link) or die('Cannot select the DB: '.mysql_error());


/* Guests are no longer unique
$query  = "SELECT G.GuestID, E.ConventionID, G.FirstName, G.LastName";
$query .= " FROM guests G";
$query .= " LEFT JOIN linkeventsguests L ON G.GuestID = L.GuestID";
$query .= " LEFT JOIN events E ON E.EventID = L.EventID";
$query .= " ORDER BY GuestID, ConventionID ASC";

$result = mysql_query($query,$link) or die('Errant query:  '.$query.'<br /><br />'.mysql_error());
$output = array();
if(mysql_num_rows($result) > 0) { 
  while($o = mysql_fetch_assoc($result)) {
    array_push($output, $o);
  }
}

foreach($output as $item) {
  $query = "UPDATE Guests SET ConventionID = ".$item['ConventionID']." WHERE GuestID = ".$item['GuestID'];
  if(mysql_query($query)) {
    echo "Updating Guest ".$item['FirstName']." ".$item['LastName']." to ConventionID ".$item['ConventionID']."<br />";
  }
}

mysql_close($link);
*/
?>
