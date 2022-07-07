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

//-------------------get questions--------------------------------
function getQuestion($status,$search,$page,&$numQuestion=0){
    global $db;
    $start = ($page-1) * QA_QUESTION_PER_PAGE;
    $offset = QA_QUESTION_PER_PAGE;
    if($status == 'all'){
        if(!isAdmin()){
            $whereStr = "status!='pending'";
        }else{
            $whereStr = 1;
        }
        if ($search!=null){
            $sql = "SELECT * FROM $db->questionTable WHERE $whereStr and text like '%$search%' order by create_date desc limit $start,$offset;";
            $countSql ="SELECT count(*) as c from $db->questionTable WHERE $whereStr and text like '%$search%'";
        }else {
            $sql = "SELECT * FROM $db->questionTable where $whereStr order by create_date desc limit $start,$offset;";
            $countSql = "SELECT count(*) as c FROM $db->questionTable where $whereStr";
        }
    }elseif (isAdmin() or (in_array(getValidstsatus($status),array('publish','answered')))){
        if ($search!=null){
            $sql = "SELECT * FROM $db->questionTable WHERE status='$status' and text like '%$search%' order by create_date desc limit $start,$offset";
            $countSql = "SELECT count(*) as c FROM $db->questionTable WHERE status='$status' and text like '%$search%'";
        }else{
            $sql ="SELECT * FROM $db->questionTable WHERE status='$status' order by create_date desc limit $start,$offset";
            $countSql = "SELECT count(*) as c FROM $db->questionTable WHERE status='$status'";
        }
    }else{
        $sql = "SELECT * FROM $db->questionTable WHERE status!='pending' order by create_date desc limit $start,$offset";
        $countSql = "SELECT count(*) as c FROM $db->questionTable WHERE status!='pending'";
    }
    $result = $db-> query($sql);
    if ($result){
        $question = $result->fetch_all(1);
        $numQuestion = $db -> query($countSql)->fetch_object()->c;
        return $question;
    }
    return null;


}


//-------------------get valid status-------------------------------
function getValidstsatus($status){
    if (isValidStatus($status)){
        return true;
    }else{
        return "all";
    }
}

function isValidStatus($status){
    $statusArr = array ('pending','publish','answered');
    if (in_array($status,$statusArr)){
        return true;
    }else {
        return false;
    }
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
        $state= $db->query("UPDATE `" . $db->questionTable . "`SET `status` = 'answered' WHERE `id` =" . $id . ";");
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


//-----------------------------pagination functions-------------------------
function getNumPage($numQuestion){
    $numPages = ceil($numQuestion/QA_QUESTION_PER_PAGE);
    return $numPages;
}

function getPageUrl($pageNumber){
    $getParameters = array();
    if (isset($_GET['status'])){
        $getParameters['status'] = $_GET['status'];
    }
    if (isset($_GET['search'])){
        $getParameters['search']= $_GET['search'];
    }
    $getParameters['page']=$pageNumber;
    $str="?";
    foreach ($getParameters as $key => $value){
        $str .= "$key=$value&";
    }
    return QA_HOME_URL . trim($str,'&');
}

function getPageBack($page){
    if ($page>1){
        $back = ($page-1);
        echo "<a class='page' href='".getPageUrl($back)."'><</a>";
    }else{
        echo "<strong class='currentPage'><</strong>";
    }
}

function getPageNext($page){
    if(isset($_GET['page'])&& $page==$_GET['page']){
        echo "<strong class='currentPage'>></strong>";
    }else {
        if(isset($_GET['page'])){

            echo "<a href='".getPageUrl($_GET['page']+1)."'class='page'>></a>";
        }else{
            echo "<a href='".getPageUrl(2)."'class='page'>></a>";

        }
    }
}