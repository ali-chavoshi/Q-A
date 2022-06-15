<?php
// config of question answering project

//  website information
define('QA_TITLE'," Question Answering System");
define('QA_HOME_URL', "http://localhost/q&s/");
define('QA_QUESTION_PER_PAGE', 4);
define('QA_QURSTION_MIN_LENTH', 10);
define('QA_UNAME_MIN_LENTH', 3);
define('QA_DATE_FORMAT', 'd F Y');


// admin information
define('QA_ADMIN_DISPLAY_NAME', 'manager');
define('QA_ADMIN_USER_NAME', 'Ali');
define('QA_ADMIN_PASSWORD', '7415963');

// Turn off error reporting after project completion
ini_set('display_errors', 'on');
error_reporting(E_ALL);

// Host information
$dbHost = "localhost";
$dbUser = "qa-user";
$dbPass = "ali2878@chwo";
$dbName = "project_qa";

$db = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// check connection error
if ($db->connect_errno) {
    printf("Connect Failed: %s\n", $db->connect_error);
    exit();
}/*else{
    echo "ok";
}*/

// for farsi data information to/from database
$db->query("SET NAMES UTF8;");

// define our table for usage in code
$db->questionTable = "questions";
$db->answersTable = "answers";
$db->userTable = "users";
