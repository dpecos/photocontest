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

$photocontest_ddbb->addTable("Photo", "photos");
$photocontest_ddbb->addField("Photo", "photoId", "photo_id", "INT", array("PK" => true, "NULLABLE" => false, "AUTO_INCREMENT" => true));
$photocontest_ddbb->addField("Photo", "userId", "user_id", "INT", array("NULLABLE" => false));
$photocontest_ddbb->addField("Photo", "contestId", "contest_id", "INT", array("NULLABLE" => false));
$photocontest_ddbb->addField("Photo", "title", "title", "STRING", array("NULLABLE" => false, "LENGTH" => 100));
$photocontest_ddbb->addField("Photo", "description", null, "TEXT", array("NULLABLE" => true, "DEFAULT" => null));
$photocontest_ddbb->addField("Photo", "exif", null, "DATA", array("NULLABLE" => true, "DEFAULT" => null));
$photocontest_ddbb->addField("Photo", "uploadDate", "upload_date", "DATE", array("NULLABLE" => true, "DEFAULT" => "CURRENT_TIMESTAMP"));

class Photo {
    public function __construct($data = null) {     
        global $photocontest_ddbb;
        $photocontest_ddbb->loadData($this, $data);
    }

    public static function loadPhotoId($id) {
        global $photocontest_ddbb;
        $data = $photocontest_ddbb->executePKSelectQuery("SELECT * FROM ".$photocontest_ddbb->getTable('Photo')." WHERE ".$photocontest_ddbb->getMapping('Photo','photoId')."=".NP_DDBB::encodeSQLValue($id, $photocontest_ddbb->getType('Photo','photoId')));
        if ($data != null)
            return new Photo($data);
        else
            return null;
    }

    public function store() {
        global $photocontest_ddbb;
        $this->photoId = $photocontest_ddbb->insertObject($this);
        return true;
    }

    public function update() {
        global $photocontest_ddbb;
        $photocontest_ddbb->updateObject($this);
        return true;
    }

    public function delete() {
        global $photocontest_ddbb;
        $sql_1 = "DELETE FROM ".$photocontest_ddbb->getTable('Photo')." WHERE ".$photocontest_ddbb->getMapping('Photo','photoId')." = ".NP_DDBB::encodeSQLValue($this->photoId, $photocontest_ddbb->getType('Photo','photoId'));
        return ($photocontest_ddbb->executeDeleteQuery($sql_1) > 0);
    }

    public function getUrl() {
        return sprintf("photos/%04s_%07s.jpg", $this->contestId, $this->photoId);
    }

    public function getThumbUrl() {
        return sprintf("photos/%04s_%07s_thumb.jpg", $this->contestId, $this->photoId);
    }

    /*public function getVotes($userId) {
        if ($userId != null) {  
            $rating = Rating::loadRating($userId, $this->photoId);
            if ($rating != null)
                return $rating->rating;
            else
                return 0;
        } else {
            $contest = Contest::loadContest($this->contestId);
            return $contest->photoRatings($this->photoId);
        }
    }*/
}
?>
