<?php
error_reporting(E_ALL);
ini_set("display_errors", "on");
set_time_limit(0);
date_default_timezone_set('Europe/Moscow');

define('DIR_START', '..');

define('FILE_SIZE_MAX', 1024 * 1024) ;
define('FILE_SIZE_LOW', 13) ;

// semicolon  separated file mask
//*[0-9]x[0-9]*.jpg;*[0-9]x[0-9]*.png;*[0-9]x[0-9]*.gif;*[0-9]x[0-9]*.jpeg
define('FILES_EXCLUDE','*.js;*.css;*.ini');

// semicolon - separated dir mask, etc: */fscure/*.*;*/docs_*
// files in this dirs can`t del or rewrite
define('DIR_SAFE','*'.basename(getcwd()).'*');

/*
 * набор регулярок для чистки врусов типа php.inject по регулярному выражению
 * replace php.inject
 * code:
 * 
*/
define('FS_PREG','~^<\?php\s.*eval.*\?>~i');
//define('FS_PREG','~/\*[a-z0-9]{3,5}\*/[\s]+@include.*[\s]+/\*[a-z0-9]{3,5}\*/~');
/*define('FS_PREG','~^<\?php\s+\$[a-z0-9]{2,8}.*?\{\s*eval\s*\(.*?\}\s*\?>\s*~i');*/
/*define('FS_PREG',"~@?preg_replace\s*\(('|\")/\(?\.\*\)?/e('|\").*?\)\s*;~i");*/
/*define('FS_PREG','/<\?php\s+\#[a-z0-9]{6}\#.*?[a-z0-9]{6}\#\s+\?>/is');*/
/*define('FS_PREG','/\#[a-z0-9]{6}\#.*?\#\/[a-z0-9]{6}\#/is');*/
/*define('FS_PREG','~\$[a-z0-9]{4,9}=.*?,\s"[a-z0-9\]{250,4000}",\s"[a-z0-9]{5,25}"\);~i');*/
/*define('FS_PREG','/<\?php\s+\$sF.*?\)\);\}\?>/is');*/
/*define('FS_PREG','/<\?php\s+\$qV\=.*?\]\);\}\?>/is');*/

//список расширений файлов которые будут сканиться.
//define('SCAN_EXT', 'php,jpg,gif,png,html,inc,tpl,htaccess');

//словари

//без учета регистра
define('SCAN_SIGN', '');

//с учетом регистра (match case) пока не работает
define('SCAN_SIGN_CASE', '.chr(101).;\x47\;\x65\x76\;$GLOBALS;HTTP_USER_AGENT;$_FILES;$_COOKIE;$_POST;$_REQUEST');

// semicolon - separated dir mask, etc: */fscure/*;*/docs_*
//пока не работает
define('DIRS_EXCLUDE','');

//experimental option
//dirs separated \n
define('PATH_REQUIRED','');

//true not recommended
define('FILES_ONLY_PHP',false);
define('PHP_EXT','.php;.phtml;htaccess;.ico');


//text if you need to  file replaced but not removed
define('DEL_REPLACE','');

define('DIR_FSCURE', rtrim(getcwd(), DIRECTORY_SEPARATOR));
define('FILE_LST', DIR_FSCURE.DIRECTORY_SEPARATOR.'fscure.lst');
define('FILE_VIR', DIR_FSCURE.DIRECTORY_SEPARATOR.'vir.txt');
define('FILE_REPLACE', DIR_FSCURE.DIRECTORY_SEPARATOR.'patch.txt');
define('URL','http://'.$_SERVER['HTTP_HOST']. $_SERVER['PHP_SELF']);