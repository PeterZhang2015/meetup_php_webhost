<?php

	/*** Connect to the server database. ***/
	$conn = mysqli_connect("localhost", "meetupap", "Hotmail28?", "meetupap_meetupdb");
	
	if (!$conn)
	{
		die('Could not connect: ' . mysql_error());
	}

	$strInviterEmail	 = $_POST["sInviterEmail"];


	$arr = null;
	
	$arr["sInviterEmail"] = $strInviterEmail;
	$arr["Success"] = "0";
	
	
	/*** Select the record . ***/
	$strSQL = "SELECT * FROM invitations WHERE 1 
		";

	$objQuery = mysqli_query($conn, $strSQL);
	if(!$objQuery) //  error.
	{
		$arr["Success"] = "0";  // (0=Failed , 1=Complete)
		$arr["error_message"] = "no sent inviatation exist!";
	
		echo json_encode($arr);
	
		echo mysql_error();
	
		exit();
	}

	$j = 0;
	

// 	$row = mysql_fetch_array($objQuery);
	
// 	$arr["row"] = $row;
	
	while($row = mysqli_fetch_array($objQuery))
	{
		
		$rowInviterEmail = $row["InviterEmail"];
		
		if ($rowInviterEmail == $strInviterEmail)
		{
		
			$arr["Success"] = "1";
	
			// Get meeting information
	    	$invitationID = $row["InvitationID"];
			$strMeetingName = $row["MeetingName"];
			$strMeetingDescription = $row["MeetingDescription"];
			$strInvitedEmail = $row["InvitedEmail"];
			
			//Get all meeting time info
			$strSQL = "SELECT * FROM invitationtimes WHERE InvitationID = '".$invitationID."' ";
			$objAllTimeQuery = mysqli_query($conn, $strSQL);
			
			$i = 0;
			while($objResult = mysqli_fetch_array($objAllTimeQuery))
			{
				$arrayAllTime[$i] = $objResult["MeetingTime"];
				$i ++;
			}
			
			//Get all meeting location info
			$strSQL = "SELECT * FROM invitationlocations WHERE InvitationID = '".$invitationID."' ";
			$objAllTimeQuery = mysqli_query($conn, $strSQL);
			
			$i = 0;
			while($objResult = mysqli_fetch_array($objAllTimeQuery))
			{
				$arrayAllLocation[$i] = $objResult["MeetingLocation"];
				$i ++;
			}
			
			
			//Get selected meeting time info
			$strSQL = "SELECT * FROM selectedinvitationtime WHERE InvitationID = '".$invitationID."' ";
			$objTimeQuery = mysqli_query($conn, $strSQL);
			$objResult = mysqli_fetch_array($objTimeQuery);
			if ($objResult)
			{
				$srtSelectedMeetingTime = $objResult["SelectedMeetingTime"];
				$haveSelectedMeetingTimeFlag = 1;
			}
			else {
				$srtSelectedMeetingTime = "";
				$haveSelectedMeetingTimeFlag = 0;
			}
			
			
			//Get selected meeting location info
			$strSQL = "SELECT SelectedInvitationLocation FROM selectedinvitationlocation WHERE InvitationID = '".$invitationID."' ";
			$objLocationQuery = mysqli_query($conn, $strSQL);
			$objResult = mysqli_fetch_array($objLocationQuery);
			if ($objResult)
			{
				$srtSelectedMeetingLocation = $objResult["SelectedInvitationLocation"];
				$haveSelectedMeetingLocationFlag = 1;
			}
			else {
			
				$srtSelectedMeetingLocation = "";
				$haveSelectedMeetingLocationFlag = 0;
			}
			
		
	
			$aSentInvitationInfo[$j] = array(
					"InvitationId" => intval($invitationID),
					"MeetingName" => $strMeetingName,
					"MeetingDescription" => $strMeetingDescription,
					"MeetingTime" => $arrayAllTime,
					"MeetingLocation" => $arrayAllLocation,
					"InvitedFriendEmail" => $strInvitedEmail,
					"InviterFriendEmail" => $strInviterEmail,
					"selectedMeetingTime" => $srtSelectedMeetingTime,
					"selectedMeetingLocation" => $srtSelectedMeetingLocation,
					"haveSelectedMeetingTimeFlag" => $haveSelectedMeetingLocationFlag,
					"haveSelectedMeetingLocationFlag" => $haveSelectedMeetingLocationFlag,
			);
			$j ++;
		
		}// end of if ($rowInviterEmail == $strInviterEmail)
	}// end of while($row = mysql_fetch_array($objQuery))
	
	if ($j == 0)
	{
		$arr["Success"] = "0";
	}
	
	$arr["invitationNum"] = $j;
	$arr["i"] = $i;

	$arr["arraySentMeetingInfo"] = $aSentInvitationInfo;

	echo json_encode($arr);
	exit();

	/**
	return 
		 // (0=Failed , 1=Complete)
		 // MemberID
		 // Error Message
	*/
	

	
?>