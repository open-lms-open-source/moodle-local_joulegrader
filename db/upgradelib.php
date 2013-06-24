<?php
/**
 * Upgrade classes and functions for joule Grader
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */

/**
 * Class local_joulegrader_comments_upgrader
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_comments_upgrader {

    /**
     * @var local_joulegrader_helper_gradingareas
     */
    protected $gradeareahelper;

    public function __construct($gradeareahelper = null) {
        if (is_null($gradeareahelper)) {
            global $CFG;
            require_once($CFG->dirroot. '/local/joulegrader/helper/gradingareas.php');
            $gradeareahelper = new local_joulegrader_helper_gradingareas();
        }
        $this->gradeareahelper = $gradeareahelper;
    }

    /**
     * Upgrades all the Joule Grader comments that are supported by the core comment api.
     *
     * @param int[] $idstoupgrade Array of local_joulegrader_comments.id
     * @param bool $requireids
     */
    public function upgrade($idstoupgrade = null, $requireids = false) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/comment/lib.php');

        // Sanity check for the restore process.
        if ($requireids and (empty($idstoupgrade) or !is_array($idstoupgrade))) {
            return;
        }

        $gradeareahelper = $this->gradeareahelper;
        $commentupgrader = null;
        $gareaid = 0;
        $guserid = 0;
        $fs = get_file_storage();

        $whereclause = 'deleted IS NULL';
        $params = array();
        if (!empty($idstoupgrade) and is_array($idstoupgrade)) {
            list($inorequalsql, $params) = $DB->get_in_or_equal($idstoupgrade);
            $whereclause .= " AND id $inorequalsql";
        }

        if ($rs = $DB->get_recordset_select('local_joulegrader_comments', $whereclause, $params,
            'gareaid ASC, guserid ASC')) {
            foreach ($rs as $crecord) {
                try {
                    if (!$commentupgrader instanceof local_joulegrader_comment_upgrader or
                            (($crecord->gareaid != $gareaid) or ($crecord->guserid != $guserid))) {

                        /**
                         * @var local_joulegrader_lib_gradingarea_abstract $gradingarea
                         */
                        $gradingarea = $gradeareahelper::get_gradingarea_instance($crecord->gareaid, $crecord->guserid);
                        $commentapi  = new comment($gradingarea->get_comment_info());

                        $commentupgrader = new local_joulegrader_comment_upgrader($commentapi, $fs);

                        // Update the current grading area and user.
                        $gareaid = $crecord->gareaid;
                        $guserid = $crecord->guserid;
                    }

                    // Do the upgrade.
                    $commentupgrader->upgrade($crecord);
                } catch (Exception $e) {
                    $expmsg = $e->getMessage();
                    debugging("Couldn't upgrade joule grader comment with id = $crecord->id. Exception message: $expmsg", DEBUG_ALL);
                    continue;
                }
            }

            $rs->close();
            unset($rs);
        }
    }
}

/**
 * Class local_joulegrader_comment_upgrader
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_comment_upgrader {

    /**
     * @var comment
     */
    protected $commentapi;

    /**
     * @var file_storage
     */
    protected $fs;

    /**
     * @var moodle_database
     */
    protected $db;


    public function __construct(comment $commentapi, file_storage $fs, $db = null) {
        global $DB;

        $this->commentapi = $commentapi;
        $this->fs = $fs;

        if (is_null($db)) {
            $db = $DB;
        }
        $this->db = $db;
    }

    /**
     * Upgrades a single
     *
     * @param stdClass $commentrecord local_joulegrader_comments record
     */
    public function upgrade(stdClass $commentrecord) {
        $newcomment = array(
            'contextid' => $this->commentapi->get_context()->id,
            'commentarea' => $this->commentapi->get_commentarea(),
            'itemid' => $this->commentapi->get_itemid(),
            'content' => $commentrecord->content,
            'format' => FORMAT_MOODLE,
            'userid' => $commentrecord->commenterid,
            'timecreated' => $commentrecord->timecreated,
        );

        if ($newid = $this->db->insert_record('comments', (object) $newcomment)) {
            if ($commentfiles = $this->fs->get_area_files($newcomment['contextid'], 'local_joulegrader', 'comment', $commentrecord->id)) {
                foreach ($commentfiles as $commentfile) {
                    $newfilerecord = new stdClass();
                    $newfilerecord->component = $this->commentapi->get_compontent();
                    $newfilerecord->filearea  = 'comments';
                    $newfilerecord->itemid    = $newid;

                    $this->fs->create_file_from_storedfile($newfilerecord, $commentfile);
                }
            }
        }
    }
}