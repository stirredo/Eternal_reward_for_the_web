<?php
    require_once("../pomodoroInterface.php");
    if(isset($_POST['Subcategory_id'])) {
        $date = new DateTime();
        $date = $date->format("Y/m/d");
        $result = GetSubcategoryTotalTimeByDate($_POST['Subcategory_id'],$date);
        $result['HighestTime'] = GetHighestTime($_POST['Subcategory_id']);
        echo json_encode($result);
        
    }
?>
