<?php

/*
 * last mod
 * added /fst option. search in first string $_ or /l
 */
function setAuth() {
  $f = 'fscure.auth';
  if (is_file($f)) return 0;
  file_put_contents($f, time());
  return 1;
}

function isAuth($act = 0) {
  $f = 'fscure.auth';
  if (!is_file($f)) return 0;
  $ux0 = file_get_contents($f);
  $ux = time();
  if ($act == 'edit' && $ux < $ux0 + (2 * 3600 * 24)) return true;
  if (!$act && $ux < $ux0 + (5 * 3600 * 24)) return true;
  return false;
}

function newFLst($d, $fLst) {
  //if (DIRS_ONLY) $aDirLst = explode("\n", DIRS_ONLY); else $aDirLst = array(0 => $d);
  //old

  $aDirLst = array(0 => $d);
  foreach ($aDirLst as $d) {
    $d = trim($d, "\r\n");
    saveFLst($d, $fLst);
  }
}

/**
 * recursive get all files from $d and save to $fLst file
 *
 * @param string $d source directory
 * @praram string $fLst file listing
 * @praram int $cnt  count files
 * @return array dirlist
 */
function saveFLst($d, $fLst) {
  $rLst = fopen($fLst, "a");

  $aLst = $a = array();

  if ($r = opendir($d)) {


    while (false !== ($f = readdir($r))) {
      if ($f == '.' || $f == '..') {
        continue;
      }

      $fPath = $d . DIRECTORY_SEPARATOR . $f;
      if (!canDLst($fPath)) continue;
      if (is_file($fPath) && canFLst($fPath)) {

        $a = stat($fPath);
        $s = af2s($a, $fPath);
        //if($a['ctime'] < strtotime('-1 year'))  fwrite($rLst, $s);
        fwrite($rLst, $s);
      } elseif (is_dir($fPath) && !is_link($fPath)) {

        //$aLst = array_merge($aLst, saveFLst($fPath, $fLst));
        $aLst += saveFLst($fPath, $fLst);
      }
    }
    closedir($r);
  }


  fclose($rLst);
  setAuth();
  return $aLst;
}

function canDLst($d) {
  if(PATH_REQUIRED) {
    $aPath =  lst(PATH_REQUIRED);
    $bRequire = false;
    foreach($aPath as $v) if(strpos($d, $v) !== false) $bRequire = true;
    if(!$bRequire) return $bRequire;
  }
  if (DIRS_EXCLUDE) {
    $aMask = lst(DIRS_EXCLUDE);
    foreach ($aMask as $v) if (strpos($d, $v) !== false) return false;
  }
  return true;
}

/**
 * filter for files. if file valid return true
 * @param string $f filename
 * @return bool
 */
function canFLst($f) {

  /*
  filenames & dirnames  separated semicolon
  */
  $fName = basename($f);

  if (FILES_EXCLUDE) {
    $aMask = lst(FILES_EXCLUDE);
    foreach ($aMask as $v) if (fnmatch($v, $fName)) return false;
  }

  $iSize = filesize($f);

  if ($iSize > FILE_SIZE_MAX) return false;
  if (FILE_SIZE_LOW && $iSize < FILE_SIZE_LOW) return false;

  if (FILES_ONLY_PHP) {
    $fName0 = strtolower($fName);
    $aPhpExt = lst(PHP_EXT);
    foreach($aPhpExt as $Ext){
      if(strpos($fName0, $Ext) !== false) return true;
    }
    return false;
  }

  return true;
}

/**
 * @param $fLst
 * @return array
 */
function getWarnLst($fLst, &$cnt) {
  $r = fopen($fLst, "r");
  $a0 = array();
  $aScanSign = Sign2A('(');
  $aScanSignCase = lst(SCAN_SIGN_CASE);
  while (!feof($r)) {
    $sInfo = fgets($r);
    $a = s2af($sInfo);
    if ($a['f']) {
      $cnt++;
      //$s = file_get_contents($a['f'], NULL, NULL, 0, 1024);
      $s = file_get_contents($a['f']);
      if (hasCaseWarnSign($s, $aScanSignCase)) {
        $a0[] = $a;
      } else { //nocase
        $s = strtolower($s);
        if (hasWarnSign($s, $aScanSign)) $a0[] = $a;
      }
    }
  }
  fclose($r);
  return $a0;
}

