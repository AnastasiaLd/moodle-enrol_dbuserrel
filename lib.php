<?php  // $Id$
/**
 * User role assignment plugin.
 *
 * This plugin synchronises user roles with external database table.
 *
 * @package    enrol
 * @subpackage dbuserrel
 * @copyright  Penny Leach <penny@catalyst.net.nz>
 * @copyright  Maxime Pelletier <maxime.pelletier@educsa.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class enrol_dbuserrel_plugin extends enrol_plugin {

    var $log;


    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param student $instance
     * @return bool
     */
    // the function below had been deprecated and replaced with new function name can_delete_instance
    public function can_delete_instance($instance) {
        if (!enrol_is_enabled('dbuserrel')) {
            return true;
        }
        if (!$this->get_config('dbtype') or !$this->get_config('dbhost') or !$this->get_config('remoteenroltable') or !$this->get_config('remotecoursefield') or !$this->get_config('remoteuserfield')) {
            return true;
        }

        //TODO: connect to external system and make sure no users are to be enrolled in this course
        return false;
    }

    /**
     * Does this plugin allow manual unenrolment of a specific user?
     * Yes, but only if user suspended...
     *
     * @param stdClass $instance course enrol instance
     * @param stdClass $ue record from user_enrolments table
     *
     * @return bool - true means user with 'enrol/xxx:unenrol' may unenrol this user, false means nobody may touch this user enrolment
     */
    public function allow_unenrol_user(stdClass $instance, stdClass $ue) {
        return true;
    }

	public function allow_unenrol(stdClass $instance) {
        // Simply making this function return true will render the unenrolment action in the participants list if the user has the 'enrol/pluginname:unenrol' capability.
        return true;
    }

/*
 * MAIN FUNCTION
 * For the given user, let's go out and look in an external database
 * for an authoritative list of relationships, and then adjust the
 * local Moodle assignments to match.
 * @param bool $verbose
 * @return int 0 means success, 1 db connect failure, 2 db read failure
 */
