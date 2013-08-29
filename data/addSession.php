<?php
    require_once("../pomodoroInterface.php");
    //echo AddSession(3004,15000000000,"2013/08/28");
    //echo true;
    if(isset($_POST['SubcategoryID']) && isset($_POST['TimespanInSeconds']) && isset($_POST['TodayDate'])) {
       echo AddSession($_POST['SubcategoryID'],$_POST['TimespanInSeconds'],$_POST['TodayDate']);
       //echo AddSession(3004,15000000000,"2013/08/28");


    } else {
        echo "Something went wrong in addSession.php";
    }
?>
