<?php
session_start();
include_once 'config.php';
function isValiedQuestion($uName, $uMail, $uPhone, $uQuestion, &$errMassage = null)
{
    $errMassage = '';
    $hasError = false;
    if (strlen($uName) < QA_UNAME_MIN_LENTH) {
        $errMassage .= "Your name is short \n";
        $hasError = true;
    }
    if (!filter_var($uMail, FILTER_VALIDATE_EMAIL)) {
        $errMassage .= "Your mail not valied !! \n";
        $hasError = true;
    }
    if (strlen($uPhone) < 11 or strlen($uPhone) > 11) {
        $errMassage .= "Your phone not valied !! \n";
        $hasError = true;
    }
    if (strlen($uQuestion) < QA_QURSTION_MIN_LENTH) {
        $errMassage .= "Your Question is short !! \n";
        $hasError = true;
    }
    if (repititiveQuestion($uName, $uQuestion)) {
        $errMassage .= "your question is repititive!! \n";
        $hasError = true;
    }
    if ($hasError) {
        return false;
    } else {
        return true;
    }
}

function addquestion($uName, $uMail, $uPhone, $uQuestion, &$errMassage = null)
{
    global $db;
    list($uname, $umail, $uphone, $uquestion) = array($uName, $uMail, $uPhone, $uQuestion);
    $result = $db->prepare("INSERT INTO `questions` (uname,uemail,umobile,text) VALUES (? ,? ,?, ?)");
    if ($result) {
        $result->bind_param("ssss", $uname, $umail, $uphone, $uquestion);
        $result->execute();
        return true;
    } else {
        $errMassage = 'An error occurred while registering your question !!!';
        return false;
    }


}

function repititiveQuestion($uName, $question)
{
    global $db;
    $stmnt = $db->prepare("SELECT * FROM `$db->questionTable` WHERE uname = ? AND text= ?");
    $stmnt->bind_param('ss', $uName, $question);
    $stmnt->execute();
    $result = $stmnt->get_result();
    $rows = $result->fetch_all();
    if (sizeof($rows) != 0) {
        return true;
    }

}

// sanitize value //
function cleanInput(&$input)
{
    $search = array(
        '@<script[^>]*?>.*?</script>@si',  //strip out javascript
        '@<[\/\!]*?[^<>]*?>@si',           //Strip out HTML tags
        '$<sytle[^>]*?>.*?</style>@siU',   //Strip style tags properly
        '@<![\s\S]*?--[ \t\n\r]*>@'        //Strip multi-line comments
    );
    $output = preg_replace($search, '', $input);
    $input = $output;
    return $output;
}

function sanitize(&$input)
{
    global $db;
    if (is_array($input)) {
        foreach ($input as $var => $val) {
            $output[$var] = sanitize($val);
        }
    } else {
        if (get_magic_quotes_gpc()) {
            $input = stripslashes($input);
        }
        $input = cleanInput($input);
        $output = mysqli_real_escape_string($db, $input);
    }
    $input = $output;
    return $output;
}

// -----------------------------------Login-----------------------------------------

function dologin($name, $lastName, $password, &$successMasage = null)
{
    global $db;
    list($name, $lastName, $password) = array($name, $lastName, $password);
    $stmnt = $db->query("SELECT * FROM `" . $db->adminTable . "` WHERE name='" . $name . "' and lastname='" . $lastName . "' and password='" . $password . "';");
    if ($stmnt) {
        $_SESSION['username'] = $name;
        $_SESSION['login'] = true;
        $_SESSION['userid'] = $_SERVER['REMOTE_ADDR'];
        header('location:' . QA_HOME_URL);
        return true;
    } else {
        $successMasage .= "Your user name or password not valied";
        return false;
    }
}

function isAdmin()
{
    if (isset($_SESSION['login'])) {
        return true;
    }
    return false;
}

function logAut()
{
    unset($_SESSION['username'], $_SESSION['login'], $_SESSION['userid']);
    return true;
}

