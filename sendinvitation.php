


<?php

	/*** Connect to the server database. ***/
	$conn = mysqli_connect("localhost", "meetupap", "Hotmail28?", "meetupap_meetupdb");
	
	if (!$conn)
	{
		die('Could not connect: ' . mysql_error());
	}
	
	$strInviterEmail	= $_POST["sInviterEmail"];
    $strInvitedEmail	= $_POST["sInvitedEmail"];
	$strMeetingName		= $_POST["sMeetingName"];
	$strMeetingDescription		= $_POST["sMeetingDescription"];
	
	
	//$arrayMeetingTime	= (Array)$_POST["asMeetingTime"];
	//$arrayMeetingLocation = (Array)$_POST["asMeetingLocation"];
	
	$arrayMeetingTime = json_decode($_POST["asMeetingTime"], true);
	$arrayMeetingLocation = json_decode($_POST["asMeetingLocation"], true);
	
    $iMeetingTimeNum	= $_POST["iMeetingTimeNum"];
    $iMeetingLocationNum	= $_POST["iMeetingLocationNum"];
    
      
    $arr = null;
    
    /* Just for test. */
    $arr["InviterEmail"] = $strInviterEmail;
    $arr["InvitedEmail"] = $strInvitedEmail;
    $arr["MeetingName"] = $strMeetingName;
    $arr["MeetingDescription"] = $strMeetingDescription;
    $arr["MeetingTime"] = $arrayMeetingTime;
    $arr["MeetingLocation"] = $arrayMeetingLocation;
    $arr["MeetingTimeNum"] = $iMeetingTimeNum;
    $arr["MeetingLocationNum"] = $iMeetingLocationNum;

    
    /*** Insert the information in the invitation table. ***/
    $strSQL = "INSERT INTO `meetupap_meetupdb`.`invitations` (`InviterEmail`, `InvitedEmail`, `MeetingName`, `MeetingDescription`)
    VALUES ('$strInviterEmail', '$strInvitedEmail', '$strMeetingName', '$strMeetingDescription')";
    
    $objQuery = mysqli_query($conn, $strSQL);
    
    if(!$objQuery) // Insert error.
    {
    
	    $arr["Success"] = "0";  // (0=Failed , 1=Complete)
	    
	    $arr["error_message"] = "Cannot save invitation data!";
	    
	    echo json_encode($arr);
	    
	    echo mysql_error();
	    
	    exit();
    
    }
    else
    {
    
	    $invitationID = mysqli_insert_id($conn);
	    
	    $arr["InvitationID"] = $invitationID;
	    
	    /*** Insert the information in the invitationtimes table. ***/
	    foreach($arrayMeetingTime as $value)
	    {
		    $strSQL = "INSERT INTO `meetupap_meetupdb`.`invitationtimes` (`InvitationID`, `MeetingTime`)
		    VALUES ('$invitationID', '$value')"; 
		    
		    	
		    $objQuery = mysqli_query($conn, $strSQL);
		    	
		    if(!$objQuery) // Insert error.
		    {
		    	
				$arr["Success"] = "0";  // (0=Failed , 1=Complete)
	    		
				$arr["error_message"] = "Cannot save time data!";
	    	
	    		echo json_encode($arr);
	    	
			    echo mysql_error();
			    	
			    exit();
			    break;
	    	
		    }
	    }
	    
		/*** Insert the information in the invitationlocations table. ***/
	    foreach($arrayMeetingLocation as $value)
		{
			$arr["MeetingLocationLast"] = $value;
			$strSQL = "INSERT INTO `meetupap_meetupdb`.`invitationlocations` (`InvitationID`, `MeetingLocation`)
						VALUES ('$invitationID', '$value')";
	     	
			$objQuery = mysqli_query($conn, $strSQL);
	     	
			if(!$objQuery) // Insert error.
			{
	     	
				$arr["Success"] = "0";  // (0=Failed , 1=Complete)
	     	
				$arr["error_message"] = "Cannot save location data!";
	     	
				echo json_encode($arr);
	     			
				echo mysql_error();
	     	
				exit();
				break;
	     	
			}
		}

		/*** Send Email to the invited user about this invitation. ***/
		
		
		$strMeetingTime = implode("\n", $arrayMeetingTime);
		$strMeetingLocation = implode("\n", $arrayMeetingLocation);
		//define the receiver of the email
		$to = $strInvitedEmail;
		//define the subject of the email
		
		//$subject = "Test email";
		//define the message to be sent. Each line should be separated with \n
		//$message = "Hi, \n"."An invitation from ".$strInviterEmail." has arrived! \n Meeting Name is: ".$strMeetingName."\n Meeting Description is: ".$strMeetingDescription."\n Meeting Time is: ".$strMeetingTime."\n Meeting Location is: ".$strMeetingLocation."\n\n Meet Up is an app for catching up with a friend. \n If you install this app, it will be easy to send or receive a meeting invitation! \n Install Meet Up application and enjoy your meeting even more! \n \n";
		//$message = "meetup";
		//define the headers we want passed. Note that they are separated with \r\n

	//	$headers = "From: ".$strInviterEmail."\r\nReply-To: ".$strInviterEmail;
		
// 		$subject = "dinner";
// 		$message = "Have dinner together";
		
		$subject = "[Meet Up] An invitation from ".$strInviterEmail." has arrived!";	
		$message = "Hi, \n"."An invitation from ".$strInviterEmail." has arrived! \n Meeting Name is: ".$strMeetingName."\n Meeting Description is: ".$strMeetingDescription."\n Meeting Time is: ".$strMeetingTime."\n Meeting Location is: ".$strMeetingLocation."\n\n Meet Up is an app for catching up with a friend. \n If you install this app, it will be easy to send or receive a meeting invitation! \n Install Meet Up application and enjoy your meeting even more! \n http://www.hyperlinkcode.com \n";
		
		//$headers = "From: MeetUpAppServer@gmail.com";  
		$headers = "From: MeetUpAppServer@gmail.com \r\nReply-To: MeetUpAppServer@gmail.com";
		$retval = mail( $to, $subject, $message, $headers );
		
		
// 		$strMeetingTime = implode("\n", $arrayMeetingTime);
// 		$strMeetingLocation = implode("\n", $arrayMeetingLocation);
// 		$to = $strInvitedEmail;
// 		$subject = "[Meet Up]An invitation from ".$strInviterEmail." has arrived!";		
// 		$message = "Hi, \n"."An invitation from ".$strInviterEmail." has arrived! \n Meeting Name is: ".$strMeetingName."\n Meeting Description is: ".$strMeetingDescription."\n Meeting Time is: ".$strMeetingTime."\n Meeting Location is: ".$arrayMeetingLocation."\n\n Meet Up is an app for catching up with a friend. \n If you install this app, it will be easy to send or receive a meeting invitation! \n Install Meet Up application and enjoy your meeting even more! \n http://www.hyperlinkcode.com \n";

		
// 		$headers = "From: MeetUp@example.com\r\nReply-To: MeetUp@example.com";
		
// 		$retval = mail ($to,$subject,$message,$header);
		 
		if( $retval == true )
		{
			$arr["Success"] = "1";  // (0=Failed , 1=Complete)
			 
			$arr["error_messageEmail"] = "Save the invitation successfully!";

		}
		else
		{
			$arr["Success"] = "0";  // (0=Failed , 1=Complete)
			 
			$arr["error_messageEmail"] = "Email to the invited user could not be sent...";
			
			echo json_encode($arr);
			 
			exit();
		}
		
		/*** Send Push Notification to the invited user about this invitation. ***/
		//Get the device token of the invited user from accountinfo table according to the Email address of the invited user.
		$QueryDeviceTokenSQL = "SELECT DeviceToken FROM accountinfo WHERE 1
		AND Email = '".$strInvitedEmail."'
		";
		$objQuery = mysqli_query($conn, $QueryDeviceTokenSQL);
		$objResult = mysqli_fetch_array($objQuery);
		if(!$objResult)   // Email not exists
		{
			//$arr["Success"] = "0";   // (0=Failed , 1=Complete)
			$arr["error_message"] = "Email of the invited user is not in the database! ";
		
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
		
		//	stream_context_set_option($ctx, 'ssl', 'passphrase', 'apns_dev_key.pem');
	//	stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
		
		$fp = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
		
		
// 		$ctx = stream_context_create();
// 		stream_context_set_option($ctx, 'ssl', 'passphrase', 'password_for_apns.pem_file');
// 		stream_context_set_option($ctx, 'ssl', 'local_cert', 'apns_pem_certificate.pem');
// 		$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
	//	stream_set_blocking ($fp, 0);
		// This allows fread() to return right away when there are no errors. But it can also miss errors during
		//  last  seconds of sending, as there is a delay before error is returned. Workaround is to pause briefly
		// AFTER sending last notification, and then do one more fread() to see if anything else is there.
		
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
			
			$message = "You received an invitation from ".$strInviterEmail;
			
			// Create the payload body
			$body['aps'] = array(
					'alert' => $message,
					'badge' => "1",
					'sound' => 'default',
					'content-available' => '1',
					'invitationID' => $invitationID,
					'eventType' => 1     // 1-------Notify invited user about coming new invitation. 2-------Notify inviter user about selected time. 3-------Notify inviter user about selected location. 4-------Notify inviter user about meeting starting. 5-------Notify invited user about meeting starting.
			);
			
			
			$arr["ReturnDeviceToken"] = $deviceToken;
			
			// Encode the payload as JSON
			$payload = json_encode($body);
			
			// Build the binary notification
			$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
			
			$arr["error_message"] = "Connect (stream_socket_client) ";
	
			
			// Send it to the server
			$result = fwrite($fp, $msg, strlen($msg));
			
			if (!$result)
				$arr["MessageToAPNS"] = "0";   // (0=Failed , 1=Complete)
			else
				$arr["MessageToAPNS"] = "1";
			
	
// 		    $apple_error_response = fread($fp, 6);

//     		if ($apple_error_response) {
    
//     		// unpack the error response (first byte 'command" should always be 8)
//     		$error_response = unpack('Ccommand/Cstatus_code/Nidentifier', $apple_error_response);
    
//     		if ($error_response['status_code'] == '0') {
//     			$error_response['status_code'] = '0-No errors encountered';
    
//     		} else if ($error_response['status_code'] == '1') {
//     			$error_response['status_code'] = '1-Processing error';
    
//     		} else if ($error_response['status_code'] == '2') {
//     			$error_response['status_code'] = '2-Missing device token';
    
//     		} else if ($error_response['status_code'] == '3') {
//     			$error_response['status_code'] = '3-Missing topic';
    
//     		} else if ($error_response['status_code'] == '4') {
//     			$error_response['status_code'] = '4-Missing payload';
    
//     		} else if ($error_response['status_code'] == '5') {
//     			$error_response['status_code'] = '5-Invalid token size';
    
//     		} else if ($error_response['status_code'] == '6') {
//     			$error_response['status_code'] = '6-Invalid topic size';
    
//     		} else if ($error_response['status_code'] == '7') {
//     			$error_response['status_code'] = '7-Invalid payload size';
    
//     		} else if ($error_response['status_code'] == '8') {
//     			$error_response['status_code'] = '8-Invalid token';
    
//     		} else if ($error_response['status_code'] == '255') {
//     			$error_response['status_code'] = '255-None (unknown)';
    
//     		} else {
//     			$error_response['status_code'] = $error_response['status_code'].'-Not listed';
    
//     		}
    
// 			$arr["status_code"] = $error_response['status_code'];
			
//     		}  // end of if ($apple_error_response)
	
			
// 			checkAppleErrorResponse($fp);
    	
	
// 			// Workaround to check if there were any errors during the last seconds of sending.
// 			// Pause for half a second. 
// 			// Note I tested this with up to a 5 minute pause, and the error message was still available to be retrieved
// 			usleep(500000); 
			
// 			checkAppleErrorResponse($fp);

			fclose($fp);
		
		}
	
	    // Return debug information.
		echo json_encode($arr);
		exit();
    
    }
    
    mysql_close($objConnect);
    
 
    
    // FUNCTION to check if there is an error response from Apple
    // Returns TRUE if there was and FALSE if there was not
    function checkAppleErrorResponse($fp) {
    
    	//byte1=always 8, byte2=StatusCode, bytes3,4,5,6=identifier(rowID).
    	// Should return nothing if OK.
    
    	//NOTE: Make sure you set stream_set_blocking($fp, 0) or else fread will pause your script and wait
    	// forever when there is no response to be sent.
    
    	$apple_error_response = fread($fp, 6);
    
    	if ($apple_error_response) {
    
    		// unpack the error response (first byte 'command" should always be 8)
    		$error_response = unpack('Ccommand/Cstatus_code/Nidentifier', $apple_error_response);
    
    		if ($error_response['status_code'] == '0') {
    			$error_response['status_code'] = '0-No errors encountered';
    
    		} else if ($error_response['status_code'] == '1') {
    			$error_response['status_code'] = '1-Processing error';
    
    		} else if ($error_response['status_code'] == '2') {
    			$error_response['status_code'] = '2-Missing device token';
    
    		} else if ($error_response['status_code'] == '3') {
    			$error_response['status_code'] = '3-Missing topic';
    
    		} else if ($error_response['status_code'] == '4') {
    			$error_response['status_code'] = '4-Missing payload';
    
    		} else if ($error_response['status_code'] == '5') {
    			$error_response['status_code'] = '5-Invalid token size';
    
    		} else if ($error_response['status_code'] == '6') {
    			$error_response['status_code'] = '6-Invalid topic size';
    
    		} else if ($error_response['status_code'] == '7') {
    			$error_response['status_code'] = '7-Invalid payload size';
    
    		} else if ($error_response['status_code'] == '8') {
    			$error_response['status_code'] = '8-Invalid token';
    
    		} else if ($error_response['status_code'] == '255') {
    			$error_response['status_code'] = '255-None (unknown)';
    
    		} else {
    			$error_response['status_code'] = $error_response['status_code'].'-Not listed';
    
    		}
    
			$arr["status_code"] = $error_response['status_code'];
    		return true;
    	}
    
    	return false;
    }
    
    
    
?>