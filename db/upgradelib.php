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

    /**
     * @var local_joulegrader_comment_loader
     */
    protected $commentloader;

    public function __construct($gradeareahelper = null, $commentloader = null) {
        if (is_null($gradeareahelper)) {
            global $CFG;
            require_once($CFG->dirroot. '/local/joulegrader/helper/gradingareas.php');
            $gradeareahelper = new local_joulegrader_helper_gradingareas();
        }
        $this->gradeareahelper = $gradeareahelper;

        if (is_null($commentloader)) {
            $commentloader = new local_joulegrader_comment_loader();
        }
        $this->commentloader = $commentloader;
    }

    /**
     * Upgrades all the Joule Grader comments that are supported by the core comment api.
     */
    public function upgrade() {
        global $CFG;
        require_once($CFG->dirroot . '/comment/lib.php');

        $gradeareahelper = $this->gradeareahelper;
        $commentupgrader = null;
        $gareaid = 0;
        $guserid = 0;
        while ($rs = $this->commentloader->load()) {
            foreach ($rs as $crecord) {
                try {
                    if (!$commentupgrader instanceof local_joulegrader_comment_upgrader or
                            (($crecord->gareaid != $gareaid) or ($crecord->guserid != $guserid))) {

                        /**
                         * @var local_joulegrader_lib_gradingarea_abstract $gradingarea
                         */
                        $gradingarea = $gradeareahelper::get_gradingarea_instance($crecord->gareaid, $crecord->guserid);
                        $commentapi  = new comment($gradingarea->get_comment_info());

                        $commentupgrader = new local_joulegrader_comment_upgrader($commentapi);

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
     * @var moodle_database
     */
    protected $db;


    public function __construct(comment $commentapi, $db = null) {
        $this->commentapi = $commentapi;

        if (is_null($db)) {
            global $DB;
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

        $this->db->insert_record('comments', (object) $newcomment);
    }
}

/**
 * Class local_joulegrader_comment_loader
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_comment_loader {

    /**
     * @var moodle_database
     */
    protected $db;

    /**
     * @var int
     */
    protected $limitfrom = 0;

    /**
     * @const int
     */
    const LIMITNUM = 500;

    public function __construct($db = null) {
        if (is_null($db)) {
            global $DB;

            $this->db = $DB;
        }
    }

    /**
     * @return moodle_recordset|false
     */
    public function load() {
        $select = 'deleted IS NULL';
        $rs = $this->db->get_recordset_select('local_joulegrader_comments', $select, array(),
                'gareaid ASC, guserid ASC', '*', $this->limitfrom, self::LIMITNUM);

        $this->limitfrom += self::LIMITNUM;

        if ($rs->valid()) {
            return $rs;
        }

        return false;
    }
}