/**
 * @param $s string  !must be lowercase
 * @return bool
 */
function hasWarnSign($s, $aScanSign) {
  //del the opening brace for functions ex: eval(
  //need for finding "eval (" and "eval("
  foreach ($aScanSign as $sSign) if ($sSign && strpos($s, $sSign) !== false) return true;
  return false;
}

/**
 * hasCaseWarnSign CASE sensitive search
 * @param $s string  !must be lowercase
 * @return bool
 */
function hasCaseWarnSign($s, $aScanSignCase) {
  foreach ($aScanSignCase as $sSign) if (strpos($s, $sSign) !== false) return true;
  return false;
}

/**
 * @param $fLst
 * @param $sSign
 * @return array
 */
function getSearchLst($fLst, $sSearch, $kSort = 'f', &$cntF) {
  if (!trim($sSearch)) return array();
  $sSearch = strtolower(base64_decode($sSearch));
  $sSearch = synonimizeSSearch($sSearch);
  $r = fopen($fLst, "r");
  $a0 = $aSort = array();
  $i = $cntF = 0;

  $aAct = parseSearchPrefix($sSearch);
  $sAct = $aAct['sAct'];
  $sVal = $aAct['v'];
  $fMask = $aAct['fName'];
  $sSearch = $aAct['sSearch'];


  while (!feof($r)) {
    $sInfo = fgets($r);
    $a = s2af($sInfo);
    if ($a['f'] && doFname($a['f'], $fMask) &&  is_file($a['f'])) {
      $cntF++;
      $s = ($sAct != 'f') ? file_get_contents($a['f']) : '';
      if (!$sAct) $s = strtolower($s);
      //multisearch /m
      if ($sAct == 'm') {

        $s = strtolower($s);
        $aSearch = explode($sVal, $sSearch);
        $bSearch = true;
        foreach ($aSearch as $vSearch) if (trim($vSearch)) {
          if (strpos($s, $vSearch) === false) $bSearch = false;
        }
        if ($bSearch) {
          $i++;
          $a['lstSign'] = 'multisearch s:' . html2helper($sVal) . ' ' . htmlspecialchars($sSearch);
          $a0[$i] = $a;
          $aSort[$i] = $a[$kSort];
        }

        // shield /s
      } elseif ($sAct == 's') {

        $s = strtolower($s);
        $sSearchS = str_replace($sVal, '', $sSearch);

        if (strpos($s, $sSearchS) !== false) {
          $i++;
          $aPreg = regSearch($s, $sSearchS);
          $a['lstSign'] = 'search /s ' . html2helper(implode(' ', $aPreg));
          $a0[$i] = $a;
          $aSort[$i] = $a[$kSort];
        }

        //long str /l
      } elseif ($sAct == 'l') {

        $sPreg = '~[^\ \n]{400,}~';
        if (preg_match_all($sPreg, $s, $aPreg) && strpos($s, $sSearch) !== false) {
          $aPreg[0] = array_map('_cbCutS', $aPreg[0]);
          $i++;
          $a['lstSign'] = 'long string /l ' . html2helper(implode(' ', $aPreg[0]));
          $a0[$i] = $a;
          $aSort[$i] = $a[$kSort];
        }

        //first str /1 /long
      } elseif ($sAct == '1') {

        $aTmp = explode("\n", $s);
        $s = isset($aTmp[0]) ? $aTmp[0] :'';

        $fTmp = strtolower(basename($a['f']));
        $fPhp = strpos($fTmp,'.php') || strpos($fTmp,'.phtml');

        if($fPhp && strpos('.'.$sSearch ,' /long') == 1 && strlen($s) > 64) { //first long
          $i++;
          $a['lstSign'] = 'first string /1 long:' . html2helper(substr($s,0,32));
          $a0[$i] = $a;
          $aSort[$i] = $a[$kSort];
        } elseif(stripos($s, $sSearch) !== false) {
          $i++;
          $a['lstSign'] = 'first string /1 ' . html2helper(substr($s,0,32));
          $a0[$i] = $a;
          $aSort[$i] = $a[$kSort];

        } /*else {
          $i++;
          $a['lstSign'] = 'first string /1 0:' . html2helper($sSearch.': '.substr($s,0,32));
          $a0[$i] = $a;
          $aSort[$i] = $a[$kSort];
        }*/


        //clEan str /e
      } elseif ($sAct == 'e') {

        $s = cleanChrs($s);
        $s = strtolower($s);
        if (strpos($s, $sSearch) !== false && $aPreg = regSearch($s, $sSearch)) {
          $i++;
          $aPreg = regSearch($s, $sSearch);
          $a['lstSign'] = 'clean string /e ' . html2helper(implode(' ', $aPreg));
          $a0[$i] = $a;
          $aSort[$i] = $a[$kSort];
        }

        //file /f
      } elseif ($sAct == 'f') {

        // ex: /fconfig.php kjkj
        $afSearch = explode(' ',$sSearch, 2);
        $bSearchF = strpos('.'.$a['f'], $afSearch[0]);  //has in fname


        $sSearchF = !isset($afSearch[1]) || $afSearch[1]==' ' ? '' : $afSearch[1];

        if($bSearchF && $sSearchF){
          $s = file_get_contents($a['f']);
          $s = cleanChrs($s);
          $s = strtolower($s);
        } else {
          $s = '';
        }

        if (($bSearchF && !$sSearchF) || ($bSearchF && strpos($s, $sSearchF) !== false && $aPreg = regSearch($s, $sSearchF))) {
          $i++;
          $aPreg = $sSearchF ? regSearch($s, $sSearchF) : array();
          $a['lstSign'] = 'clean string in files /f '.$sSearchF . html2helper(implode(' ', $aPreg));
          $a0[$i] = $a;
          $aSort[$i] = $a[$kSort];
        }

        //Hex(oct) str  /h
      } elseif ($sAct == 'h') {

        //$s = cleanChrs($s);
        $s = strtolower($s);
        $sPreg = "~\\\x[a-h0-9]{2}~";
        if (strpos($s, "\\x") !== false && preg_match_all($sPreg, $s, $aPreg)) {
          $sReg = "~.{0,6}\\\x[a-h0-9]{2}.{0,6}~";
          preg_match_all($sReg, $s, $aPreg);
          $aPreg[0] = array_map('_cbCutS', $aPreg[0]);
          $aPreg[0] = array_slice($aPreg[0], 0, 16);
          $i++;
          $a['lstSign'] = 'hex string /h ' . html2helper(implode(' ', $aPreg[0]));
          $a0[$i] = $a;
          $aSort[$i] = $a[$kSort];
        }

        //count
      } elseif ($sAct == 'c') {


        if (strpos($s, $sSearch) !== false && $cntSearch = substr_count($s, $sSearch)) {
          if (!intval($sVal)) $sVal = 2;
          if ($cntSearch >= $sVal) {
            $i++;
            $aPreg = regSearch($s, $sSearch);
            $a['lstSign'] = "[" . $cntSearch . "]" . html2helper(implode(' ', $aPreg));
            $a0[$i] = $a;
            $aSort[$i] = $a[$kSort];
          }
        }

        //anon functions search #test /a
      } elseif ($sAct == 'a') {

        $s = strtolower($s);
        $sPreg = '~\$[a-z0-9_]{1,32}\([0-9a-z_$\/#\'\"\s]{1}~'; // после переменной. \n  $  0-9a-z_\#
        $cntSearch = strpos($s, '<?') !== false ? preg_match_all($sPreg, $s, $aPreg) : 0;


        if ($cntSearch) {
          if (!intval($sVal)) $sVal = 1;
          if ($cntSearch >= $sVal) {
            $i++;
            $a['lstSign'] = "[" . $cntSearch . "]" . html2helper(implode(' ', $aPreg[0]));
            $a0[$i] = $a;
            $aSort[$i] = $a[$kSort];
          }
        }

        //default
      } elseif (strpos($s, $sSearch) !== false) {
        $s = strtolower($s);
        $i++;
        $aPreg = regSearch($s, $sSearch);
        $a['lstSign'] = html2helper(implode(' ', $aPreg));
        $a0[$i] = $a;
        $aSort[$i] = $a[$kSort];
      }
    }
  }
  fclose($r);
  array_multisort($aSort, strpos($kSort, 'ux') !== false ? SORT_DESC : SORT_ASC, $a0);
  saveSearchLst($a0, $sSearch);
  return $a0;
}

