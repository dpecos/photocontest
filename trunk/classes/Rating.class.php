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

$photocontest_ddbb->addTable("Rating", "ratings");
$photocontest_ddbb->addField("Rating", "userId", "user_id", "INT", array("PK" => true, "NULLABLE" => false));
$photocontest_ddbb->addField("Rating", "photoId", "photo_id", "INT", array("PK" => true, "NULLABLE" => false));
$photocontest_ddbb->addField("Rating", "rating", null, "INT", array("NULLABLE" => false));
$photocontest_ddbb->addField("Rating", "ratingDate", "rating_date", "DATE", array("NULLABLE" => true, "DEFAULT" => "CURRENT_TIMESTAMP"));

class Rating {
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
        $sql_1 = "DELETE FROM ".$photocontest_ddbb->getTable('Rating')." WHERE ".$photocontest_ddbb->getMapping('Rating','userId')." = ".NP_DDBB::encodeSQLValue($this->userId, $photocontest_ddbb->getType('Rating','userId'))." AND ".$photocontest_ddbb->getMapping('Rating','photoId')." = ".NP_DDBB::encodeSQLValue($this->photoId, $photocontest_ddbb->getType('Rating','photoId'));
        return ($photocontest_ddbb->executeDeleteQuery($sql_1) > 0);
    }

    public static function loadRating($userId, $photoId) {
        global $photocontest_ddbb;
        $sql = "SELECT * FROM ".$photocontest_ddbb->getTable('Rating')." WHERE ".$photocontest_ddbb->getMapping('Rating','userId')." = ".NP_DDBB::encodeSQLValue($userId, $photocontest_ddbb->getType('Rating','userId'))." AND ".$photocontest_ddbb->getMapping('Rating','photoId')." = ".NP_DDBB::encodeSQLValue($photoId, $photocontest_ddbb->getType('Rating','photoId'));

        $data = $photocontest_ddbb->executePKSelectQuery($sql);
        if ($data != null)
            return new Rating($data);
        else 
            return null;


    }
}
?>
