<?php
/**
 * Logout Handler
 * Destroys the session and redirects to login page.
 */
session_start();
session_unset();
session_destroy();
header("Location: /smart waste system/index.php");
exit();