//-------------------get questions----------
function getQuestion($page = 1, &$numPages = null, &$errorMasage = null)
{
    global $db;

    $start = ($page - 1) * QA_QUESTION_PER_PAGE;
    $end = QA_QUESTION_PER_PAGE;

    /* ----------------------------admin------------------------------------*/
    if (isAdmin()) {

        if (isset($_GET['status']) && isset($_GET['srchInp']) && $_GET['srchInp'] != null && $_GET['status'] != 'All') {
            $srchInp = str_ireplace(' ', '%', $_GET['srchInp']);
            $status = $_GET['status'];
            $result = $db->query("SELECT * FROM `" . $db->questionTable . "` WHERE status='" . $status . "' AND text LIKE '%" . $srchInp . "%' LIMIT $start ,$end");
            if ($result) {
                $result->fetch_all(1);
                $numRowsStmnt = $db->query("SELECT * FROM `" . $db->questionTable . "` WHERE status='" . $status . "' AND text LIKE '%" . $srchInp . "%'");
                $rows = $numRowsStmnt->fetch_all();
                $countResult = sizeof($rows);
                $numPages .= ceil($countResult / QA_QUESTION_PER_PAGE);
                return $result;
            } else {
                $errorMasage .= "No query found for your search !!";
            }

        } elseif (isset($_GET['status']) && $_GET['status'] != 'All') {
            $result = $db->query("SELECT * FROM `" . $db->questionTable . "` WHERE status='" . $_GET['status'] . "' LIMIT $start,$end");
            if ($result) {
                $result->fetch_all(1);
                $numRowsStmnt = $db->query("SELECT * FROM `" . $db->questionTable . "` WHERE status='" . $_GET['status'] . "'");
                $rows = $numRowsStmnt->fetch_all();
                $countResult = sizeof($rows);
                $numPages .= ceil($countResult / QA_QUESTION_PER_PAGE);
                return $result;

            }


        } elseif (isset($_GET['srchInp']) && $_GET['srchInp'] != null) {
            $srchInp = str_ireplace(' ', '%', $_GET['srchInp']);
            $result = $db->query("SELECT * FROM `" . $db->questionTable . "` WHERE text LIKE '%" . $srchInp . "%' LIMIT $start , $end");
            if ($result) {
                $result->fetch_all(1);
                $numRowsStmnt = $db->query("SELECT * FROM `" . $db->questionTable . "` WHERE text LIKE '%" . $srchInp . "%'");
                $rows = $numRowsStmnt->fetch_all();
                $countResult = sizeof($rows);
                $numPages .= ceil($countResult / QA_QUESTION_PER_PAGE);
                return $result;

            } else {
                $errorMasage .= "No query found for your search !!";

            }
        } else {
            $result = $db->query("SELECT * FROM `$db->questionTable` LIMIT $start , $end ");
            $result->fetch_all(1);
            $numRowsstmnt = $db->query("SELECT * FROM `$db->questionTable`");
            $numRows = $numRowsstmnt->fetch_all();
            $countResult = sizeof($numRows);
            $numPages .= ceil($countResult / QA_QUESTION_PER_PAGE);
            return $result;
        }

        /* ------------------------------- not admin------------------------------------------------*/
    } else {

        if (isset($_GET['status']) && isset($_GET['srchInp']) && $_GET['srchInp'] != null && $_GET['status'] != 'All') {
            $srchInp = str_ireplace(' ', '%', $_GET['srchInp']);
            $status = $_GET['status'];
            $result = $db->query("SELECT * FROM `" . $db->questionTable . "` WHERE status='" . $status . "'AND status!='pending' AND text LIKE '%" . $srchInp . "%' LIMIT $start ,$end");
            if ($result) {
                $result->fetch_all(1);
                $numRowsStmnt = $db->query("SELECT * FROM `" . $db->questionTable . "` WHERE status='" . $status . "' AND status!='pending' AND text LIKE '%" . $srchInp . "%'");
                $rows = $numRowsStmnt->fetch_all();
                $countResult = sizeof($rows);
                $numPages .= ceil($countResult / QA_QUESTION_PER_PAGE);
                return $result;
            } else {
                $errorMasage .= "No query found for your search !!";

            }

        } elseif (isset($_GET['status']) && $_GET['status'] != 'All') {
            $result = $db->query("SELECT * FROM `" . $db->questionTable . "` WHERE status='" . $_GET['status'] . "' AND status!='pending' LIMIT $start,$end");
            if ($result) {
                $result->fetch_all(1);
                $numRowsStmnt = $db->query("SELECT * FROM `" . $db->questionTable . "` WHERE status='" . $_GET['status'] . "' AND status!='pending'");
                $rows = $numRowsStmnt->fetch_all();
                $countResult = sizeof($rows);
                $numPages .= ceil($countResult / QA_QUESTION_PER_PAGE);
                return $result;

            }


        } elseif (isset($_GET['srchInp']) && $_GET['srchInp'] != null) {
            $srchInp = str_ireplace(' ', '%', $_GET['srchInp']);
            $result = $db->query("SELECT * FROM `" . $db->questionTable . "` WHERE text LIKE '%" . $srchInp . "%' LIMIT $start , $end");
            if ($result) {
                $result->fetch_all(1);
                $numRowsStmnt = $db->query("SELECT * FROM `" . $db->questionTable . "` WHERE text LIKE '%" . $srchInp . "%'");
                $rows = $numRowsStmnt->fetch_all();
                $countResult = sizeof($rows);
                $numPages .= ceil($countResult / QA_QUESTION_PER_PAGE);
                return $result;

            } else {
                $errorMasage .= "No query found for your search !!";

            }
        } else {
            $stmnt = $db->query("SELECT * FROM `$db->questionTable` WHERE status !='pending' LIMIT $start,$end ;");
            if ($stmnt) {
                $result = $stmnt->fetch_all(1);
                $numRowsStmnt = $db->query("SELECT * FROM `$db->questionTable` WHERE status !='pending';");
                $numRows = $numRowsStmnt->fetch_all();
                $countResult = sizeof($numRows);
                $numPages .= ceil($countResult / QA_QUESTION_PER_PAGE);
                return $result;
            }

        }

    }
    return false;
}

