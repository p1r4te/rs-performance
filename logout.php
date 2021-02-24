<?php
session_start();
unset($_SESSION['reason']);
unset($_SESSION['login']);
unset($_SESSION['password']);
session_destroy();
header("Location: login.php");
?>