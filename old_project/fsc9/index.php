<?php
/**
 * @version 1.4.0
 * @date  26.04.20
 * Этот файл использовался для поиска вирусов на сайте и лечения
 * http://fstrange.ru/coder/php/joomla-udalenie-virusov.html
 * http://fstrange.ru/coder/about_all/joomla-poisk-virusov.html
 * Его можно смело удалять!
 */


define('uxSTART',microtime(true));
define('MEM_START', memory_get_peak_usage(true));
require 'config.php';
require 'functions.php';
require 'print.php';


$sAct = filterV('sAct', 'list,reload,search,view,del,patch');
$sSafeAct = filterV('sSafeAct', ',del,patch,patchPreg,delExif');
$sSort = filterV('sSort', 'uxModify,uxCreate,iSize,f,sExt', isset($_GET['sSort']) ? '_GET' : '_COOKIE');
if (isset($_GET['sSort'])) setcookie('sSort', $sSort, time() + 2 * 3600);
$sType =  filterV('sType', 'iSize,sExt');
$sSign =  filterV('sSign', '>,<,==');
$sAssert = isset($_GET['sAssert']) ? $_GET['sAssert'] : '';


if ('reload' == $sAct && is_file(FILE_LST)) unlink(FILE_LST);
$dStart = empty($_COOKIE['dStart']) ? DIR_START : $_COOKIE['dStart'];
if (!is_file(FILE_LST)  || $sAct == 'reload') {
  setAuth();
  newFLst($dStart, FILE_LST);
  header('Location:?sAct=list&'.ceil(filesize(FILE_LST)/1024));
}

//блокировка если забыл удалить
if (!isAuth()) {
  $s = "<!doctype html><html lang=ru><head><meta charset=utf-8> <title>fsCure</title></head><body>";
  $s .= "<h2>Файл заблокирован! Свяжитесь с разработчиком!</h2>" . copyright() . "</body></html>";
  die($s);
}

$cntF = $i = 0;

$sSearch = $sSrc = '';
$af = $aLst = array();
if ('search' == $sAct) {
  $sSearch = isset($_GET['sSearch']) ? urldecode($_GET['sSearch']) : '';
  $aLst = getSearchLst(FILE_LST, $sSearch, $sSort, $cntF);
} elseif ('view' == $sAct && !empty($_GET['f'])) {
  $sSrc = defined('ENT_IGNORE') ? htmlspecialchars(viewF($_GET['f'], $af), ENT_IGNORE) : htmlspecialchars(viewF($_GET['f'], $af)) ;
} else {
  $aLst = getWarnLst(FILE_LST, $cntF);
  $aLst = getFilteredWarnLst($aLst, $sSort);
}

$cntWarn = count($aLst);
$iRnd = rand(1000, 9999) ;
header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang=ru>
<head>
  <meta charset="utf-8">
  <title>fsCure</title>
  <link rel="stylesheet" type="text/css" href="style.css" >
  <!-- <link rel="stylesheet" href="http://yandex.st/highlightjs/8.0/styles/arta.min.css">
  <link rel="stylesheet" href="http://yandex.st/highlightjs/8.0/styles/pojoaque.min.css">-->
  <link rel="stylesheet" href="highlight.css">
  <script src="highlight.pack.js"></script>
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
<div>
  <?php echo realpath($dStart) ?><br>
  <a href='?sAct=list' class="button white reload">Список файлов</a>
  <a href='?sAct=reload' class="button white reload">Перегрузить</a>
  <span class="info-data"><?php echo date("Ymd H:i:s", filemtime(FILE_LST)) ?></span></div>
<?php if ('view' == $sAct) : ?>
  <div class="space"></div>
<?php if ('del' == $sSafeAct): ?>
  <?php echo htmlMsgDel(delF($af['f']), $af['f']); ?>
<?php else: ?>
  <div><a href="edit.php?f=<?php echo base64_encode($af['f']) ?>" class="button white">править</a>
    <a href="?sAct=view&f=<?php echo $af['f'] ?>&sSafeAct=del" class="button pink">Удалить?</a>
  </div>
