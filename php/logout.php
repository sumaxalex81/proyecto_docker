<?php
session_start();


$_SESSION = [];
session_destroy();


if (isset($_COOKIE['password'])) {
    setcookie('password', '', time() - 3600, "/");
}
if (isset($_COOKIE['token'])) {
    setcookie('token', '', time() - 3600, "/");
}

header("Location: login.php");
exit();
?>
