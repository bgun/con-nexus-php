<?php
if($_SERVER['SERVER_NAME'] === 'connexus.ben' || $_SERVER['SERVER_NAME'] === 'connexus-app.ben') {
  $dbserver = 'localhost';
  $dbname   = 'connexus';
  $dbuser   = 'root';
  $dbpass   = 'dFs7zxj34S';
} else {
  $dbserver = 'mysql305.hostexcellence.com';
  $dbname   = 'bengund_connexus';
  $dbuser   = 'bengund_connexus';
  $dbpass   = 'dFs7zxj34S';
}
?>

