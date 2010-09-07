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

$photocontest_ddbb->addTable("Contest", "contests");
$photocontest_ddbb->addField("Contest", "contestId", "contest_id", "INT", array("PK" => true, "NULLABLE" => false, "AUTO_INCREMENT" => true));
$photocontest_ddbb->addField("Contest", "title", "title", "STRING", array("NULLABLE" => false, "LENGTH" => 100));
$photocontest_ddbb->addField("Contest", "description", null, "TEXT", array("NULLABLE" => true, "DEFAULT" => null));
$photocontest_ddbb->addField("Contest", "numberPhotos", "number_photos", "INT", array("NULLABLE" => false, "DEFAULT" => "0"));
$photocontest_ddbb->addField("Contest", "numberVotes", "number_votes", "INT", array("NULLABLE" => false, "DEFAULT" => "5"));
$photocontest_ddbb->addField("Contest", "maxNumberVotes", "max_number_votes", "INT", array("NULLABLE" => false, "DEFAULT" => "3"));
$photocontest_ddbb->addField("Contest", "status", null, "STRING", array("NULLABLE" => true, "LENGTH" => 15, "DEFAULT" => "NEW"));
$photocontest_ddbb->addField("Contest", "creationDate", "creation_date", "DATE", array("NULLABLE" => true, "DEFAULT" => "CURRENT_TIMESTAMP"));
$photocontest_ddbb->addField("Contest", "openingDate", "opening_date", "DATE", array("NULLABLE" => true));
$photocontest_ddbb->addField("Contest", "votingDate", "voting_date", "DATE", array("NULLABLE" => true));
$photocontest_ddbb->addField("Contest", "closingDate", "closing_date", "DATE", array("NULLABLE" => true));

