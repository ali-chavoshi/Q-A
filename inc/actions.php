<?php
include_once 'config.php';
include_once 'functions.php';
$errorMasage = false;
$successMasage = false;



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


//---- get questions and pagination-----------
$page= (isset($_GET['page'])) ? $_GET['page'] : 1;
$numQuestion = 0;
$numPage = ceil($numQuestion/QA_QUESTION_PER_PAGE);
$questions = '';

if (isset($_GET['search']) && strlen($_GET['search'])>0){
    $search = str_ireplace(' ','%',$_GET['search']);

    if (isset($_GET['status'])){
        $questions = getQuestion(trim($_GET['status']),$search,1,$numQuestion);
    }else{
        $questions = getQuestion('all',$search,1,$numQuestion);
    }
}else{
    if(isset($_GET['status'])){
        $questions = getQuestion(trim($_GET['status']),null,$page,$numQuestion);
    }else{
        $questions = getQuestion('all',null,$page,$numQuestion);
    }

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