/**
 * Convert string to fileinfo array( $ctime, $mtime, $size, $f)
 * @param $s string
 * @return array fileinfo array( $ctime, $mtime, $size, $f)
 */
function s2af($s) {
  $a = array();
  sscanf($s, "%d;%d;%d;;%s\n", $a['uxCreate'], $a['uxModify'], $a['iSize'], $a['f']);
  $a['f'] = rtrim(substr($s, strpos($s, ';;') + 2));
  $a['sExt'] = pathinfo($a['f'], PATHINFO_EXTENSION);

  return $a;
}

/**
 * Convert array to fileinfo string
 * @param $f string filepath
 * @param $a array fileinfo array( $ctime, $mtime, $size, $f)
 * @return string
 */
function af2s($a, $f) {
  $s = sprintf("%s;%s;%s;;%s\n", $a['ctime'], $a['mtime'], $a['size'], $f);
  return $s;
}

/**
 * @param $aWarnLst array
 * @return array
 */
function getFilteredWarnLst($aWarnLst, $kSort = 'f') {
  $a = $aSort = array();
  $aScanSignCase = lst(SCAN_SIGN_CASE);
  $aScanSign = Sign2A();
  foreach ($aWarnLst as $k => $a0) {
    $lstSign = '';
    $s = file_get_contents($a0['f']);
    foreach ($aScanSignCase as $sSign) {
      if (strpos($s, $sSign) !== false) $lstSign .= $sSign . ',';
    }
    $s = strtolower($s);
    //  Removes single line '//' comments, treats blank characters
    $s = preg_replace('![ \t]*//.*[ \t]*[\r\n]!', '', $s);

    //remove multi line comments
    $s = preg_replace('!/\*.*?\*/!s', '', $s);

    // Compress newlines after opening braces and strip spaces before and after brace
    $s = preg_replace('/\s*\(\s*\n?\s*/', '(', $s);

    //remove all concatent string
    $s = preg_replace("/[\"']{1}[ ]{0,}\.[ ]{0,}[\"']{1}/", '', $s);


    foreach ($aScanSign as $sSign) {
      if ($sSign && strpos($s, $sSign) !== false) $lstSign .= $sSign . ',';
    }
    if ($lstSign) {
      $a0['lstSign'] = rtrim($lstSign, ',');
      $a[$k] = $a0;
      $aSort[$k] = $a0[$kSort];
    }
  }
  array_multisort($aSort, (strpos($kSort, 'ux') !== false) ? SORT_DESC : SORT_ASC, $a);
  return $a;
}

