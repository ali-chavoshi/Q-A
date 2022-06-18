<?php
include_once 'inc/actions.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo QA_TITLE; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <?php
    if ($errorMasage) {
        echo "<div class='error'>" . nl2br($errorMasage) . "</div>";
    } elseif ($successMasage) {
        echo "<div class='succes'>" . nl2br($successMasage) . "</div>";
    }
    ?>
    <div class="header">
        <div class="title">
            <a class="titleA" href="<?php echo QA_HOME_URL ?>"><?php echo QA_TITLE; ?></a>
        </div>
        <div class="search">
            <form action="" name="search" method="post" class="searchform">
                <button class="srchbtn">
                    SEARCH
                    <i class="fa fa-search"></i>
                </button>

                <select class="status">
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="publish">Published</option>
                    <option value="answered">Answered</option>
                </select>
                <div class="serchBarMain">
                    <input class="serchBar" name="serchBar" placeholder="search">

                </div>
            </form>
        </div>

    </div>
    <div class="body">

        <div class="inputQuestion">
            <form action="" method="post" class="formQuestion">
                <strong class="inputTitle">Ask a question</strong>
                <input type="text" name="Uname" class="inputQ" placeholder="Name:" required>
                <input type="text" name="Umail" class="inputQ" placeholder="Email:" required>
                <input type="text" name="Uphone" class="inputQ" placeholder="Phone">
                <textarea name="Uqst" class="inputQ" cols="15" rows="5" placeholder="Enter your question:"></textarea>
                <button name="submitQuestion" class="inputbtn">
                    SUBMIT
                </button>
            </form>
        </div>
        <div class="content">
            <div class="formLogin">
                <strong class="inputTitleLogin">Login</strong>
                <form action="" method="post">
                    <input type="text" class="inputQ" name="logN" placeholder="Name">
                    <input type="text" class="inputQ" name="logLn" placeholder="Last Name">
                    <input type="text" class="inputQ" name="logpass" placeholder="Password">
                    <button class="loginBtn" name="loginBtn">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>



