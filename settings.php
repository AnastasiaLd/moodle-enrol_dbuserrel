<?php
/**
 * User role assignment plugin settings and presets.
 *
 * @package    enrol
 * @subpackage dbuserrel
 * @copyright  Penny Leach <penny@catalyst.net.nz>
 * @copyright  Maxime Pelletier <maxime.pelletier@educsa.org>
 * @copyright Madhu Avasarala
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * DO NOT USE mysql but use mysqli if using php7.x. The default has been changed and mysql option deleted
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
//--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_dbuserrel_settings', '', get_string('pluginname_desc', 'enrol_dbuserrel')));

    $settings->add(new admin_setting_heading('enrol_dbuserrel_exdbheader', get_string('settingsheaderdb', 'enrol_dbuserrel'), ''));

    $options = array('', "access","ado_access", "ado", "ado_mssql", "borland_ibase", "csv", "db2", "fbsql", "firebird", "ibase", "informix72", "informix", "mssql", "mssql_n", "mssqlnative", "mysqli", "mysqlt", "oci805", "oci8", "oci8po", "odbc", "odbc_mssql", "odbc_oracle", "oracle", "postgres64", "postgres7", "postgres", "proxy", "sqlanywhere", "sybase", "vfp");
    $options = array_combine($options, $options);
    $settings->add(new admin_setting_configselect('enrol_dbuserrel/dbtype', get_string('dbtype', 'enrol_dbuserrel'), get_string('dbtype_desc', 'enrol_dbuserrel'), 'oci8', $options));

    $settings->add(new admin_setting_configtext('enrol_dbuserrel/dbhost', get_string('dbhost', 'enrol_dbuserrel'), get_string('dbhost_desc', 'enrol_dbuserrel'), 'localhost'));

    $settings->add(new admin_setting_configtext('enrol_dbuserrel/dbuser', get_string('dbuser', 'enrol_dbuserrel'), '', ''));

    $settings->add(new admin_setting_configpasswordunmask('enrol_dbuserrel/dbpass', get_string('dbpass', 'enrol_dbuserrel'), '', ''));

    $settings->add(new admin_setting_configtext('enrol_dbuserrel/dbname', get_string('dbname', 'enrol_dbuserrel'), '', ''));

    $settings->add(new admin_setting_configtext('enrol_dbuserrel/dbencoding', get_string('dbencoding', 'enrol_dbuserrel'), '', 'utf-8'));

    $settings->add(new admin_setting_configtext('enrol_dbuserrel/remoteenroltable', get_string('remoteenroltable', 'enrol_dbuserrel'), get_string('remoteenroltable_desc', 'enrol_dbuserrel'), ''));

    $settings->add(new admin_setting_heading('enrol_dbuserrel_remoteheader', get_string('remote_fields_mapping', 'enrol_dbuserrel'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbuserrel/localparentuserfield', get_string('localparentuserfield', 'enrol_dbuserrel'), get_string('localparentuserfield_desc', 'enrol_dbuserrel'), 'idnumber'));

    $settings->add(new admin_setting_configtext('enrol_dbuserrel/localstudentuserfield', get_string('localstudentuserfield', 'enrol_dbuserrel'), get_string('localstudentuserfield_desc', 'enrol_dbuserrel'), 'idnumber'));

    $settings->add(new admin_setting_configtext('enrol_dbuserrel/localrolefield', get_string('localrolefield', 'enrol_dbuserrel'), get_string('localrolefield_desc', 'enrol_dbuserrel'), 'shortname'));

    $settings->add(new admin_setting_configtext('enrol_dbuserrel/remoteparentuserfield', get_string('remoteparentuserfield', 'enrol_dbuserrel'), get_string('remoteparentuserfield_desc', 'enrol_dbuserrel'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbuserrel/remotestudentuserfield', get_string('remotestudentuserfield', 'enrol_dbuserrel'), get_string('remotestudentuserfield_desc', 'enrol_dbuserrel'), ''));

    /*$settings->add(new admin_setting_configtext('enrol_dbuserrel/remoterolefield', get_string('remoterolefield', 'enrol_dbuserrel'), get_string('remoterolefield_desc', 'enrol_dbuserrel'), ''));*/

    /* Custom settings fields */
    $settings->add(new admin_setting_configtext('enrol_dbuserrel/parentrole', get_string('parentrole', 'enrol_dbuserrel'), get_string('parentrole_desc', 'enrol_dbuserrel'), ''));
    $settings->add(new admin_setting_configtext('enrol_dbuserrel/currentacademicyear', get_string('currentacademicyear', 'enrol_dbuserrel'), get_string('currentacademicyear_desc', 'enrol_dbuserrel'), ''));
}