// ------------get answers-------------
function getAnswers($qid)
{
    global $db;
    $stmnt = $db->query("SELECT * FROM `$db->answersTable` WHERE qid = $qid");
    if ($stmnt) {
        $result = $stmnt->fetch_all(1);
        return $result;

    }
    return false;
}


// -----------------------------add answer-------------------------------

function addAnswer($id, $txtA, &$errorMassage)
{
    global $db;
    $admName = $_SESSION['username'];
    $stmnt = $db->prepare("INSERT INTO `answers`(`qid`, `text`, `admname`) VALUES (?,?,?)");
    if ($stmnt) {
        $stmnt->bind_param("iss",$id,$txtA,$admName);
        $stmnt->execute();
        return true;
    } else {
        $errorMassage .= "An error occurred while registering your Answer !!";
        return false;
    }
}
function deletAnswer($id,$errorMasage){
    global $db;
    $stmnt = $db->query("DELETE FROM `".$db->answersTable."` WHERE id=".$id.";");
    if ($stmnt){
        return true;
    }else{
        return  false;
        $errorMasage .= "An Error ccourred While Deleting Your Answer!!!";
    }
}

//----------------------------delete question------------------------------

function deletQuestion($id)
{
    global $db;
    $stmnt = $db->query("DELETE FROM `" . $db->questionTable . "` WHERE id=" . $id . ";");
    if ($stmnt) {

        return true;
    } else {
        return false;
    }

}

//------------------------------published----------------------------------

function published($id)
{
    global $db;
    $stmnt = $db->query("UPDATE `" . $db->questionTable . "`SET status = 'publish' WHERE `" . $db->questionTable . "`.`id` =" . $id . ";");
    if ($stmnt) {
        return true;
    } else {
        return false;
    }
}