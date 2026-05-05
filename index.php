<?php
session_start();

if(isset($_SESSION['login'])){
    header("Location: pages/dashboard.php");
    exit;
}else{
    header("Location: auth/login.php");
    exit;
}
?>
