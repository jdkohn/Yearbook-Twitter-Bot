<?php

$servername = "localhost";
  $username = "hsfantasyball";
  $password = "2016";
  $dbname = "halestudents";

      // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
      // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  } 

?>