/**
 * filter input var
 * @param $k string
 * @param $canLst string list of valid values
 * @param string $sInput input method ex: _GET, _POST, _COOKIE
 * @return mixed
 */
function filterV($k, $canLst, $sInput = '_GET') {
  $a = explode(',', $canLst);
  return isset($GLOBALS[$sInput][$k]) && in_array($GLOBALS[$sInput][$k], $a) ? $GLOBALS[$sInput][$k] : $a[0];
}

function viewF($f, &$af) {
  if (!is_file($f)) return '';
  $r = fopen($f, 'r');
  $a = fstat($r);
  //$a['uxAccess'], $a['uxCreate'], $a['uxModify'], $a['iSize']
  $af = array('uxCreate' => $a['ctime'], 'uxModify' => $a['mtime'], 'iSize' => $a['size']);
  $af['f'] = $f;
  $s0 = fread($r, $a['size']);

  $s = strtolower($s0);

  //  Removes single line '//' comments, treats blank characters
  $s = preg_replace('![ \t]*//.*[ \t]*[\r\n]!', '', $s);

  //remove multi line comments
  $s = preg_replace('!/\*.*?\*/!s', '', $s);


  // Compress newlines after opening braces and strip spaces before and after brace
  $s = preg_replace('/\s*\(\s*\n?\s*/', '(', $s);


  $aSign = array();
  $aSignLst = Sign2A() + lst(strtolower(SCAN_SIGN_CASE));
  foreach ($aSignLst as $sSign) {
    $cnt = $sSign ? substr_count($s, $sSign) : 0;
    if ($cnt) $aSign[$sSign] = $cnt;
  }
  $af['aLst'] = $aSign;
  $af['lstSign'] = '';
  fclose($r);
  return $s0;
}

