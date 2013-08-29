$(document).ready(function() {
    Initialize();
    window.currentStatus = PomodoroStatus.Session.Start;
    window.currentTimerTime = PomodoroLength.Session;
    switchControls(currentStatus);
    setTime(currentTimerTime);
    $("select").change(function () {
        if ($("select option:selected").val() != "none") {
                RefreshInfo();
                $("input[type=button]").removeAttr('disabled');
            } else{
                $("input[type=button]").attr('disabled','disabled');
            }


    });
    $("#StartTimer").click(function() {
        StartTimer();
        $("select").attr("disabled","disabled");
    });
    $("#StartBreak").click(function() {
        StartTimer();

    });
    $("#Complete").click(function() {
        clearInterval(timerInterval);
        $("select").removeAttr("disabled");
        var SubcategoryId = $("select option:selected").val();
        var timeSpan = (PomodoroLength.Session - currentTimerTime);
        AddSessionAjax(SubcategoryId,timeSpan);

        currentTimerTime = PomodoroLength.Session;
        currentStatus = PomodoroStatus.Session.Start;

        switchControls(currentStatus);
    });
    $("#Stop").click(function() {
       clearInterval(timerInterval);
       currentTimerTime = PomodoroLength.Session;
        currentStatus = PomodoroStatus.Session.Start;
        $("select").removeAttr("disabled");
       switchControls(currentStatus);
    });
    $("#Restart").click(function() {
        currentTimerTime = PomodoroLength.Session;
    });
    $("#PostponeBreak").click(function() {
        clearInterval(timerInterval);
        postponeTimerTime = currentTimerTime;
        currentTimerTime = PomodoroLength.Session;
        currentStatus = PomodoroStatus.Session.Start;

        $("select").removeAttr("disabled");
        switchControls(currentStatus);
    });

});


function Initialize() {
    window.session = 0;
    window.solemn = $("#solemn")[0];
    window.achievement = $("#achievement")[0];
    window.postponeTimerTime = 0;
    PomodoroLength = {
        Session: parseInt($("#countdown").data("pomodorotime")),
        ShortBreak: parseInt($("#countdown").data("breaktime")),
        LongBreak: parseInt($("#countdown").data("pomodorotime")) + parseInt($("#countdown").data("breaktime"))
    }
    PomodoroStatus = {
        Session : {
            Start: 'PomodoroStart',
                Running: 'PomodoroRunning'
        },
        Break : {
            Start: 'BreakStart',
                Running: 'BreakRunning'
        }
    }
}




function setTime(totalTime) {
    var minute = parseInt(totalTime/60);
    var second = parseInt(totalTime%60);

    minute = (minute < 10)?"0"+minute:minute;
    second = (second < 10)?"0"+second:second;
    $(".minute").text(minute);
    $(".second").text(second);
    $(".session").text("(" + session + ")");
}

function switchControls(status) {
    switch(status) {
        case PomodoroStatus.Session.Start:
            $("#allButtons div").addClass("hideElement");
            $("#PomodoroStart").removeClass("hideElement");
            setTime(currentTimerTime);
            break;
        case PomodoroStatus.Session.Running:
            $("#allButtons div").addClass("hideElement");
            $("#PomodoroOngoing").removeClass("hideElement");
            setTime(currentTimerTime);

            break;
        case PomodoroStatus.Break.Start:
            $("#allButtons div").addClass("hideElement");
            $("#BreakStart").removeClass("hideElement");
            setTime(currentTimerTime);
            break;




    }
}
function TimerTick() {
    if(window.currentTimerTime > 0) {
        currentTimerTime -= 1;
        setTime(currentTimerTime);
    } else {
        switch(currentStatus) {
            case PomodoroStatus.Session.Running:
                clearInterval(timerInterval);
                var SubcategoryID = $("select option:selected").val();
                AddSessionAjax(SubcategoryID,PomodoroLength.Session);
                currentStatus = PomodoroStatus.Break.Start;
                window.session += 1;
                if(session % 4 == 0) {
                    currentTimerTime = PomodoroLength.LongBreak + postponeTimerTime;
                } else {
                    currentTimerTime = PomodoroLength.ShortBreak + postponeTimerTime;
                }

                RefreshInfo();
                switchControls(currentStatus);
                achievement.play();
                break;
            case PomodoroStatus.Break.Running:
                clearInterval(timerInterval);
                currentStatus = PomodoroStatus.Session.Start;
                $("select").removeAttr("disabled");
                currentTimerTime = PomodoroLength.Session;
                postponeTimerTime = 0;
                switchControls(currentStatus);
                solemn.play();
                break;


        }

    }

}

function StartTimer() {
    window.timerInterval =  setInterval(TimerTick,1000);
    if(currentStatus == PomodoroStatus.Session.Start) {
    currentStatus = PomodoroStatus.Session.Running;
        switchControls(currentStatus);
    } else if(currentStatus == PomodoroStatus.Break.Start) {
        currentStatus = PomodoroStatus.Break.Running;
        switchControls(currentStatus);
    }
}
function RefreshInfo() {
    urlTotalTime = "data/getSubcategoryTotalTime.php";
//    dt = new Date();
//    todayDate = dt.getFullYear()+"/"+dt.getMonth()+"/"+dt.getDate();
    var SubcategoryId = $("select option:selected").val();
    //$.post(url,{'Subcategory_id':2002,'date': todayDate},function(data) {
    $.post(urlTotalTime,{'Subcategory_id':SubcategoryId},function(data) {
        console.log(obj = $.parseJSON(data));
        //obj = $.parseJSON(data);
        var TotalMinute = (obj.TotalTime.i < 10)?"0"+obj.TotalTime.i:obj.TotalTime.i;
        var TotalSecond = (obj.TotalTime.s < 10)?"0"+obj.TotalTime.s:obj.TotalTime.s;
        $(".totalTimeToday").text("Total Time Today: "+obj.TotalTime.h + ":" + TotalMinute + ":" + TotalSecond);
        minute = (obj.HighestTime.TotalTime.i < 10)?"0"+obj.HighestTime.TotalTime.i:obj.HighestTime.TotalTime.i;
        second = (obj.HighestTime.TotalTime.s < 10)?"0"+obj.HighestTime.TotalTime.s:obj.HighestTime.TotalTime.s;
        $(".highestTime").text("Highest Time Ever: "+obj.HighestTime.TotalTime.h + ":" + minute + ":" + second);

    });


}
function AddSessionAjax(SubcategoryID,TimespanInSeconds) {
    url = "data/addSession.php";
    TodayDate = new Date();
    TodayDate = TodayDate.getFullYear() + "/" + (TodayDate.getMonth() + 1) + "/" + TodayDate.getDate();
    $.post(url,{'SubcategoryID':SubcategoryID,'TimespanInSeconds':TimespanInSeconds,'TodayDate':TodayDate},function(data) {
        console.log(data);
        if(data=="1") {
            RefreshInfo();

        } else {
            alert("Something went wrong while adding the session.");
        }
    });
}