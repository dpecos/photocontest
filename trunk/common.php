<?
$VERSION = "0.1";

header("Expires: Sat, 01 Jan 2000 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!array_key_exists("app", $_GET)) {
    require_once("npadmin_config.php");
    if (isset($NPADMIN_REL_PATH))
        $NPADMIN_PATH = $NPADMIN_REL_PATH.$NPADMIN_PATH;
    require_once($NPADMIN_PATH."API.php");
}

global $photocontest_ddbb;
$photocontest_ddbb = new NP_DDBB($photocontest_ddbb_settings);

#require_once("classes/Contest.class.php");
#require_once("classes/ContestGroup.class.php");
#require_once("classes/Photo.class.php");
#require_once("classes/Rating.class.php");

// TODO: esta forma de incluir clases no funciona desde el ajax/photo.php
array_walk(glob('classes/*.class.php'), create_function('$v,$i', 'return require_once($v);')); 

define ("STATUS_NEW", "NEW");
define ("STATUS_OPEN", "OPEN");
define ("STATUS_VOTING", "VOTING");
define ("STATUS_FINISHED", "FINISHED");

bindtextdomain("messages", "i18n");
?>
