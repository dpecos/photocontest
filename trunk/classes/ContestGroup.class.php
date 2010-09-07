<?php
/** 
 * @package np-admin
 * @version 20090624
 * 
 * @author Daniel Pecos Martínez
 * @copyright Copyright (c) Daniel Pecos Martínez 
 * @license http://www.gnu.org/licenses/lgpl.html  LGPL License
 */
global $photocontest_ddbb;

$photocontest_ddbb->addTable("ContestGroup", "contests_groups");
$photocontest_ddbb->addField("ContestGroup", "contestId", "contest_id", "INT", array("PK" => true, "NULLABLE" => false));
$photocontest_ddbb->addField("ContestGroup", "groupId", "group_id", "INT", array("PK" => true, "NULLABLE" => false));

class ContestGroup {
   public function __construct($data = null) {     
      global $photocontest_ddbb;
      $photocontest_ddbb->loadData($this, $data);
   }
   
   public function store() {
      global $photocontest_ddbb;
      $photocontest_ddbb->insertObject($this);
      return true;
   }
   
   public function delete() {
	   global $photocontest_ddbb;
	   $sql_1 = "DELETE FROM ".$photocontest_ddbb->getTable('ContestGroup')." WHERE ".$photocontest_ddbb->getMapping('ContestGroup','contestId')." = ".NP_DDBB::encodeSQLValue($this->contest_contestId, $photocontest_ddbb->getType('ContestGroup','contestId'))." AND ".$photocontest_ddbb->getMapping('ContestGroup','groupId')." = ".NP_DDBB::encodeSQLValue($this->groupId, $photocontest_ddbb->getType('ContestGroup','groupId'));
	   return ($photocontest_ddbb->executeDeleteQuery($sql_1) > 0);
   }
}
?>
