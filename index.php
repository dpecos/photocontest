<?
require_once("common.php");
require_once($NPADMIN_PATH."API.php");

$contestId = $_GET['contest_id'];
$contest = Contest::loadContest($contestId);

if ($contest->status !== STATUS_FINISHED) {
    npadmin_security($contest->getAllowedGroups(), false, _("You are not invited to this contest"), true);
    $userId = npadmin_loginData()->getUser()->userId;    
} else {
    $userId = null;
}

if ($contest->status === STATUS_OPEN)
    $photos = $contest->photosOfUser($userId);
else {
    $photos = $contest->photosOfUser(null);
    if ($contest->status === STATUS_VOTING)
        shuffle($photos);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" /> 
        <style type="text/css" media="all">
        @import url("static/style.css");
        </style>
        
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/base/jquery-ui.css" type="text/css" />

        <script type="text/javascript" src="static/jquery_plugins/jquery_form_plugin/jquery.form.js"></script>
        <script type="text/javascript" src="static/jquery_plugins/jquery_fancybox/jquery.fancybox-1.3.1.pack.js"></script>
        <script type="text/javascript" src="static/jquery_plugins/jquery_fancybox/jquery.easing-1.3.pack.js"></script>
        <script type="text/javascript" src="static/jquery_plugins/jquery_fancybox/jquery.mousewheel-3.0.2.pack.js"></script>
        <link rel="stylesheet" href="static/jquery_plugins/jquery_fancybox/jquery.fancybox-1.3.1.css" type="text/css" media="screen" />
        <script type="text/javascript" src="http://dev.jquery.com/view/trunk/plugins/validate/jquery.validate.js"></script> 

        <script src='static/jquery_plugins/jquery_star_rating/jquery.MetaData.js' type="text/javascript" language="javascript"></script>
        <script src='static/jquery_plugins/jquery_star_rating/jquery.rating.js' type="text/javascript" language="javascript"></script>
        <link href='static/jquery_plugins/jquery_star_rating/jquery.rating.css' type="text/css" rel="stylesheet"/>


        <script type="text/javascript" src="<?= $NPADMIN_PATH ?>/public/js/np-lib/nplib_string.js"></script>

        <script>
            // wait for the DOM to be loaded 
            $(document).ready(function() { 

<? if ($contest->status === STATUS_OPEN) { ?>
                $('#upload_form').ajaxForm({
                    beforeSubmit: function() {
                        return $('#upload_form').valid();
                    },
                    success: function(responseText) { 
                        $('#upload_form').resetForm();
                        if (responseText.startsWith("OK")) {
                            window.location.reload();
                        } else
                            alert(responseText);
                    }
                }); 

                validating_options = {
                    rules: {
                        title: "required",    
                        description: "required",    
                        photoFile: "required"    
                    },
                    messages: {
                        title: "<?= _("Please, enter a title for your photo.") ?>",
                        description: "<?= _("Please, enter a description for your photo.") ?>",
                        photoFile: "<?= _("Please, select a file to upload.") ?>"
                    }
                };

                $("#upload_form").validate(validating_options);

                $('.actions .delete').bind('click',function(event){
                    event.preventDefault();
                    if (confirm('<?= _("Are you sure you want to delete this photo?") ?>')) {
                        $.get(this.href,{},function(responseText){ 
                            if (responseText.startsWith("OK")) {
                                window.location.reload();
                            } else
                                alert(responseText);
                        })  
                    }
                })

                $('.actions .edit').bind('click',function(event){
                    event.preventDefault();
                    id = event.target.id;

                    $('#edit_form [name=title]').val($("#photo_title_" + id).text());
                    $('#edit_form [name=description]').val($("#photo_description_" + id).text());
                    $('#edit_form [name=photoId]').val(id);
                    
                    $('#edit_form_dialog').removeClass('invisible');
                    $('#edit_form_dialog').dialog('open');
                    $('#edit_form').valid();
                })

                $('#edit_form_dialog').dialog({
                    autoOpen:false,
                    title: "<?= _("Edit photo") ?>",
                    height: 230,
                    width: 350,
                    modal: true,
                    buttons: {
                        <?= _("Edit") ?>: function() {
                            if ($('#edit_form').valid()) {
                                $('#edit_form').submit();    
                                $(this).dialog('close');
                            }
                        },
                        <?= _("Cancel") ?>: function() {
                            $('#edit_form').resetForm();
                            $(this).dialog('close');
                        }
                    }
                });

                $('#edit_form').ajaxForm(function(responseText) {
                    $('#edit_form').resetForm();
                    if (responseText.startsWith("OK")) {
                        window.location.reload();
                    } else
                        alert(responseText);
                });

                $("#edit_form").validate(validating_options);
<? } ?>

<? if ($contest->status === STATUS_VOTING) { ?>
                photoVotes = {};
                <? foreach ($photos as $photo) { ?>
                photoVotes["star_<?= $photo->photoId ?>"] = <?= $contest->photoRatings($photo->photoId, $userId) ?>;
                <? } ?>
                maxVotes = <?= $contest->numberVotes ?>;
            
                function countVotes() {
                    total = 0;
                    for (key in photoVotes) {
                        total += parseInt(photoVotes[key], 10);
                    }
                    return total;
                }

                $(".auto-submit-star").rating({
                    callback: function (value, element) {
                        debugger;
                        id = element.parentNode.id;
                        if (this.nodeName == "DIV") {
                            id = this.nextSibling.id;
                        }
                        if (value == undefined)
                            value = 0;
                        else 
                            value = parseInt(value, 10);

                        oldVote = parseInt(photoVotes['star_' + id], 10);
                        if (oldVote != value) {
                            photoVotes['star_' + id] = value;
                            newTotalVotes = countVotes();

                            if (newTotalVotes > maxVotes) {
                                photoVotes['star_' + id] = oldVote;
                                $(".auto-submit-star[name='star_" + id + "']").rating('select', oldVote + "", false); // false does not fire callback
                                alert("<?= _('Maximun number of votes for this contest achieved. You should rethink your votes ;-)') ?>");
                            } else {
                                postData = "op=vote&contest_id=<?= $contestId ?>&user_id=<?= $userId ?>&photo_id=" + id + "&rating=" + value;
                                $.ajax({
                                    type: 'post',
                                    async: true,
                                    url: 'ajax/photo.php',
                                    data: postData,
                                    success: function(response) {
                                        if (!response.startsWith("OK")) {
                                            photoVotes['star_' + id] = oldVote;
                                            $(".auto-submit-star[name='star_" + id + "']").rating('select', oldVote + "", false); // false does not fire callback
                                            alert(response)
                                        }
                                    },
                                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                                        photoVotes['star_' + id] = oldVote;
                                        $(".auto-submit-star[name='star_" + id + "']").rating('select', oldVote + "", false); // false does not fire callback
                                        alert("ERROR - Ajax: " + textStatus);
                                    }
                                });
                            }
                        }
                    }
                });
<? } ?>

<? if ($contest->status === STATUS_OPEN || $contest->status === STATUS_FINISHED) { ?>
                $(".photo_exif_view").bind('click', function(src) {
                        id = src.target.id;
                        $("#photo_exif_"+id).removeClass("invisible");
                        $("#photo_exif_"+id).dialog({title:"Exif"});
                    }
                );
<? } ?>

<? if ($contest->status === STATUS_FINISHED) { ?>
                //$(".star").rating({split: '4'});
<? } ?>

                $('.photo').fancybox();

             }); 

        </script>

    </head>

    <body>

