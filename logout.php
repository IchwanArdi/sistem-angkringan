<?php
// logout.php
require_once 'auth.php';
logout();
header("Location: login.php?message=logout_success");
exit();
?>