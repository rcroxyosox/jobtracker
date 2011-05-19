<?php
session_start();
$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
header('Location: '.$url);

?>