<div class="title"><a href="http://danielpecos.com/projects/photocontest">PhotoContest</a> - <?= _("UPLOAD PHOTO") ?></div>
<div class="subtitle"><?= $contest->title ?><div class="description"><?= $contest->description ?></div></div>

<? if ($contest->status === STATUS_OPEN) { ?>

<?
if (count($photos) < $contest->numberPhotos || $contest->numberPhotos == 0) {
?>
<div class="actions_box">
    <div class="upload_box">
        <form id="upload_form" action="ajax/photo.php" method="post" enctype="multipart/form-data">
        <table>
            <tr><td><label for="title"><?= _("Title") ?>:</label></td><td><input type="text" name="title" id="title"/></td></tr>
            <tr><td><label for="description"><?= _("Description") ?>:</label></td><td><input type="text" name="description" id="description"/></td></tr>
            <tr><td><label for="photoFile"><?= _("Photo") ?>:</label></td><td><input type="file" name="photoFile" id="photoFile"/></td></tr>
        </table>
        <input type="submit" value="<?= _('Upload Photo') ?>"/>
        <input type="hidden" name="op" value="upload"/>
        <input type="hidden" name="contest_id" value="<?= $contestId ?>"/>
        <input type="hidden" name="MAX_FILE_SIZE" value="10000000" /> <!-- 10MB -->
        </form>
    </div>
</div>
<? } ?>

<? } ?>

