<?php
    function ConnectDatabase() {
        try {
            $serverName = "(local)\\sqlexpress";
            $dbHandle = new PDO("sqlsrv:server=$serverName;Database=Pomodoro","","");
            $dbHandle->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            return $dbHandle;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }


    }
    function TicksToDateInterval($ticks) {
        $seconds = $ticks/10000000; // To convert C# ticks to seconds (Thats 10 million to a second)
        $dt = new DateTime();
        $dt = $dt->sub(new DateInterval("PT{$seconds}S"));
        $dtToday = new DateTime();
        $diff = $dtToday->diff($dt);
        return $diff;
    }
    function SecondsToTicks($minute,$seconds) {
        $result = (($minute * 60) + $seconds) * 10000000; // multiply by 10 million to convert seconds to ticks
        return $result;
    }
    function AddCategory($CategoryName) {
        try {
        $dbHandle = ConnectDatabase();
        $statement = $dbHandle->prepare("INSERT INTO Categories (Category_name) VALUES (:name)");
        $statement->bindParam(":name",$CategoryName,PDO::PARAM_STR);
        $statement->execute();
        if($statement->rowCount() == 1) {

            return true;
        } else {
            return false;
        }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    function DeleteCategory($CategoryId) {
        try {
        $dbHandle = ConnectDatabase();
        $statement = $dbHandle->prepare("DELETE FROM Categories WHERE Category_id = :id");
        $statement->bindParam(":id",$CategoryId,PDO::PARAM_INT);
        $statement->execute();
        if($statement->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    function AddSubcategory($SubcategoryName,$CategoryId,$AllowManual=true) {
        try {
            $dbHandle = ConnectDatabase();
            $statement = $dbHandle->prepare("INSERT INTO Subcategories(Subcategory_name,Category_id,Allow_manual) " .
            "VALUES (:Subcategory_name,:Category_id,:Allow_Manual)");

            $statement->bindParam(":Subcategory_name",$SubcategoryName);
            $statement->bindParam(":Category_id",$CategoryId);
            $AllowManual = ($AllowManual)? 1: 0;
            $statement->bindParam(":Allow_Manual",$AllowManual);
            $statement->execute();
            if($statement->rowCount() == 1) {
                return true;
            } else {
                return false;
            }

        } catch (PDOException $e) {
            echo $e->getMessage();
        }

    }
    function RemoveSubcategory($SubcategoryId) {
        try {
            $dbHandle = ConnectDatabase();
            $SQL = "DELETE from Subcategories where Subcategory_id = :id";
            echo "$SQL $SubcategoryId";
            $statement = $dbHandle->prepare($SQL);
            $statement->bindParam(":id",$SubcategoryId,PDO::PARAM_INT);
            if($statement->rowCount() == 1 ){
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    function AddSession($SubcategoryId, $SessionTimespan, $SessionDate) {
        try {
            $dbHandle = ConnectDatabase();
            $SQL = "INSERT INTO Sessions(subcategory_id,session_timespan,session_date)" .
                " VALUES (:subcategoryId,:sessionTimespan,:sessionDate)";
            $statement = $dbHandle->prepare($SQL);
            $statement->bindParam(":subcategoryId",$SubcategoryId,PDO::PARAM_INT);
            $SessionTimespan = SecondsToTicks(0,$SessionTimespan);
            $statement->bindParam(":sessionTimespan",$SessionTimespan,PDO::PARAM_INT);
            $statement->bindParam(":sessionDate",$SessionDate,PDO::PARAM_STR);
            $statement->execute();
            if($statement->rowCount() == 1) {
                return true;
            } else {
                return false;
            }

        } catch (PDOException $e) {
            echo $e->getMessage();
        }

    }
    function RemoveSession($sessionId) {
        try {
            $dbHandle = ConnectDatabase();
            $SQL = "DELETE FROM Sessions WHERE session_id = :SessionId";
            $statement = $dbHandle->prepare($SQL);
            $statement->bindParam(":SessionId",$sessionId);
            $statement->execute();
            if($statement->rowCount()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }


    }
    function GetSubcategoryTotalTime($SubcategoryId,$LastXDays = -1) {
        //Associative Array as TotalTime (DateInterval Object) and Subcategory (Subcategory Name)

        try {
            $dbHandle = ConnectDatabase();
            if($LastXDays > 0) {
                $SQL = "SELECT SUM(sess.session_timespan) AS TotalTime,Subcategories.Subcategory_name AS Subcategory
	                        FROM Sessions AS sess
                            JOIN Subcategories
                            ON sess.subcategory_id = Subcategories.Subcategory_id
                            WHERE sess.subcategory_id = :SubcategoryId AND sess.session_date >= :SessionDate
                            GROUP BY Subcategories.Subcategory_name";

//               $SQL = "SELECT SUM(sess.session_timespan) AS TotalTime,Subcategories.Subcategory_name AS Subcategory".
//                        "FROM Sessions AS sess".
//                        "JOIN Subcategories" .
//                        "ON sess.Subcategory_id = Subcategories.Subcategory_id".
//                        "WHERE Sess.subcategory_id = :SubcategoryId AND Sess.session_date >= :SessionDate".
//                        "GROUP BY Subcategories.Subcategory_name";
                $statement = $dbHandle->prepare($SQL);
                $statement->bindParam(":SubcategoryId",$SubcategoryId,PDO::PARAM_INT);
                $dtFrom = new DateTime();
                $dtFrom->sub(new DateInterval("P"."$LastXDays"."D"));
                $dt = $dtFrom->format("Y/m/d");
                $statement->bindParam(":SessionDate",$dt,PDO::PARAM_STR);
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $statement->execute();
                $result = $statement->fetch();
                $result["TotalTime"] = TicksToDateInterval($result['TotalTime']);
                return $result;
            } else {
                $SQL = "SELECT SUM(sess.session_timespan) AS TotalTime,Subcategories.Subcategory_name AS Subcategory
	                        FROM Sessions AS sess
                            JOIN Subcategories
                            ON sess.subcategory_id = Subcategories.Subcategory_id
                            WHERE sess.subcategory_id = :SubcategoryId
                            GROUP BY Subcategories.Subcategory_name";
                $statement = $dbHandle->prepare($SQL);
                $statement->bindParam(":SubcategoryId",$SubcategoryId);
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $statement->execute();
                $result = $statement->fetch();
                $result["TotalTime"] = TicksToDateInterval($result['TotalTime']);
                return $result;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

    }
    function GetSubcategoryTotalTimeByDate($SubcategoryId,$date) {
        try {
        $dbHandle = ConnectDatabase();
        $SQL =  "SELECT SUM(sess.session_timespan) AS TotalTime,Subcategories.Subcategory_name AS Subcategory
                    FROM Sessions AS sess
                    JOIN Subcategories
                       ON sess.subcategory_id = Subcategories.Subcategory_id
                    WHERE sess.subcategory_id = :SubcategoryId AND sess.session_date = :SessionDate
                    GROUP BY Subcategories.Subcategory_name";
        $statement = $dbHandle->prepare($SQL);
        //$statement->bindParam(":SubcategoryId",$SubcategoryId,PDO::PARAM_INT);

        $statement->bindParam(":SubcategoryId",$SubcategoryId,PDO::PARAM_INT);
        $dtFrom = new DateTime();
        $dt = $dtFrom->format("Y/m/d");
        $statement->bindParam(":SessionDate",$date,PDO::PARAM_STR);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->execute();
        $result = $statement->fetch();
        $result["TotalTime"] = TicksToDateInterval($result['TotalTime']);
        return $result;
        } catch(PDOException $e) {
            echo $e->getMessage();
        }
    }
    function GetAllCategories() {
        try {
            $SQL = "SELECT * FROM Categories";
            $dbHandle = ConnectDatabase();
            $statement = $dbHandle->prepare($SQL);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->execute();
            $result = $statement->fetchAll();
            return $result;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

    }
    function GetAllSubcategories() {
        try {
            $SQL = "SELECT * FROM Subcategories";
            $dbHandle = ConnectDatabase();
            $statement = $dbHandle->prepare($SQL);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->execute();
            return $statement->fetchAll();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    function GetSessionData($SubcategoryId) {
        try {
            $SQL = "SELECT * FROM Sessions WHERE Subcategory_id = :SubcategoryId";
            $dbHandle = ConnectDatabase();
            $statement = $dbHandle->prepare($SQL);
            $statement->bindParam(":SubcategoryId",$SubcategoryId);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->execute();
            return $statement->fetchAll();
     } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    function GetSessionDataByDate($SubcategoryId,$Date) {
        try {
            $SQL =  "SELECT * FROM Sessions WHERE subcategory_id = :SubcategoryId AND session_date = :sessionDate";
            $dbHandle = ConnectDatabase();
            $statement = $dbHandle->prepare($SQL);
            $statement->bindParam(":SubcategoryId",$SubcategoryId,PDO::PARAM_INT);
            $statement->bindParam(":sessionDate",$Date,PDO::PARAM_STR);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->execute();
            return $statement->fetchAll();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    function GetHighestTime($SubcategoryId) {
        try {
            $SQL = "SELECT SUM(session_timespan) AS TotalTime,session_date AS Date FROM Sessions
                        WHERE subcategory_id = :SubcategoryId
                        GROUP BY session_date
                        ORDER BY SUM(session_timespan) DESC";
            $dbHandle = ConnectDatabase();
            $statement = $dbHandle->prepare($SQL);
            $statement->bindParam(":SubcategoryId",$SubcategoryId);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->execute();
            $result = $statement->fetch();
            $result['TotalTime'] = TicksToDateInterval($result['TotalTime']);
            return $result;

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    function GetPomodoroTime(){
        // Associative array - PomodoroTime and BreakTime
        try {
            $SQL = "SELECT Pomodoro_time AS PomodoroTime,Break_time AS BreakTime  FROM Settings";
            $dbHandle = ConnectDatabase();
            $statement = $dbHandle->prepare($SQL);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->execute();
            $result = $statement->fetch();
            $result['PomodoroTime'] = TicksToDateInterval($result['PomodoroTime']);
            $result['BreakTime'] = TicksToDateInterval($result['BreakTime']);
            return $result;

        } catch (PDOException $e ) {
            echo $e->getMessage();
        }
    }
    function SetPomodoroTime($PomodoroTime) {
        //MUST BE IN TICKS - 1 SECOND = 10 MILLION TICKS
        try {
            $SQL = "UPDATE Settings SET Pomodoro_time = :PomodoroTime";
            $dbHandle = ConnectDatabase();
            $statement = $dbHandle->prepare($SQL);
            $statement->bindParam(":PomodoroTime",$PomodoroTime);
            $statement->execute();
            if($statement->rowCount() > 0) {
                return true;
            } else {
                return false;
            }

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    function SetBreakTime($BreakTime) {
        //MUST BE IN TICKS - 1 SECOND = 10 MILLION TICKS
        try {
            $SQL = "UPDATE Settings SET Break_time = :BreakTime";
            $dbHandle = ConnectDatabase();
            $statement = $dbHandle->prepare($SQL);
            $statement->bindParam(":BreakTime",$PomodoroTime);
            $statement->execute();
            if($statement->rowCount() > 0) {
                return true;
            } else {
                return false;
            }

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
?>
