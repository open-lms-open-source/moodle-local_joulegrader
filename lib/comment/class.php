<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * joule Grader comment class
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_lib_comment_class implements renderable {

    /**
     * @var int - id of the comment record
     */
    protected $id;

    /**
     * @var int - id of the grading_area record that the comment belongs to
     */
    protected $gareaid;


    /**
     * @var int - id of the user record for the work that comment belongs to
     */
    protected $guserid;

    /**
     * @var string - the content of the comment
     */
    protected $content;

    /**
     * @var int - id of user making the comment
     */
    protected $commenterid;

    /**
     * @var int - timestamp for when the comment was created
     */
    protected $timecreated;

    /**
     * @var int - is there an attachment
     */
    protected $attachment;

    /**
     * @var int - timestamp for when comment was deleted
     */
    protected $deleted;

    /**
     * @param mixed $record
     */
    public function __construct($record = null) {
        //if an object is passed try to set properties for this class
        if (!is_null($record) && is_object($record)) {
            //iterate through the record properties and try to set them in this class
            foreach ((array) $record as $property => $value) {
                if (property_exists($this, $property)) {
                    $this->$property = $value;
                }
            }
        }
    }

    /**
     * @param int $id
     */
    public function set_id($id) {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * @param int $gareaid
     */
    public function set_gareaid($gareaid) {
        $this->gareaid = $gareaid;
    }

    /**
     * @return int
     */
    public function get_gareaid() {
        return $this->gareaid;
    }

    /**
     * @param int $guserid
     */
    public function set_guserid($guserid) {
        $this->guserid = $guserid;
    }

    /**
     * @return int
     */
    public function get_guserid() {
        return $this->guserid;
    }

    /**
     * @param string $content
     */
    public function set_content($content) {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function get_content() {
        return $this->content;
    }

    /**
     * @param int $commenterid
     */
    public function set_commenterid($commenterid) {
        $this->commenterid = $commenterid;
    }

    /**
     * @return int
     */
    public function get_commenterid() {
        return $this->commenterid;
    }

    /**
     * @param int $timecreated
     */
    public function set_timecreated($timecreated) {
        $this->timecreated = $timecreated;
    }

    /**
     * @return int
     */
    public function get_timecreated() {
        return $this->timecreated;
    }

    /**
     * @param int $attachment
     */
    public function set_attachment($attachment) {
        $this->attachment = $attachment;
    }

    /**
     * @return int
     */
    public function get_attachment() {
        return $this->attachment;
    }

    /**
     * @param int $deleted
     */
    public function set_deleted($deleted) {
        $this->deleted = $deleted;
    }

    /**
     * @return int
     */
    public function get_deleted() {
        return $this->deleted;
    }

    /**
     * Save the comment
     */
    public function save() {
        global $DB;

        //build a record object based on the object vars currently set
        $properties = get_object_vars($this);

        //add non-null values to a record
        $record = new stdClass();
        foreach ($properties as $property => $value) {
            //check for null
            if (!is_null($value)) {
                $record->$property = $value;
            }
        }

        //check to see if id is set
        if (!empty($record->id)) {
            //this is an update
            $DB->update_record('local_joulegrader_comments', $record);
        } else {
            //add new record
            $this->id = $DB->insert_record('local_joulegrader_comments', $record);
        }
    }

    /**
     * Delete the comment
     */
    public function delete() {
        global $DB;

        //this should be called only after a comment has been loaded from the DB, id must be set
        if (empty($this->id)) {
            throw new coding_exception('delete() should not be called if the comment id is not set');
        }

        //record object for delete
        $record = new stdClass();
        $record->id = $this->id;
        $record->deleted = time();

        //set the class deleted
        $this->deleted = $record->deleted;

        //update the record with the deleted flag set
        $DB->update_record('local_joulegrader_comments', $record);
    }

    /**
     * @return bool
     */
    public function user_can_delete() {
        global $USER;

        //can delete if logged in user is commenter AND the id is set AND the comment is not already deleted
        $candelete = (($this->commenterid == $USER->id) && !empty($this->id) && empty($this->deleted));

        return $candelete;
    }

    /**
     * @return stdClass - user object for the commenter
     */
    public function get_commenter() {
        global $USER, $DB;

        //local non-persistant cache
        static $usercache = array();

        //check the logged in $USER
        if ($USER->id == $this->commenterid) {
            $commenter = $USER;
        //else check the local cache
        } else if (array_key_exists($this->commenterid, $usercache)) {
            $commenter = $usercache[$this->commenterid];
        //else just need to load it from the database and cache it
        } else {
            $commenter = $DB->get_record('user', array('id' => $this->commenterid), 'id, picture, firstname, lastname, imagealt, email', MUST_EXIST);
            $usercache[$commenter->id] = $commenter;
        }

        return $commenter;
    }
}