function delF($f, $Search = '') {
  if (DIR_SAFE) {
    $aMask = explode(';', DIR_SAFE);
    foreach ($aMask as $v) if (fnmatch($v, $f)) return null;
  }

  //chmod(dirname($f),0755);
  //chmod($f,0644);
  log2f($f, $Search, 'delF');
  bkpF($f);
  if(DEL_REPLACE) {
    $b = file_put_contents($f, DEL_REPLACE);
  } else {
    $b = unlink($f);
  }
  return $b;
}

function patchFF($f, $Search = '', $fVir, $sReplace = '') {
  $sSign = file_get_contents($fVir);
  if (DIR_SAFE) {
    $aMask = explode(';', DIR_SAFE);
    foreach ($aMask as $v) if (fnmatch($v, $f)) return null;
  }
  $s = file_get_contents($f);
  if (is_file(FILE_REPLACE)) $sReplace = file_get_contents(FILE_REPLACE);

  // may be troubles in php<5.2 and multibite string
  $s = str_replace($sSign, $sReplace, $s);

  //chmod(dirname($f),0755);
  //chmod($f,0644);
  log2f($f, $Search, 'patchFF');
  bkpF($f);
  return file_put_contents($f, $s);
}

function patchPreg($f, $Search = '') {
  if (DIR_SAFE) {
    $aMask = explode(';', DIR_SAFE);
    foreach ($aMask as $v) if (fnmatch($v, $f)) return null;
  }
  if (!FS_PREG) return null;
  $s = file_get_contents($f);
  $s = preg_replace(FS_PREG, '', $s);
  log2f($f, $Search, 'patchPreg');
  bkpF($f);
  return file_put_contents($f, $s);
}

/**
 * @param array $a file type
 * @param string $k array key
 * @param string $sSign ">" , "<", OR "=="
 * @param string $sAssert comparison value
 * @return bool
 */
function assertF($a, $k = '', $sSign = '', $sAssert = '') {
  $sAssert = trim($sAssert);
  if (!$sAssert || !isset($a[$k])) return TRUE; //no_filter
  $sVal = $a[$k];

  if ($k == 'sExt') {
    $sVal = "'$sVal'";
    $sAssert = "'$sAssert'";
  }

  return @assert($sVal . $sSign . $sAssert);
}

function parseSearchPrefix($sSearch) {
  $sSrc = $sSearch;
  $sAct = '';
  $fName = '*';
  $v = '/';
  if ($sSearch[0] == '/' && in_array($sSearch[1], array('m', 's', 'c', 'l', 'e', 'a', 'h', 'f', '1'))) {
    $sAct = $sSearch[1];

    $sSearch = substr($sSrc, 2, strlen($sSrc));
    $iEnd = strpos($sSearch, '///');

    if ($iEnd && $iEnd < 16) {
      $sArg = substr($sSearch, 0, $iEnd);
      $sArg = ltrim($sArg, ' /');

      if (strpos($sArg, ' /')) list($v, $fName) = explode(" /", $sArg, 2); else {
        if (strlen($sArg) < 3) $v = $sArg; else $fName = $sArg;
      }
      $sSearch = ltrim(strstr($sSearch, '///'), '/');
    }
  }
  $a = compact("sAct", "v", "fName", "sSearch");
  return $a;
}

function doFname($fName, $fMask = '*') {
  if ($fMask == '*' || !$fMask) return true; //all file
  if ($fMask[0] != '!') return stripos('!' . $fName, $fMask); //mask file
  $fMask = ltrim($fMask, '!');
  return !stripos('!' . $fName, $fMask); //not mask
}

function delExif($f, $Search = '') {
  $img = imagecreatefromjpeg($f);
  $b = imagejpeg($img, $f, 100);
  imagedestroy($img);
  log2f($f, $Search, 'delExif');
  return $b;
}

function urlSafeB64Encode($data) {
  $b64 = base64_encode($data);
  $b64 = str_replace(array('+', '/', '\r', '\n', '='),
    array('-', '_'),
    $b64);
  return $b64;
}

