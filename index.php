<?php
require_once("pomodoroInterface.php");
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Eternal Reward now on the Web!</title>
    <link rel="stylesheet" href="cssreset.css"/>
    <link rel="stylesheet" href="style.css"/>
    <script src="jquery.js"></script>

</head>
<body>
<?php
    $Settings = GetPomodoroTime();
    $pomodoroTime = $Settings['PomodoroTime'];
    $breakTime = $Settings['BreakTime'];
    $pomodoroTime = ($pomodoroTime-> h * 60 * 60) + ($pomodoroTime->i * 60) + ($pomodoroTime->s);
    $breakTime = ($breakTime-> h * 60 * 60) + ($breakTime->i * 60) + ($breakTime->s)
?>

<div id="countdown" data-pomodoroTime="<?= $pomodoroTime ?>" data-breakTime="<?= $breakTime ?>">
    <span class="minute">00</span>
    <span class="divider">:</span>
    <span class="second">00</span>
    <span class="session">(0)</span>
</div>
<div id="info">
   <p class="totalTimeToday">Total Time Today: </p>
   <p class="highestTime">Highest Time Ever: </p>
    <p class="message"></p>
</div>
<div id="controls">
    <label for="subcategory">Select a subcategory: </label>
    <select name="subcategory" id="subcategory">
        <option value="none">Select One</option>
        <?php
        $result = GetAllSubcategories();
        ?>
        <?php foreach ($result as $row): ?>
            <option value="<?= $row['Subcategory_id']; ?>"><?= $row['Subcategory_name'] ?></option>
        <?php endforeach; ?>
    </select>

    <div id="allButtons">
        <div id="PomodoroStart">
            <input disabled type="button" value="Start Timer" id="StartTimer"/>
        </div>
        <div id="PomodoroOngoing">
            <input disabled type="button" value="Complete" id="Complete"/><br/>
            <input disabled type="button" value="Stop" id="Stop"/><br/>
            <input disabled type="button" value="Restart" id="Restart"/><br/>
        </div>
        <div id="BreakStart">
            <input disabled type="button" value="Start Break" id="StartBreak"/><br/>
            <input disabled type="button" value="Postpone Break" id="PostponeBreak"/><br/>
        </div>
    </div>
</div>
<audio src="sound/solemn.mp3" id="solemn">
<audio src="sound/achievement.mp3" id="achievement">
<script src="app.js"></script>

</body>
</html>