class Contest {
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
        $sql_1 = "DELETE FROM ".$photocontest_ddbb->getTable('Contest')." WHERE ".$photocontest_ddbb->getMapping('Contest','contestId')." = ".NP_DDBB::encodeSQLValue($this->contestId, $photocontest_ddbb->getType('Contest','contestId'));
        return ($photocontest_ddbb->executeDeleteQuery($sql_1) > 0);
    }

    public static function loadContest($id) {
        global $photocontest_ddbb;

        $data = $photocontest_ddbb->executePKSelectQuery("SELECT * FROM ".$photocontest_ddbb->getTable("Contest")." WHERE ".$photocontest_ddbb->getMapping('Contest','contestId')."=".NP_DDBB::encodeSQLValue($id, $photocontest_ddbb->getType('Contest','contestId')));
        $contest = new Contest($data);
        return $contest;
    }

    public function getAllowedGroups() {
        global $photocontest_ddbb;

        $authClass = npadmin_setting("NP-ADMIN", "AUTH");
        $authenticator = new $authClass;
        $allGroups = $authenticator->listGroups();

        $result = $photocontest_ddbb->executeSelectQuery("SELECT * FROM ".$photocontest_ddbb->getTable("ContestGroup")." WHERE ".$photocontest_ddbb->getMapping('Contest','contestId')."=".NP_DDBB::encodeSQLValue($this->contestId, $photocontest_ddbb->getType('Contest','contestId')));
        $group_ids = array();
        foreach ($result as $idx => $data) {
            $group_ids[] = $data['group_id'];
        }

        $groups = array();
        foreach ($allGroups as $idx => $group) {
            if (in_array($group->groupId, $group_ids))
                $groups[] = $group;
        }
        return $groups;
    }

    public function getAllowedContestsForUser($userId) {
    }

    public function checkDates() {
    }

    public function photosOfUser($userId) {
        global $photocontest_ddbb;

        $photos = array();

        if ($this->status !== STATUS_FINISHED) {
            $sql = "SELECT * FROM ".$photocontest_ddbb->getTable("Photo")." WHERE ".$photocontest_ddbb->getMapping('Photo','contestId')."=".NP_DDBB::encodeSQLValue($this->contestId, $photocontest_ddbb->getType('Photo','contestId'));

            if ($userId != null) {
                $sql .= " AND ".$photocontest_ddbb->getMapping('Photo','userId')."=".NP_DDBB::encodeSQLValue($userId, $photocontest_ddbb->getType('Photo','userId'));
            } 
        } else {
            //$sql = "SELECT p.*, SUM(r.rating)/count(*) as result FROM ".$photocontest_ddbb->getTable("Photo")." p LEFT JOIN ".$photocontest_ddbb->getTable("Rating")." r ON r.".$photocontest_ddbb->getMapping('Rating','photoId')."=p.".$photocontest_ddbb->getMapping('Photo','photoId')." WHERE ".$photocontest_ddbb->getMapping('Photo','contestId')."=".NP_DDBB::encodeSQLValue($this->contestId, $photocontest_ddbb->getType('Photo','contestId'))." GROUP BY p.".$photocontest_ddbb->getMapping('Photo','photoId')." ORDER BY result DESC, SUM(r.rating) DESC";
            $sql = "SELECT p.*, SUM(r.rating) as result, AVG(r.rating) as average FROM ".$photocontest_ddbb->getTable("Photo")." p LEFT JOIN ".$photocontest_ddbb->getTable("Rating")." r ON r.".$photocontest_ddbb->getMapping('Rating','photoId')."=p.".$photocontest_ddbb->getMapping('Photo','photoId')." WHERE ".$photocontest_ddbb->getMapping('Photo','contestId')."=".NP_DDBB::encodeSQLValue($this->contestId, $photocontest_ddbb->getType('Photo','contestId'))." GROUP BY p.".$photocontest_ddbb->getMapping('Photo','photoId')." ORDER BY result DESC, average DESC";
        }

        $result = $photocontest_ddbb->executeSelectQuery($sql);
        foreach ($result as $idx => $photoData) {
            $photos[]= new Photo($photoData);
        }
        return $photos;
    }

    /*public function userRatings($userId) {
        global $photocontest_ddbb;

        $sql = "SELECT SUM(r.".$photocontest_ddbb->getMapping('Rating','rating').") as total_votes, count(*) number_vostes FROM ".$photocontest_ddbb->getTable("Rating")." r JOIN ".$photocontest_ddbb->getTable("Photo")." p ON r.photo_id=p.photo_id WHERE r.user_id=".NP_DDBB::encodeSQLValue($userId, $photocontest_ddbb->getType('Rating','userId'))." AND p.contest_id=".NP_DDBB::encodeSQLValue($this->contestId, $photocontest_ddbb->getType('Photo','contestId'));
        $result = $photocontest_ddbb->executePKSelectQuery($sql);

        return $result;
    }*/

    public function photoRatings($photoId, $userId = null) {
        global $photocontest_ddbb;

        $result = null;

        if ($userId !== null) {

            $rating = Rating::loadRating($userId, $photoId);
            if ($rating != null)
                return $rating->rating;
            else
                return 0;

        } else {

            $sql = "SELECT SUM(r.".$photocontest_ddbb->getMapping('Rating','rating').") as total_votes, count(*) number_people FROM ".$photocontest_ddbb->getTable("Rating")." r JOIN ".$photocontest_ddbb->getTable("Photo")." p ON r.photo_id=p.photo_id WHERE r.photo_id=".NP_DDBB::encodeSQLValue($photoId, $photocontest_ddbb->getType('Rating','photoId'))." AND p.contest_id=".NP_DDBB::encodeSQLValue($this->contestId, $photocontest_ddbb->getType('Photo','contestId'));
            $result = $photocontest_ddbb->executePKSelectQuery($sql);
            
            if ($result['total_votes'] === null)
                $result['total_votes'] = 0;
        }

        return $result;
    }

    public function vote($userId, $photoId, $rating) {
        $oldRating = Rating::loadRating($userId, $photoId);
        if ($oldRating != null)
            $oldRating->delete();

        if ($rating > 0) {
            $newRating = new Rating(array('user_id' => $userId, 'photo_id' => $photoId, 'rating' => $rating));
            $newRating->store();

            $votes = $this->userRatings($userId);
            if ($votes['total_votes'] > $this->numberVotes) {
                $newRating->delete();
                if ($oldRating != null)
                    $oldRating->store();
                return false; 
            } else {
                return true;
            }
        } else
            return true;

    }

}
?>
