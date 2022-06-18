<?php
include_once 'config.php';
include_once 'functions.php';
$errorMasage = false;
$successMasage = false;
$numPage = false;
$questions = '';
//------- submit qquestion----------------------
if (isset($_POST['submitQuestion'])){
    if (isValiedQuestion($_POST['Uname'] , $_POST['Umail'] ,$_POST['Uphone'] ,$_POST['Uqst'],$errorMasage)){
        //add question to database
        if (addquestion($_POST['Uname'] , $_POST['Umail'] ,$_POST['Uphone'] ,$_POST['Uqst'],$errorMasage)){
            $successMasage .= "Your question has been successfully submitted  ";
        }
    }
}
//---------------- log in ----------------------
if (isset($_POST['loginBtn'])){
    if (dologin($_POST['logN'],$_POST['logLn'],$_POST['logpass'],$errorMasage)){
        $successMasage .= "You are log in";
    }
}
//---------------- log out ---------------------
if (isset($_GET['exit'])){
    logAut();
    $successMasage .= "you are logged out ";
}
//---- get questions and page ination-----------
if (isset($_GET['page'])){
    $questions = getQuestion($_GET['page'],$numPage);
}else{
    $questions = getQuestion(1,$numPage);
}