function urlSafeB64Decode($b64) {
  $b64 = str_replace(array('-', '_'),
    array('+', '/'),
    $b64);
  return base64_decode($b64);
}

//shugar
function lst($lst, $sSep = ';') {
  return explode($sSep, $lst);
}

/**
 * Sign2S
 * @param $Sign
 * @return array
 */
function Sign2A($chrDel = '') {
  $sSign = file_get_contents('sign.vir');
  $sSign = str_replace('~', '', $sSign);
  //$s = SCAN_SIGN . ';' . str_rot13(strrev($sSign));
  $s = SCAN_SIGN . ';' . $sSign;
  if ($chrDel) $s = str_replace($chrDel, '', $s);
  return lst(strtolower($s));
}

function dbg2f($message, $label = null) {
  $sf = 'dbg2f.log';
  $s = date('Y-m-d H:i:s');
  $s .= $label ? $label . ': ' : '';
  $s .= print_r($message, true);
  file_put_contents($sf, $s . "\n\n", FILE_APPEND);
  chmod($sf, 0664);
}

//if(!function_exists('log2f')) function log2f($a,$b){;};
function file_force_download($file, $fName) {

  if (ob_get_level()) {
    ob_end_clean();
  }
  // заставляем браузер показать окно сохранения файла
  header('Content-Description: File Transfer');
  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename=' . $fName);
  header('Content-Transfer-Encoding: binary');
  header('Expires: 0');
  header('Cache-Control: must-revalidate');
  header('Pragma: public');
  header('Content-Length: ' . filesize($file));
  // читаем файл и отправляем его пользователю
  readfile($file);
  exit;

}


function bkpF($f) {
  if (!class_exists('ZipArchive')) return 0;
  $fArchive = DIR_FSCURE . '/_bckp.zip';
  $zip = new ZipArchive;
  $zip->open($fArchive, ZIPARCHIVE::CREATE);
  $fDst = ltrim(realpath($f), DIRECTORY_SEPARATOR);
  $zip->addFile($f, $fDst);
  $zip->close();
}

function cleanSpaces($s) {
  $s = strtolower($s);

  $s = str_replace(' ', '', $s);
  return $s;
}

function cleanChrs($s, $a = array(' ', '"', "'", '.', "\n", "\r", "\t")) {
  $s = str_replace($a, '', $s);
  return $s;
}

function html2helper($s) {
  $a = array('&', '<', '>');
  $aR = array('&amp;', '&lt;', '&gt;');
  return str_replace($a, $aR, $s);
}

function regSearch($s, $sSearch, $iPreviewBefore = 6, $iPreviewAfter = 16, $iLimit = 16) {
  $sSearch = preg_quote($sSearch); //echo $sSearch;
  if (strpos('.' . $sSearch, 'eval')) $sSearch = 'eval[/(]';

  $sReg = "~.{0," . $iPreviewBefore . "}" . $sSearch . ".{0," . $iPreviewAfter . "}~";
  if (preg_match_all($sReg, $s, $a0)) {
    $aPreg = array_slice($a0[0], 0, $iLimit);
    $aPreg = array_map('_cbCutS', $aPreg);
    return $aPreg;
  }
  return array();
}

function _cbCutS($s, $iCut = 32) {
  return substr($s, 0, $iCut);
}

function synonimizeSSearch($s) {
  return str_replace('--ev', 'eval', $s);
}

function log2f($f, $Search = '', $Act = null) {
  $sf = 'log.txt';
  $s = date('Y-m-d H:i:s ');
  $s .= $Search ? ' s:' . $Search . ': ' : '';
  $s .= $Act ? ' ' . $Act . ': ' : '';
  //$s .= print_r($message, true);
  $s .= 'md:' . date('Y-m-d H:i:s ', filemtime($f));
  $s .= 'cd:' . date('Y-m-d H:i:s ', filectime($f));
  $s .= $f;
  file_put_contents($sf, $s . "\n", FILE_APPEND);
}

function saveSearchLst($aLst, $sSearch){
  if(!$aLst) return 0;
  $f = 'search.lst';
  $s = '# '.$sSearch."\n\n";

  foreach ($aLst as $k => $a){
    $s .= $a['f']."\n";
  }

  file_put_contents($f, $s);
}