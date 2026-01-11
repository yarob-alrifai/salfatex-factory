<?php error_reporting(E_ALL);
ini_set("display_errors", "on");

?><!DOCTYPE html>
<html lang=ru>
<head>
  <meta charset="utf-8">
  <title>fsCure</title></head>
<body>
<div><?php
  $d = isset($_GET['d']) ? $_GET['d'] : '..';
  if (isset($_GET['setCookie'])) setcookie('dStart', $d, time() + 3600);

  if (isset($_GET['chm'])) {
    chmod($d, octdec($_GET['chm']));
    echo "<script>location.assign(\"dir.php?d=$d\"); </script>";
    die();
  }

  echo realpath($d);
  echo " <a href='dir.php?d=$d&chm=" . decoct(0755) . "' title='chmod 0755?'>[755]</a> ";
  echo " <a href='dir.php?d=$d&chm=" . decoct(0777) . "' title='chmod 0777?'>[777]</a> ";
  echo "<br>";
  echo empty($_COOKIE['dStart']) ? $d : $_COOKIE['dStart'];

  echo '<br>';
  echo " <a href='dir.php?d=$d&setCookie'>Установить стартовой?</a><br><br>";

  $a = glob($d . '/*', GLOB_ONLYDIR);

  $dParent = dirname(realpath($d));
  echo "<a href='dir.php?d=$dParent'>$dParent</a> <a href='dir.php?d=$dParent&setCookie' title='Установить стартовой?'>[S]</a> ";


  echo decoct(fileperms($dParent)) . "<br/>";
  foreach ($a as $d0) {
    echo "<a href='dir.php?d=$d0'>$d0</a> <a href='dir.php?d=$d0&setCookie' title='Установить стартовой?'>[S]</a> ";
    echo decoct(fileperms($d0)) . "<br/>";
  }
  ?></div>
<div>
  <?php

  $a = glob($d . '/*');
  if ($a) foreach ($a as $f) if (is_file($f)) {
    echo "$f ";
    echo decoct(fileperms($f)) . "<br/>";
  }

  ?>
</div>
</body>
</html>

