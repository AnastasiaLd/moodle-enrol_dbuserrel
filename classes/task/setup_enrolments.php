<?php
namespace enrol_dbuserrel\task;

/**
 * Assigning the parent to the student
 */
class setup_enrolments extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('taskname', 'enrol_dbuserrel');
    }

    /* Sync */
    
    public function execute() {
      global $CFG;
      require_once($CFG->dirroot . '/enrol/dbuserrel/lib.php');
      $enrol = new \enrol_dbuserrel_plugin();
      $enrol->setup_enrolments();
    }
}
