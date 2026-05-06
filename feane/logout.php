<?php
// ==========================================
// FILE: logout.php
// Clear session and logout
// ==========================================
session_start();
session_destroy();
header("Location: index.php");
exit();
?>