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
     * @var stdClass Comment record from comment api
     */
    public $commentrecord;

    /**
     * @var context
     */
    protected $context;

    /**
     * @var int
     */
    protected $guserid;

    /**
     * @var int
     */
    protected $gareaid;

    /**
     * @param stdClass|null  $commentrecord
     */
    public function __construct(stdClass $commentrecord = null) {
        $this->commentrecord = $commentrecord;
    }

    /**
     * @return int
     */
    public function get_id() {
        return $this->commentrecord->id;
    }

    /**
     * @return string
     */
    public function get_content() {
        return $this->commentrecord->content;
    }

    /**
     * @param string $content
     */
    public function set_content($content) {
        $this->commentrecord->content = $content;
    }

    /**
     * @return int
     */
    public function get_timecreated() {
        return $this->commentrecord->timecreated;
    }

    /**
     * @return string
     */
    public function get_avatar() {
        return $this->commentrecord->avatar;
    }

    /**
     * @return string
     */
    public function get_user_fullname() {
        return $this->commentrecord->fullname;
    }

    /**
     * @return string
     */
    public function get_user_profileurl() {
        return $this->commentrecord->profileurl;
    }

    /**
     * @return bool
     */
    public function can_delete() {
        return !empty($this->commentrecord->delete);
    }

    /**
     * @return string
     */
    public function get_dateformat() {
        return $this->commentrecord->strftimeformat;
    }

    /**
     * @param $context
     */
    public function set_context($context) {
        $this->context = $context;
    }

    /**
     * @return mixed - context
     */
    public function get_context() {
        return $this->context;
    }

    /**
     * @return int
     */
    public function get_guserid() {
        return $this->guserid;
    }

    /**
     * @return mixed
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
     * @param int $gareaid
     */
    public function set_gareaid($gareaid) {
        $this->gareaid = $gareaid;
    }

}