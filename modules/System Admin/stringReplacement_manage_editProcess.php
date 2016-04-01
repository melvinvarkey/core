<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include "../../functions.php" ;
include "../../config.php" ;

//New PDO DB connection
try {
  	$connection2=new PDO("mysql:host=$databaseServer;dbname=$databaseName;charset=utf8", $databaseUsername, $databasePassword);
	$connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

@session_start() ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$gibbonStringID=$_GET["gibbonStringID"] ;
$search="" ;
if (isset($_GET["search"])) {
	$search=$_GET["search"] ;
}
$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/stringReplacement_manage_edit.php&gibbonStringID=$gibbonStringID&search=$search" ;

if (isActionAccessible($guid, $connection2, "/modules/System Admin/stringReplacement_manage_edit.php")==FALSE) {
	//Fail 0
	$URL.="&updateReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	//Proceed!
	if ($gibbonStringID=="") {
		//Fail1
		$URL.="&updateReturn=fail1" ;
		header("Location: {$URL}");
	}
	else {
		try {
			$data=array("gibbonStringID"=>$gibbonStringID); 
			$sql="SELECT * FROM gibbonString WHERE gibbonStringID=:gibbonStringID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			//Fail2
			$URL.="&deleteReturn=fail2" ;
			header("Location: {$URL}");
			exit() ;
		}
		
		if ($result->rowCount()!=1) {
			//Fail 2
			$URL.="&updateReturn=fail2" ;
			header("Location: {$URL}");
		}
		else {
			//Validate Inputs
			$original=$_POST["original"] ; 
			$replacement=$_POST["replacement"] ;
			$mode=$_POST["mode"] ;
			$caseSensitive=$_POST["caseSensitive"] ;
			$priority=$_POST["priority"] ;
			
			if ($original=="" OR $replacement=="" OR $mode=="" OR $caseSensitive=="" OR $priority=="") {
				//Fail 3
				$URL.="&updateReturn=fail3" ;
				header("Location: {$URL}");
			}
			else {
				//Write to database
				try {
					$data=array("original"=>$original, "replacement"=>$replacement, "mode"=>$mode, "caseSensitive"=>$caseSensitive, "priority"=>$priority, "gibbonStringID"=>$gibbonStringID); 
					$sql="UPDATE gibbonString SET original=:original, replacement=:replacement, mode=:mode, caseSensitive=:caseSensitive, priority=:priority WHERE gibbonStringID=:gibbonStringID" ;
					$result=$connection2->prepare($sql);
					$result->execute($data);
				}
				catch(PDOException $e) { 
					//Fail 2
					$URL.="&updateReturn=fail2" ;
					header("Location: {$URL}");
					exit() ;
				}

				//Update string list in session & clear cache to force reload
				setStringReplacementList($connection2, $guid) ;
				$_SESSION[$guid]["pageLoads"]=NULL ;
	
				//Success 0
				$URL.="&updateReturn=success0" ;
				header("Location: {$URL}");
			}
		}
	}
}
?>