function setup_enrolments($verbose = false, &$user=null) {
    global $CFG, $DB;

    if ($verbose) {
      mtrace('Starting user enrolment synchronisation...');
    }

    // NOTE: if $this->db_init() succeeds you MUST remember to call
    // $this->enrol_disconnect() as it is doing some nasty vodoo with $CFG->prefix
    if ($verbose) {
	mtrace("Starting db_init()");
    }
    $extdb = $this->db_init();
    if (!$extdb) {
        error_log('Error: [ENROL_DBUSERREL] Could not make a connection');
        return;
    }

    // we may need a lot of memory here
    // the time limit statement below replaces the old @set_time_limit(0)
    core_php_time_limit::raise();
    raise_memory_limit(MEMORY_HUGE);

    /**
     * Local fields (local DB)
     */
    $flocalparent  = strtolower($this->get_config('localparentuserfield'));
    $flocalstudent   = strtolower($this->get_config('localstudentuserfield'));
    $flocalrole     = strtolower($this->get_config('localrolefield'));
    /**
     * Remote fields (remote DB)
     */
    $fremoteparent = strtolower($this->get_config('remoteparentuserfield'));
    $fremotestudent  = strtolower($this->get_config('remotestudentuserfield'));
    $fremoterole    = 'parentrole';
    $parentrole = strtolower($this->get_config('parentrole'));
    $currentacademicyear = strtolower($this->get_config('currentacademicyear'));
    $dbtable        = $this->get_config('remoteenroltable');


    /**
     * Get all entries from source(remote) table
     *
     * Added temporary field as the desired parent role for schema matching between remote and local db
     * Skip all rows where the academic year is not current
     * Skip all rows where a parent has not yet been allocated to a student
     *
     */

    $sql = "SELECT
                LOWER({$fremoteparent}) AS {$fremoteparent},
                LOWER({$fremotestudent}) AS {$fremotestudent},
                '{$parentrole}' AS $fremoterole
            FROM
                {$dbtable}
            WHERE
                  academic_year = '{$currentacademicyear}'";
    mtrace($sql);

	// Execute query to get entries from external DB
    if ($rs = $extdb->Execute($sql)) {

        if ($verbose) {
	    mtrace($rs->RecordCount()." entries in the external table");
        }

		// Unique identifier of the role assignment
        $uniqfield = $DB->sql_concat("r.$flocalrole", "'|'", "u1.$flocalparent", "'|'", "u2.$flocalstudent");

		// Query to retreive all user role assignment from Moodle that were made using this plugin only
        $sql = "SELECT $uniqfield AS uniq,
            ra.*, r.{$flocalrole} ,
            u1.{$flocalparent} AS parentid,
            u2.{$flocalstudent} AS studentid
            FROM {role_assignments} ra
            JOIN {role} r ON ra.roleid = r.id
            JOIN {context} c ON c.id = ra.contextid
            JOIN {user} u1 ON ra.userid = u1.id
            JOIN {user} u2 ON c.instanceid = u2.id
            WHERE ra.component = 'enrol_dbuserrel'
			AND c.contextlevel = " . CONTEXT_USER;
            //(!empty($user) ?  " AND c.instanceid = {$user->id} OR ra.userid = {$user->id}" : '');

		// Is there any role in Moodle?
		// The first column is used as the key
		if (!$existing = $DB->get_records_sql($sql)) {
			$existing = array();
        }

        if ($verbose) {
	    mtrace(sizeof($existing)." role assignement entries from dbuserrel found in Moodle DB");
        }

	// Is there something in the remote table?
        if (!$rs->EOF) {

            // MOODLE 1.X => $roles = $DB->get_records('role', array(), '', '', "$flocalrole, id");
	    $roles = $DB->get_records('role', array(), '', "$flocalrole, id", 0, 0);

            if ($verbose) {
	        mtrace(sizeof($roles)." role entries found in Moodle DB");
            }

            $parentusers = array(); // cache of mapping of localparentuserfield to mdl_user.id (for get_context_instance)
            $studentusers = array(); // cache of mapping of localparentuserfield to mdl_user.id (for get_context_instance)
            $contexts = array(); // cache

            $rels = array();

            // We loop through all the records of the remote table
            while ($row = $rs->FetchRow() ) {
		// Convert encoding if necessary
		//		$row = reset($row);
		$row = $this->db_decode($row);

                if ($verbose) {
                    print_r($row);
                    mtrace("Role:".$row[$fremoterole]);
                }

		// TODO: Handle coma seperated values in remotestudent field
                // either we're assigning ON the current user, or TO the current user
                $key = $row[$fremoterole] . '|' . $row[$fremoteparent] . '|' . $row[$fremotestudent];

				// Check if the role is already assigned
                if (array_key_exists($key, $existing)) {
                    // exists in moodle db already, unset it (so we can delete everything left)
                    unset($existing[$key]);
                    error_log("Warning: [$key] exists in moodle already");
                    continue;
                }

				// Check if the role from the remote table exist in Moodle
                if (!array_key_exists($row[$fremoterole], $roles)) {
                    // role doesn't exist in moodle. skip.
                    error_log("Warning: role " . $row[$fremoterole] . " wasn't found in moodle.  skipping $key");
                    continue;
                }

				// Fill the parent array
                if (!array_key_exists($row[$fremoteparent], $parentusers)) {
                    $parentusers[$row[$fremoteparent]] = $DB->get_field('user', 'id', array($flocalparent => $row[$fremoteparent]) );
                }

				// Check if parent exist in Moodle
                if ($parentusers[$row[$fremoteparent]] == false) {
                    error_log("Warning: [" . $row[$fremoteparent] . "] couldn't find parent user -- skipping $key");
                    // couldn't find user, skip
                    continue;
                }

				// Fill the student array
                if (!array_key_exists($row[$fremotestudent], $studentusers)) {
                    $studentusers[$row[$fremotestudent]] = $DB->get_field('user', 'id', array($flocalstudent => $row[$fremotestudent]) );
                }

				// Check if student exist in Moodle
                if ($studentusers[$row[$fremotestudent]] == false) {
                    // couldn't find user, skip
                    error_log("Warning: [" . $row[$fremotestudent] . "] couldn't find student user --  skipping $key");
                    continue;
                }

				// Get the context of the student
				$context = context_user::instance($studentusers[$row[$fremotestudent]]);
				if ($verbose) {
						mtrace("Information: [" . $row[$fremoteparent] . "] assigning " . $row[$fremoterole] . " to remote user " . $row[$fremoteparent]
						   . " on " . $row[$fremotestudent]);
				}

				// MOODLE 1.X => role_assign($roles[$row->{$fremoterole}]->id, $parentusers[$row->{$fremoteparent}], 0, $context->id, 0, 0, 0, 'dbuserrel');
				// MOODLE 2.X => role_assign($roleid, $userid, $contextid, $component = '', $itemid = 0, $timemodified = '')
				// This way of role assignment using the component name means that we cannot manually unassign this from UI
				//  We can only unassign using this same plugin. The unassign role statement is below and the same component name is used
				//
				role_assign($roles[$row[$fremoterole]]->id, $parentusers[$row[$fremoteparent]], $context->id, 'enrol_dbuserrel', 0, '');

            }

		if ($verbose) {
			mtrace("Deleting old role assignations");
		}
            // delete everything left in existing
            foreach ($existing as $key => $assignment) {
                if ($assignment->component == 'enrol_dbuserrel') {
                    if ($verbose) {
						mtrace("Information: [$key] unassigning $key");
					}
                    // MOODLE 1.X => role_unassign($assignment->roleid, $assignment->userid, 0, $assignment->contextid);
					role_unassign($assignment->roleid, $assignment->userid, $assignment->contextid, 'enrol_dbuserrel', 0);
                }
            }
        } else {
            error_log('Warning: [ENROL_DBUSERREL] Couldn\'t get rows from external db: '.$extdb->ErrorMsg(). ' -- no relationships to assign');
        }
    }
    $this->enrol_disconnect($extdb);
}

    /**
     * Tries to make connection to the external database.
     *
     * @return null|ADONewConnection
     */
    protected function db_init() {

        global $CFG;

        /* Control the casing of the retrieved recordsets'fields from remote db */

        define('ADODB_ASSOC_CASE', 0);

        require_once($CFG->libdir.'/adodb/adodb.inc.php');

        // Connect to the external database (forcing new connection)
        $extdb = ADONewConnection($this->get_config('dbtype'));
        if ($this->get_config('debugdb')) {
            $extdb->debug = true;
            ob_start(); //start output buffer to allow later use of the page headers
        }

        // the dbtype my contain the new connection URL, so make sure we are not connected yet
        if (!$extdb->IsConnected()) {
            $result = $extdb->Connect($this->get_config('dbhost'), $this->get_config('dbuser'), $this->get_config('dbpass'), $this->get_config('dbname'), true);
            if (!$result) {
                return null;
            }
        }

        $extdb->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($this->get_config('dbsetupsql')) {
            $extdb->Execute($this->get_config('dbsetupsql'));
        }

        return $extdb;
    }


/// DB Disconnect
function enrol_disconnect($extdb) {
    global $CFG;

    $extdb->Close();
}

    protected function db_addslashes($text) {
        // using custom made function for now
        if ($this->get_config('dbsybasequoting')) {
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace(array('\'', '"', "\0"), array('\\\'', '\\"', '\\0'), $text);
        } else {
            $text = str_replace("'", "''", $text);
        }
        return $text;
    }

    protected function db_encode($text) {
        $dbenc = $this->get_config('dbencoding');
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach($text as $k=>$value) {
                $text[$k] = $this->db_encode($value);
            }
            return $text;
        } else {
            return textlib::convert($text, 'utf-8', $dbenc);
        }
    }

    protected function db_decode($text) {
        $dbenc = $this->get_config('dbencoding');
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach($text as $k=>$value) {
                $text[$k] = $this->db_decode($value);
            }
            return $text;
        } else {
            return textlib::convert($text, $dbenc, 'utf-8');
        }
    }

} // end of class
