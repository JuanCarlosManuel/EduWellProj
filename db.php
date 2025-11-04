<?php
$hostname = "localhost";
$username = "root"; 
$pass = "";       
$dbname = "eduwell";  

// Create connection
$conn = new mysqli($hostname, $username, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
