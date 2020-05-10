<?php
$servername = "localhost";
$username = "php";
$password = "bhv53FAusNfD6ZL";
$dbname = "hue";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("SQL Connection failed: " . $conn->connect_error);
}