<div class="album">
<?
$participants = array();
foreach ($photos as $photo) {
?>
    <a name="photo_<?= $photo->photoId ?>" />
    <div class="photo_frame">
        
    <? if ($contest->status === STATUS_VOTING) { ?>
        <a class="photo" rel="gallery" href="<?= $photo->getUrl() ?>"><img src="<?= $photo->getThumbUrl() ?>"/></a>
    <? } else { ?>
        <!--a class="photo" rel="gallery_<?= $userId ?>" href="<?= $photo->getUrl() ?>"><img src="<?= $photo->getThumbUrl() ?>" alt="<?= $photo->title ?>"/></a-->
        <a class="photo" rel="gallery" href="<?= $photo->getUrl() ?>"><img src="<?= $photo->getThumbUrl() ?>" alt="<?= $photo->title ?>"/></a>
    <? } ?>

    <? if ($contest->status === STATUS_FINISHED) { 
        $votes = $contest->photoRatings($photo->photoId);
    ?>
        <div>
            <?
            $result = $votes['total_votes'] / $votes['number_people'];
            for ($i = 1; $i <= $contest->maxNumberVotes * 4; $i++) { 
                $checked = ($i*0.25 <= $result && ($i+1)*0.25 > $result);
            ?>
            <input name="star_<?= $photo->photoId ?>" id="<?= $photo->photoId ?>" type="radio" class="star {split:4}" value="<?= $i ?>" disabled="disabled" <?= $checked ? "checked='checked'" : "" ?>/>
            <?
            }
            ?>
        </div>
    <? } ?>

    <? if ($contest->status === STATUS_OPEN || $contest->status === STATUS_FINISHED) { ?>

        <div class="info">
            <center>
            <table>
                <? if ($contest->status === STATUS_FINISHED) { 
                        $participants[$photo->userId] = npadmin_user($photo->userId)->user;
                ?>
                <tr><td><span class="label"><?= _("Owner") ?>:</span></td><td><span id="photo_owner_<?= $photo->photoId ?>"><?= $participants[$photo->userId] ?></span></td></tr>
                <? } ?>
                <tr><td><span class="label"><?= _("Title") ?>:</span></td><td><span id="photo_title_<?= $photo->photoId ?>"><?= $photo->title ?></span></td></tr>
                <tr><td><span class="label"><?= _("Description") ?>:</span></td><td><span id="photo_description_<?= $photo->photoId ?>"><?= $photo->description ?></span></td></tr>
            </table>
            </center>
            <img class="photo_exif_view" id="<?= $photo->photoId ?>" alt="Exif" src="static/img/info.gif"/>
            <div class="photo_exif invisible" id="photo_exif_<?= $photo->photoId ?>">
                <span class="label"><?= _("Camera") ?>:</span> <?= $photo->exif['Make']." - ".$photo->exif['Model'] ?><br/>
                <span class="label"><?= _("ISO") ?>:</span> <?= $photo->exif['ISOSpeedRatings'] ?><br/>
                <span class="label"><?= _("Aperture") ?>:</span> f <?= $photo->exif['ApertureValue'] ?><br/>
                <span class="label"><?= _("Exposure") ?>:</span> <?= $photo->exif['ExposureTime'] ?> <?=_ ("sec.") ?><br/>
                <span class="label"><?= _("Date") ?>:</span> <?= $photo->exif['DateTimeOriginal'] ?><br/>
            </div>
        </div>

    <? } ?> 

    <? if ($contest->status === STATUS_OPEN) { ?>
        <div class="actions">
            <a class="delete" href="ajax/photo.php?op=delete&contest_id=<?= $contestId ?>&photoId=<?= $photo->photoId ?>" class="inline"><img src="static/img/trash.png" /></a>
            <a class="edit" href="#edit_form"><img id="<?= $photo->photoId ?>" src="static/img/pencil.png" /></a>
        </div>
    <? } ?>

    <? if ($contest->status === STATUS_VOTING) { ?>
        <div>
            <? 
            $votes = $contest->photoRatings($photo->photoId, $userId);
            for ($i = 1; $i <= $contest->maxNumberVotes; $i++) { 
                $disabled = ($photo->userId === $userId);
                $checked = ($i === $votes && !$disabled)
            ?>
    
            <input name="star_<?= $photo->photoId ?>" id="<?= $photo->photoId ?>" type="radio" class="auto-submit-star" value="<?= $i ?>" <?= $checked ? "checked='checked'" : "" ?> <?= $disabled ? "disabled='disabled'" : "" ?>/>
            <? } ?>
        </div>
    <? } ?>

    <? if ($contest->status === STATUS_FINISHED) { ?>
        <div class="votes">
            <center>
            <table>
                <tr><td><span class="label"><?= _("Total votes") ?></span></td><td class="number"><?= $votes['total_votes'] ?></td></tr>
                <tr><td><span class="label"><?= _("People who voted for it") ?></span></td><td class="number"><?= $votes['number_people'] ?></td></tr>
                <tr><td><span class="label"><?= _("Final result") ?></span></td><td class="number"><?= sprintf(_("%.2f"), $result) ?></td></tr>
            </table>
            </center>
        </div>
    <? } ?>
    </div>
<? } ?>
</div>


