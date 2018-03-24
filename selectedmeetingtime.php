


<?php

	/*** Connect to the server database. ***/
	$conn = mysqli_connect("localhost", "meetupap", "Hotmail28?", "meetupap_meetupdb");
	
	if (!$conn)
	{
		die('Could not connect: ' . mysql_error());
	}
	
	/*Get meeting time information. */
	$iInvitationId	=  (int) $_POST["iInvitationID"];
	$strSelectedMeetingTime	= $_POST["sSelectedMeetingTime"];
	
	
    $arr = null;
    
    $arr["intInvitationId"] = $iInvitationId;
    
    $arr["Event"] = "Select meeting time";
    
    
	/*** Insert the information in the selectedinvitationtime table. ***/
	$strSQL = "INSERT INTO `meetupap_meetupdb`.`selectedinvitationtime` (`InvitationID`, `SelectedMeetingTime`) 
	VALUES ('$iInvitationId', '$strSelectedMeetingTime')";


	$objQuery = mysqli_query($conn, $strSQL);
	if(!$objQuery) // Insert error.
	{
		$arr["Success"] = "0";  // (0=Failed , 1=Complete)
		$arr["error_message"] = "Cannot save selected meeting time data!";
		
		echo json_encode($arr);
		
		echo mysql_error();
		
		exit();
	}
	else
	{
		$arr["Success"] = "1";  // (0=Failed , 1=Complete)
		$arr["error_message"] = "Save selected meeting time successfully!";

	}
	
	/*** Get the inviter user email address in the invitation table. ***/
	$strSQL = "SELECT MeetingName, MeetingDescription, InviterEmail, InvitedEmail FROM invitations WHERE InvitationID = '".$iInvitationId."' ";
	$objQuery = mysqli_query($conn, $strSQL);
	//$objResult = mysql_fetch_array($objQuery, MYSQL_ASSOC);
	$objResult = mysqli_fetch_array($objQuery);
	if(!$objResult)   // invitationID does not exist.
	{
		$arr["Success"] = "0";   // (0=Failed , 1=Complete)
		$arr["error_message"] = "Invitation ID does not exist!";
	
		echo json_encode($arr);
		exit();
	}

	$strMeetingName = $objResult['MeetingName'];
	$strMeetingDescription = $objResult['MeetingDescription'];
	$strInviterEmail = $objResult['InviterEmail'];
	$strInvitedEmail = $objResult['InvitedEmail'];

	
	/*** Send Email about the selected meeting time to the meeting inviter. ***/
	$to = $strInviterEmail;
	$subject = "[Meet Up] The invited user ".$strInvitedEmail." has selected meeting time in meeting ".$strMeetingName;
	$message = "Hi, \n"."The invited user ".$strInvitedEmail." has selected meeting time! \n Selected meeting time is ".$strSelectedMeetingTime." \n"."The detail meeting information is: "." \n"." Meeting Name: ".$strMeetingName." \n"." Meeting Description: ".$strMeetingDescription." \n"." Invited user: ".$strInvitedEmail;
	$headers = "From: MeetUpAppServer@gmail.com \r\nReply-To: MeetUpAppServer@gmail.com";
	
	$retval = mail ($to,$subject,$message,$header);
		
	if( $retval == true )
	{
		$arr["Success"] = "1";  // (0=Failed , 1=Complete)
	
		$arr["error_message"] = "Send email to inviter user about the selected time successfully!";
	//	echo json_encode($arr);
	}
	else
	{
		$arr["Success"] = "0";  // (0=Failed , 1=Complete)
	
		$arr["error_message"] = "Email to the inviter user could not be sent...";
			
		echo json_encode($arr);
	
		exit();
	}
	
	/*** Send Push Notification to the inviter user about time selection. ***/
	//Get the device token of the inviter user from accountinfo table according to the Email address of the inviter user.
	$QueryDeviceTokenSQL = "SELECT DeviceToken FROM accountinfo WHERE 1
	AND Email = '".$strInviterEmail."'
	";
	$objQuery = mysqli_query($conn, $QueryDeviceTokenSQL);
	$objResult = mysqli_fetch_array($objQuery);
	if(!$objResult)   // Email not exists
	{
		$arr["Success"] = "0";   // (0=Failed , 1=Complete)
		$arr["error_message"] = "Email of the inviter user is not in the database! ";
	
		echo json_encode($arr);
		exit();
	}
	
	$deviceToken = $objResult['DeviceToken'];
	
	
	//Setup stream (connect to Apple Push Server)
	//$apnsHost = 'gateway.sandbox.push.apple.com';
	$apnsHost = 'gateway.push.apple.com';
	$apnsPort = 2195;
	$passphrase = 'meetup123456789';
	//$apnsCert = 'apns_dev.pem';
	$apnsCert = 'meetup_dis_pem.pem';
	
	$streamContext = stream_context_create();
	stream_context_set_option($streamContext, 'ssl', 'passphrase', $passphrase);
	stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);

	$fp = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
	

	if (!$fp) {
		//ERROR
		$arr["Success"] = "0";   // (0=Failed , 1=Complete)
		$arr["error_message"] = "Failed to connect (stream_socket_client) ";
		$arr["err"] = $err;
		$arr["errstrn"] = $errorString;
			
		// Return debug information.
		echo json_encode($arr);
		exit();
	
	} else {
	
		$arr["Success"] = "1";   // (0=Failed , 1=Complete)
		$arr["error_message"] = "Connected to APNS ";
		$arr["APNS"] = "1";   // (0=Failed , 1=Complete)
			
		$message = "In your meeting ".$strMeetingName.", invited user ".$strInvitedEmail."has selected a meeting time!";
			
		// Create the payload body
		$body['aps'] = array(
				'alert' => $message,
				'badge' => "1",
				'sound' => 'default',
				'content-available' => '1',
				'invitationID' => $iInvitationId,
				'eventType' => 2          // 1-------Notify invited user about coming new invitation. 2-------Notify inviter user about selected time. 3-------Notify inviter user about selected location. 4-------Notify inviter user about meeting starting. 5-------Notify invited user about meeting starting.
			);
			
			
		$arr["ReturnDeviceToken"] = $deviceToken;
			
		// Encode the payload as JSON
		$payload = json_encode($body);
			
		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
			
	//	$arr["error_message"] = "Connect (stream_socket_client) ";
			
		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));
			
		if (!$result)
			$arr["MessageToAPNS"] = "0";   // (0=Failed , 1=Complete)
		else
			$arr["MessageToAPNS"] = "1";
			
		fclose($fp);
	
	}
	
	// Return debug information.
	echo json_encode($arr);
	exit();

	
?>