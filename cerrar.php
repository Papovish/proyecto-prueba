<?php
include 'uploads/setup/config.php';

session_start();
session_destroy();

header("Location:login.html");
?>