<? if ($contest->status === STATUS_FINISHED) { ?>
    <div class="title"><?= _("Photos by User") ?></div>
    <?
    NP_asorti($participants);
    foreach ($participants as $participantId => $participantName) { 
    ?>
    <div class="subtitle"><?= $participantName ?></div>
    <div class="album">
            <? 
            foreach ($photos as $photo) { 
                if ($photo->userId == $participantId) {
            ?>
                <div class="photo_frame photo_small">
                    <a rel="gallery_<?= $participantId ?>" href="#photo_<?= $photo->photoId ?>"><img src="<?= $photo->getThumbUrl() ?>" alt="<?= $photo->title ?>"/></a>
                    <?
                    $votes = $contest->photoRatings($photo->photoId);
                    ?>
                    <div>
                        <?
                        $result = $votes['total_votes'] / $votes['number_people'];
                        for ($i = 1; $i <= $contest->maxNumberVotes * 4; $i++) { 
                            $checked = ($i*0.25 <= $result && ($i+1)*0.25 > $result);
                        ?>
                        <input name="star_participant_<?= $photo->photoId ?>" id="participant_<?= $photo->photoId ?>" type="radio" class="star {split:4}" value="<?= $i ?>" disabled="disabled" <?= $checked ? "checked='checked'" : "" ?>/>
                        <?
                        }
                        ?>
                    </div>
                </div>
            <? 
                }
            } 
            ?>
    </div>
    <? } ?>
<? } ?>

<? if ($contest->status === STATUS_OPEN) { ?>

<div id="edit_form_dialog" class="invisible">
    <form id="edit_form" action="ajax/photo.php" method="post">
        <table>
            <tr><td><label for="edit_photo_title"><?= _("Title") ?>:</label></td><td><input type="text" id="edit_photo_title" name="title"/></td></tr>
            <tr><td><label for="edit_photo_description"><?= _("Description") ?>:</label></td><td><input type="text" id="edit_photo_description" name="description"/></td></tr>
        </table>
        <input type="hidden" name="op" value="edit"/>
        <input type="hidden" name="contest_id" value="<?= $contestId ?>"/>
        <input type="hidden" name="photoId" value=""/>
    </form>        
</div>

<? } ?>

<div class="copyright">
    <a href="http://danielpecos.com/projects/photocontest">PhotoContest <?= $VERSION ?></a> &copy; 2010 Daniel Pecos Mart&iacute;nez
</div>

    </body>
</html>
