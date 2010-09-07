<?
global $photocontest_ddbb;

include("common.php");

global $app;
$app = new Application(array("name" => "PhotoContest", "version" => "0.1", "url" => "http://danielpecos.com/projects/photocontest", "author" => "Daniel Pecos Martinez (contact@danielpecos.com)"));
	
$app->ddbb = $photocontest_ddbb;
$app->rols = array("#r1" => array("rol_name" => "PhotoContest Admin", "description" => "PhotoContest admin's rol"));
$app->groups = array("#g1" => array("group_name" => "PhotoContest Admins", "description" => "PhotoContest admin's group", "rols" => array("#r1")));
$app->panels = array("#p1" => array("id" => "photocontest-main", "title" => "PhotoContest", "rols" => array("#r1")));
$app->menus = array("#m1" => array("parent_id" => 0, "order" => 0, "url" => "../photocontest/admin/index.php", "panel_id" => "#p1"));
?>

