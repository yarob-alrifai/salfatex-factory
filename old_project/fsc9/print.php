<?php



function copyright(){
  return '<div>разработчик <a href="http://fstrange.ru/coder/about">fStrange</a>
<br><a href="http://fstrange.ru/coder/fscure-script-dlya-poiska-i-udaleniya-virusov-na-sajtax">Кратко о возможностях скрипта</a><br>
<a href="http://fstrange.ru/coder/php/cure-joomla-phpshell.html">Как искать вирусы, пример</a><br>
 </div><div class="space"></div>';
}

function sort2Link($sSort, $uBase){
  $s = "uxModify=время модификации&uxCreate=время создания&iSize=размер&f=имена файлов&sExt=расширение";
  parse_str($s, $a);
  $a0 = array();
  foreach($a as $k=>$v) $a0[$k] = htmlA($uBase.$k, $v);
  $a0[$sSort] =  "<strong>".$a[$sSort]."</strong>";
  return implode(" | ", $a0);
}

function htmlA($u, $sTitle){
  return "<a href=\"$u\">$sTitle</a>";
}

function htmlF($a, $su=''){
  $sF = HtmlA($su.'?sAct=view&amp;f='.$a['f'], $a['f']);
  $s = sprintf("%s %s %d %s %s", ux2dtm($a['uxModify']), ux2dtm($a['uxCreate']), $a['iSize'], $sF, $a['lstSign']);
 return $s ;
}
function htmlSignLst($aLst){
  $a = array();
  foreach($aLst as $sSign=>$v){
    $a[] = rtrim($sSign,'([])')."($v)";
  }
  return implode(' , ', $a);
}
function htmlWordHl($aLst){
  $a = array();
  foreach($aLst as $sSign=>$v){
    $a[] = sprintf("$('pre').wordhl('%s');\n", rtrim($sSign,'([])'));
  }
  return implode("\n", $a);
}
function ux2dtm($ux){
  return date("Ymd H:i:s", $ux);
}

function htmlMsgDel($b, $f = ''){
  $s = '<div class="msg %s">Файл %s %s удален!</div>';
  return sprintf($s, $b?'ok':'fail', $f, $b?'':'не');
}
function htmlMsgPatch($b, $f = ''){
  $s = '<div class="msg %s">Файл %s %s исправлен!</div>';
  return sprintf($s, $b?'ok':'fail', $f, $b?'':'не');
}


function htmlDbg(){
  $s = '<div class="dbg">Время %.4F сек.</div><div class="dbg">Память %s/%s Mb</div>';
  return sprintf($s,  microtime(true) - uxSTART, number_format(MEM_START / 1048576, 3), number_format(memory_get_peak_usage(true) / 1048576, 3));
}