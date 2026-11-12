<?php
require_once 'auth.php';
session_regenerate_id(true);
session_unset();
session_destroy();
header("Location: login.php");
exit;
?>