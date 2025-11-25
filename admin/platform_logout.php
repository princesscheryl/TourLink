<?php
session_start();
session_unset();
session_destroy();
header("Location: platform_login.php");
exit();
