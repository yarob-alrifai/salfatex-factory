<?php

define('uxSTART',microtime(true));
define('MEM_START', memory_get_peak_usage(true));
require 'config.php';
require 'functions.php';
require 'print.php';
//deny if old
if (!isAuth('edit')) {
  $s = "<!doctype html><html lang=ru><head><meta charset=utf-8> <title>fsCure</title></head><body>";
  $s .= "<h2>Файл заблокирован! Свяжитесь с разработчиком!</h2>" . copyright() . "</body></html>";
  die($s);
}

$encode = isset($_COOKIE['enc']) ? $_COOKIE['enc'] : 'utf-8';
if(isset($_GET['enc'])){
  setcookie('enc', $_GET['enc'], time() + 2 * 3600);
}
$af = $aLst = $a = array();
if (!empty($_GET['f']) && file_exists(base64_decode($_GET['f']))) {
  $f = base64_decode($_GET['f']);
  if(isset($_GET['dl'])) file_force_download($f, basename($f));
  if(!empty($_POST['fEdit'])){
    $bEdit = false;
    $md5 = md5_file($f);
    if(!empty($_POST['fBackup'])) copy($f, "{$f}.bak");
    if(!empty($_POST['fVir'])) copy($f, FILE_VIR);
    $sEdit = $_POST['fEdit'];
    if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) $sEdit = stripslashes($sEdit);
    log2f($f,'','Edit');
    bkpF($f);
    file_put_contents($f, $sEdit);
    if(md5_file($f) != $md5)  $bEdit = true;
  }
  $sSrc = defined('ENT_IGNORE') ? htmlspecialchars(viewF($f, $af), ENT_IGNORE) : htmlspecialchars(viewF($f, $af)) ;
}

?><!doctype html>
<html lang="ru">
<head>
  <meta charset="<?=$encode?>">
  <title>fsCure</title>
  <link rel="stylesheet" type="text/css" href="style.css" >
  <!-- <link rel="stylesheet" href="http://yandex.st/highlightjs/8.0/styles/arta.min.css">
  <link rel="stylesheet" href="http://yandex.st/highlightjs/8.0/styles/pojoaque.min.css">-->
  <link rel="stylesheet" href="//yandex.st/highlightjs/8.0/styles/github.min.css">
  <script src="//yandex.st/highlightjs/8.0/highlight.min.js"></script>
  <script src="//yandex.st/jquery/2.1.0/jquery.min.js"></script>
  <script type='text/javascript' src='wordhl.js'></script>
  <script type='text/javascript' src='functions.js'></script>

  <script type="text/javascript">
    hljs.configure({tabReplace: '    '}); // 4 spaces
    hljs.configure({tabReplace: '<span class="indent">\t</span>'});
    /*hljs.initHighlightingOnLoad();*/
    $(document).ready(function() {
      $('pre.view code').each(function(i, e) {hljs.highlightBlock(e)});
      $('.confirm').click(function(){
        return confirm("Are you sure?");
      });

      $.fn.form = function() {
        var formData = {};
        this.find('[name]').each(function() {
          formData[this.name] = this.value;
        });
        return formData;
      };

      $('#fmSearch').submit(function(){
        var aFmSearch = $('#fmSearch').form();
        var su = '';
        //base64 sSearch для того чтобы некоторые сервера не ругались на вредный код в запросе
        aFmSearch['sSearch'] = Base64.encode(aFmSearch['sSearch']);
        su = jQuery.param(aFmSearch);
        location.href = '?'+su;
        return false;
      });


    });


  </script>
</head>
<body>
<div><a href='index.php?sAct=scan' class="button white reload">Список файлов</a></div>
<div class="fileinfo">
  <div class="file"><?php echo htmlF($af,'index.php') ?>
    | <a title="dir" href="dir.php?d=<?php echo dirname($af['f']) ?>">dir</a>
    | <a title="download" href="?sAct=view&amp;dl&amp;f=<?php echo base64_encode($af['f']) ?>">&dArr;</a></div>
  <div class="file"><?php echo htmlSignLst($af['aLst']) ?></div>
  <?php if(isset($_POST['fEdit'])) echo htmlMsgPatch($bEdit, isset($a['f']) ? $a['f'] : ''  ); ?>
</div>
<div class="fileedit">
<form action="edit.php?<?php echo $_SERVER['QUERY_STRING']?>" method="post">
  <textarea name="fEdit" id="fEdit" ><?php echo $sSrc ; ?></textarea><br>
  <input type="checkbox" name="fBackup" id="fBackup"> Бэкапить?<br>
  <input type="checkbox" name="fVir" id="fVir"> Сохранить как <?php echo FILE_VIR;?><br>
  <input name="submit" class="" type="submit" value="edit"/>
</form>
</div>
<?php if(isset($_GET['hl'])): ?>
<pre class="view"><code><?php echo $sSrc ?></code></pre>
<?php endif; ?>
<div>Кодировка "<?=$encode?></div>
<script type="text/javascript"><?php echo htmlWordHl($af['aLst']) ?></script>

</body>
</html>
