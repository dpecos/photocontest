<?
global $NPADMIN_REL_PATH;
$NPADMIN_REL_PATH = "../";
require_once("../common.php");

$_POST = NP_UTF8_decode($_POST);

$userId = npadmin_loginData()->getUser()->userId;
$contestId = $_POST['contest_id'] !== null ? $_POST['contest_id'] : $_GET['contest_id'];

$contest = Contest::loadContest($contestId);

npadmin_security($contest->getAllowedGroups(), false, _("You are not invited to this contest")." (ID: ".$contestId.")", true);

if ($_POST['op'] == 'upload') {

    if ($_FILES['photoFile']['error'] === 0) {

        if ($contest->status === STATUS_OPEN) {

            $photos = $contest->photosOfUser($userId);

            if (count($photos) < $contest->numberPhotos) {

                $photo = new Photo();
                $photo->userId = $userId;
                $photo->contestId = $contestId;
                $photo->title = $_POST['title'];
                $photo->description = $_POST['description'];
                $photo->exif = read_exif_data($_FILES['photoFile']['tmp_name']);
                if ($photo->store()) {

                    $photo_filename = "../".$photo->getUrl();
                    $photo_filename_thumb = "../".$photo->getThumbUrl();

                    if(move_uploaded_file($_FILES['photoFile']['tmp_name'], $photo_filename)) {
                        $image = new NP_Image($photo_filename);

                        $image_resized = $image->resizeMaxSize(1024);
                        $image_resized->save($photo_filename);

                        $image_thumb = $image->resizeMaxSize(240);

                        if ($image_thumb->save($photo_filename_thumb))
                            echo "OK: ".$photo_filename;
                        else    
                            echo _("ERROR: Could not create thumbnail");

                        $image->close();
                        $image_resized->close();
                        $image_thumb->close();
                    } else{
                        echo sprintf(_("ERROR: Could not move the uploaded file (%s) to %s"), $_FILES['photoFile']['tmp_name'], $photo_filename);
                    }
                } else {
                    echo _("ERROR: Could not insert the photo in the database");
                }
            } else {
                echo _("ERROR: Maximun number of photos for this contest achieved");
            }
        } else {
            echo _("ERROR: Contest not open");
        }
    } else {
        switch ($_FILES['photoFile']['error'])
        {   
            case 1:
            print _('ERROR: The file is bigger than this PHP installation allows');
            break;
            case 2:
            print _('ERROR: The file is bigger than this form allows');
            break;
            case 3:
            print _('ERROR: Only part of the file was uploaded');
            break;
            case 4:
            print _('ERROR: No file was uploaded');
            break;
            default:
            echo _("ERROR: Unknown");
            break;
        }
    }

} else if ($_GET['op'] == "delete") {
    $photo = Photo::loadPhotoId($_GET['photoId']);
    if ($photo != null) {
        unlink("../".$photo->getUrl());
        unlink("../".$photo->getThumbUrl());
        if ($photo->delete())
            echo "OK";
        else
            echo "ERROR";
    } else
        echo "ERROR";

} else if ($_POST['op'] == "edit") {
    if ($contest->status === STATUS_OPEN) {
        $photo = Photo::loadPhotoId($_POST['photoId']);
        if ($photo->contestId == $contestId) {
            $photo = new Photo(); // para no sobreescribir fechas
            $photo->photoId = $_POST['photoId'];
            $photo->title = $_POST['title'];
            $photo->description = $_POST['description'];
            if ($photo->update())
                echo "OK";
            else
                echo "ERROR";
        } else {
            echo _("ERROR: This photo doesn't belong to the contest");
        }
    } else {
        echo _("ERROR: Not allowed to edit because of the contest status");
    }

} else if ($_POST['op'] == "vote") {
    if ($contest->status === STATUS_VOTING) {
        if ($userId == $_POST['user_id']) {
            if ($contest->vote($userId, $_POST['photo_id'], $_POST['rating']))
                echo "OK";
            else
                echo _("ERROR: Maximun number of votes for this contest achieved");
        } else {
            echo _("ERROR: Cheating?");
        }
    } else {
        echo _("ERROR: Not allowed to vote because of the contest status");
    }
}
?>
