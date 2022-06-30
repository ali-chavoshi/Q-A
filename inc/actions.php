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
    $questions = getQuestion($_GET['page'],$numPage,$errorMasage);

}else{
     $questions = getQuestion(1,$numPage,$errorMasage);
}

//----------delete Questios------------------
if (isset($_GET['delete'])){
    deletQuestion($_GET['delete']);
    $successMasage .= "Question removed successfully";
}

//-------------published---------------------
if (isset($_GET['publish'])){
    published($_GET['publish']);
}

//-------------add answer admin---------------
if (isset($_POST['adminAsnwer'],$_POST['ansId'])){
    if (addAnswer($_POST['ansId'],$_POST['adminAsnwer'],$errorMasage)){
        $successMasage .= "Your answer was successfully registered";
    }
}
//--------------delete answers-----------------

if (isset($_GET['asnswerId'])){
    deletAnswer($_GET['asnswerId'],$errorMasage);
    $successMasage .= "Your answer was successfully deleted";
}