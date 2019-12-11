THIS PLUGIN IS IN BETA STATUS! BE CAREFULL WITH PRODUCTION ENVIRONMENT!

This plugin was first developed by Penny Leach <penny@catalyst.net.nz> for Moodle 1.9
and modified by Maxime Pelletier <maxime.pelletier@educsa.org> for 2.3. 
Modified by https://github.com/mfuhrmeisterDM for 2.9 and Madhu Avasarala for 3.4
Plugin has been tested on 2.3 and 2.6. and 3.4

USE
==============
This plugin allows you to configure automatic relationships between users from an external database.
This plugin is beign used to assign LSE tutors to students.

HOW TO INSTALL
==============
Prerequisites
a. SQL table containing parent-student-role relationship information.  
b. PHP library to connect to SQL table.
c. Parent and student user already in Moodle.
d. Role already in Moodle.

e. Fill all parameters using Moodle plugin administration interface.
f. Set up a scheduled task to sync parent-student-role assignments.
