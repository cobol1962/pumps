<?php
  session_start();
  foreach ($_POST as $k => $v) {
    $_SESSION["reports"][$k] = $v;
  }
  var_dump($_SESSION);
 ?>