<?php endif; ?>
  <div class="fileinfo">
    <div class="file"><?php echo htmlF($af) ?></div>
    <div class="file"><?php echo htmlSignLst($af['aLst']) ?>
    </div>
  </div>
  <pre class="view"><code><?php echo $sSrc ?></code></pre>

  <script type="text/javascript"><?php echo htmlWordHl($af['aLst']) ?></script>

<?php else : ?>
  Найдено: <?php printf("%d/%d", $cntWarn, $cntF) ?>
  <div class="space"></div>
  Сортировка: <?php echo sort2Link($sSort, "?sAct=$sAct&sSearch=$sSearch&sSort=") ?>
  <div class="space"></div>
  <div class="filelst">
    <?php foreach ($aLst as $k => $a) if(assertF($a, $sType, $sSign, $sAssert)): ?>
      <div class="file" id="f_<?php echo $k ?>"><?php echo htmlF($a) ?></div>
      <?php if ('del' == $sSafeAct): ?>
        <?php echo htmlMsgDel(delF($a['f'], base64_decode($sSearch)), $a['f']); ?>
      <?php endif; ?>
      <?php if ('patch' == $sSafeAct): ?>
        <?php echo htmlMsgPatch(patchFf($a['f'], base64_decode($sSearch) , FILE_VIR), $a['f']); ?>
      <?php endif; ?>
      <?php if ('patchPreg' == $sSafeAct): ?>
        <?php echo htmlMsgPatch(patchPreg($a['f'], base64_decode($sSearch)), $a['f']); ?>
      <?php endif; ?>
      <?php if ('delExif' == $sSafeAct): ?>
        <?php echo htmlMsgPatch(delExif($a['f'], base64_decode($sSearch)), $a['f']); ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if ('search' == $sAct || 'filter' == $sAct): ?>
  <div><a href="?<?php echo $_SERVER['QUERY_STRING']?>&sSafeAct=del" class="confirm button pink">Удалить все</a></div>
  <div><a href="?<?php echo $_SERVER['QUERY_STRING']?>&sSafeAct=delExif" class="confirm button pink">Удалить EXIF</a></div>
  <?php if(is_file(FILE_VIR)):?><div><a href="?<?php echo $_SERVER['QUERY_STRING']?>&sSafeAct=patch" class="confirm button pink">Патчить</a></div><?php endif; ?>
  <?php if(FS_PREG):?><div><a href="?<?php echo $_SERVER['QUERY_STRING']?>&sSafeAct=patchPreg" class="confirm button pink">Патчить рег</a>(<?php echo FS_PREG ;?>)</div><?php endif; ?>
  <div class="space"></div>
<?php endif; ?>
<div class="space"></div>
<form id="fmSearch" action="?sAct=search">
  <input type="hidden" value="search" id="sAct" name="sAct"><input type="hidden" value="<?php echo $iRnd ?>" id="r" name="r">
  <label for="sSearch">Поиск по строке</label> <input type="text" name="sSearch" id="sSearch" value="<?php printf('%s',base64_decode($sSearch)) ?>"/>
  <br><label for="sAssert">Сравнить</label>
  <select name="sType">
    <option value="iSize">размер файла</option>
    <option value="sExt">расширение</option>
  </select>
  <select name="sSign">
    <option value="==">==</option>
    <option value="&gt;">&gt;</option>
    <option value="&lt;">&lt;</option>
  </select>
  <input type="text" name="sAssert" id="sAssert" /> <br>
  <input type="submit" value="ok"/>
</form>
<div class="space"></div>
<div>
  <?php echo realpath($dStart) ?><br> <a href='?sAct=list' class="button white reload">Список файлов</a> <a href='?sAct=reload' class="button white reload">Перегрузить</a>
  <span class="info-data"><?php echo date("Ymd H:i:s", filemtime(FILE_LST)) ?></span></div>
<div>Сигнатуры CASE MATCH:<span class="info-data"> <?php echo htmlspecialchars(SCAN_SIGN_CASE) ?></span> <br>
 Сигнатуры NOCASE: <span class="info-data"><?php echo htmlspecialchars(implode(';',Sign2A())) ?> </span></div>
<div><?php echo htmlDbg() ?></div>
</body>
</html>