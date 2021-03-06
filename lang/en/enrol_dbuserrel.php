<?php
$string['pluginname'] = 'DB User role assignment';
$string['dbtype'] = 'Database type';
$string['dbtype_desc'] = 'ADOdb database driver name, type of the external database engine.';
$string['dbhost'] = 'Server IP name or number';
$string['dbhost_desc'] = 'Type database server IP address or host name';
$string['dbuser'] = 'Server user';
$string['dbpass'] = 'Server password';
$string['dbname'] = 'Database name';
$string['dbtable'] = 'Database table';
$string['dbencoding'] = 'Database encoding';
$string['description'] = 'You can use a external database (of nearly any kind) to control your relationships between users. It is assumed your external database contains a field containing two user IDs, and a Role ID.  These are compared against fields that you choose in the local user and role tables';
$string['enrolname'] = 'External Database (User relationships)';
$string['localrolefield'] = 'Local role field';
$string['localparentuserfield'] = 'Local parent field';
$string['localstudentuserfield'] = 'Local student field';
$string['localrolefield_desc'] = 'The name of the field in the roles table that we are using to match entries in the remote database (eg shortname).';
$string['localparentuserfield_desc'] = 'The name of the field in the user table that we are using to match entries in the remote database (eg username). for the <i>parent</i> role assignment';
$string['localstudentuserfield_desc'] = 'The name of the field in the user table that we are using to match entries in the remote database (eg username). for the <i>student</i> role assignment';
$string['remote_fields_mapping'] = 'Database field mapping';
$string['remoterolefield'] = 'Remote role field';
$string['remoteparentuserfield'] = 'Remote parent field';
$string['remotestudentuserfield'] = 'Remote student field';
$string['remoterolefield_desc'] = 'The name of the field in the remote table that we are using to match entries in the roles table.';
$string['remoteparentuserfield_desc'] = 'The name of the field in the remote table that we are using to match entries in the user table for the <i>parent</i> role assignment';
$string['remotestudentuserfield_desc'] = 'The name of the field in the remote table that we are using to match entries in the user table for the <i>student</i> role assignment';
$string['server_settings'] = 'External Database Server Settings';
$string['pluginname_desc'] = 'You can use an external database (of nearly any kind) to control your mentor role. It is assumed your external database contains at least a field containing a student username, a mentor role, and a field containing a mentor username. These are compared against fields that you choose in the local role and user tables.';
$string['settingsheaderdb'] = 'External database connection';
$string['remoteenroltable'] = 'Remote user enrolment table';
$string['remoteenroltable_desc'] = 'Specify the name of the table that contains list of user enrolments. Empty means no user enrolment sync.';
/* Custom fields */
$string['parentrole'] = 'Role to assign';
$string['parentrole_desc'] = 'Specify the role to assign';
$string['currentacademicyear'] = 'Current academic year';
$string['currentacademicyear_desc'] = 'Specify the current academic year (eg 2018/19)';
/* ****** */
$string['taskname'] = 'Assigning the parent to the student';
