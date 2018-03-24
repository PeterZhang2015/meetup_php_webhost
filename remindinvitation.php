



 <?php

	 /*** Connect to the server database. ***/
	 $conn = mysqli_connect("localhost", "meetupap", "Hotmail28?", "meetupap_meetupdb");
	 
	 if (!$conn)
	 {
	 	die('Could not connect: ' . mysql_error());
	 }
		
	/*** Select all meeting time from selectedinvitationtime ***/
	$strSQL = "SELECT * FROM selectedinvitationtime WHERE 1";
	

	$objTimeQuery = mysqli_query($conn, $strSQL) or die(mysql_error());

	
	$intNumRows = mysqli_num_rows($objTimeQuery);
	
	$arr = null;
	
	$arr["SelectedMeetingNum"] = $intNumRows;
	$arr["Success"] = "0";
	
	if($intNumRows==0)   // No record matched!
	{
		
		$arr["error_message"] = "Row number is zero.";
		echo json_encode($arr);
		exit();
	}
	else     // Find the mathced record according to current time.  
	{
		
		date_default_timezone_set('Australia/Adelaide');  // Set time zone.
		$remindTime = date('Y-m-d H:i:s', strtotime('+1 hour')); // Get one hour after current date and time.
		

		while($row = mysqli_fetch_array($objTimeQuery))	
		{
			$haveRemindedFlag = $row["HaveRemindedFlag"];
			
			if ($haveRemindedFlag == 1)
			{
				continue;
			}


			$selectedMeetingTime = $row["SelectedMeetingTime"];   // Loop the selectedMeetingTime table.
			
			$arr["lastRowTime"] = $remindTime;
			$arr["selectedMeetingTime"] = $selectedMeetingTime;
			
			/*** Remind user at one hour before selected meeting time, which means meeting time is one hour after current time. ***/
			if ($selectedMeetingTime <= $remindTime)
			{
				$arr["Success"] = "1";
				
		//		$arr["TimeMatchResult"] = "1";
				
				$invitationID = $row["InvitationID"];

					
				// Get meeting information from meeting time.
				$strSQL = "SELECT MeetingName, MeetingDescription, InviterEmail, InvitedEmail FROM invitations WHERE InvitationID = '".$invitationID."' ";
				$objQuery = mysqli_query($conn, $strSQL);
				$result = mysqli_fetch_array($objQuery);
				
				$strMeetingName = $result["MeetingName"];
				$strMeetingDescription = $result["MeetingDescription"];
				$strInviterEmail = $result["InviterEmail"];
				$strInvitedEmail = $result["InvitedEmail"];
	
				$strSQL = "SELECT SelectedInvitationLocation FROM selectedinvitationlocation WHERE InvitationID = '".$invitationID."' ";
				$objQuery = mysqli_query($conn, $strSQL);
				$result = mysqli_fetch_array($objQuery);
				
				$srtSelectedMeetingLocation = $result["SelectedInvitationLocation"];
			

					
				/* Send Email to remind the invitor of the meeting. */
				
				//define the receiver of the email
				$to = $strInviterEmail;
				//define the subject of the email
				$subject = "[Meet Up] Important remind: The meeting ".$strMeetingName." will be hold in an hour.";
				$message = "Hi, \n"."The following meeting will be hold in an hour, please be prepare for the meeting! \n Meeting Name is: ".$strMeetingName."\n Meeting Description is: ".$strMeetingDescription."\n Meeting Time is: ".$selectedMeetingTime."\n Meeting Location is: ".$srtSelectedMeetingLocation."\n Install Meet Up application and enjoy your meeting even more! \n http://www.hyperlinkcode.com \n";		
				//$headers = "From: MeetUpAppServer@gmail.com";
				$headers = "From: MeetUpAppServer@gmail.com \r\nReply-To: MeetUpAppServer@gmail.com";
				$retval = mail( $to, $subject, $message, $headers );
				
				
				/* Send Email to remind the invited user of the meeting. */
				
				//define the receiver of the email
				$to = $strInvitedEmail;
				//define the subject of the email
				$subject = "[Meet Up] Important remind: The meeting ".$strMeetingName." will be hold in an hour.";
				$message = "Hi, \n"."The following meeting will be hold in an hour, please be prepare for the meeting! \n Meeting Name is: ".$strMeetingName."\n Meeting Description is: ".$strMeetingDescription."\n Meeting Time is: ".$selectedMeetingTime."\n Meeting Location is: ".$srtSelectedMeetingLocation."\n Install Meet Up application and enjoy your meeting even more! \n http://www.hyperlinkcode.com \n";
				//$headers = "From: MeetUpAppServer@gmail.com";
				$headers = "From: MeetUpAppServer@gmail.com \r\nReply-To: MeetUpAppServer@gmail.com";
				$retval = mail( $to, $subject, $message, $headers );
				
			
				/* Send push notification to remind the invitor of the meeting. */
				
				//Get the device token of the invitor user from accountinfo table according to the Email address of the invitor user.
				$QueryDeviceTokenSQL = "SELECT DeviceToken FROM accountinfo WHERE 1
				AND Email = '".$strInviterEmail."'
				";
				$objQuery = mysqli_query($conn, $QueryDeviceTokenSQL);
				$objResult = mysqli_fetch_array($objQuery);
				if(!$objResult)   // Email not exists
				{
			//		$arr["error_message"] = "strInviterEmail not exit.";
		//			echo json_encode($arr);
			//		exit();
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
		//			$arr["error_message"] = "Caller APNS failed";
		//			echo json_encode($arr);
		//			exit();
				
				} else {
	
					$message = "Important remind: the meeting ".$strMeetingName." will be hold in an hour!";
						
					// Create the payload body
					$body['aps'] = array(
							'alert' => $message,
							'badge' => "1",
							'sound' => 'default',
							'content-available' => '1',
							'invitationID' => $invitationID,
							'eventType' => 4     // 1-------Notify invited user about coming new invitation. 2-------Notify inviter user about selected time. 3-------Notify inviter user about selected location. 4-------Notify inviter user about meeting starting. 5-------Notify invited user about meeting starting.
					);
		
					// Encode the payload as JSON
					$payload = json_encode($body);
						
					// Build the binary notification
					$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
			
					// Send it to the server
					fwrite($fp, $msg, strlen($msg));

					fclose($fp);
				
				}
				
				
				/* Send push notification to remind the invited user of the meeting. */
				//Get the device token of the invited user from accountinfo table according to the Email address of the invited user.
				$QueryDeviceTokenSQL = "SELECT DeviceToken FROM accountinfo WHERE 1
				AND Email = '".$strInvitedEmail."'
				";
				$objQuery = mysqli_query($conn, $QueryDeviceTokenSQL);
				$objResult = mysqli_fetch_array($objQuery);
				if(!$objResult)   // Email not exists
				{
			//		$arr["error_message"] = "strInvitedEmail not exit.";
		//			echo json_encode($arr);
			//		exit();
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
			//		$arr["error_message"] = "Called APNS failed";
			//		echo json_encode($arr);
				//	exit();
				
				} else {
				
					$message = "Important remind: the meeting ".$strMeetingName." will be hold in an hour!";
				
					// Create the payload body
					$body['aps'] = array(
							'alert' => $message,
							'badge' => "1",
							'sound' => 'default',
							'content-available' => '1',
							'invitationID' => $invitationID,
							'eventType' => 5     // 1-------Notify invited user about coming new invitation. 2-------Notify inviter user about selected time. 3-------Notify inviter user about selected location. 4-------Notify inviter user about meeting starting. 5-------Notify invited user about meeting starting.
					);
				
					// Encode the payload as JSON
					$payload = json_encode($body);
				
					// Build the binary notification
					$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
						
					// Send it to the server
					fwrite($fp, $msg, strlen($msg));
				
					fclose($fp);
				
				}
					
 
				 $strSQL = "UPDATE selectedinvitationtime
				 SET HaveRemindedFlag = 1
				 WHERE 1
				 AND InvitationID = '".$invitationID."'
				 ";

				 $objQuery = mysqli_query($conn, $strSQL) or die(mysql_error());
 
		//		 $arr["error_message"] = "Update flag successfully";
	
			}// end of if ($selectedMeetingTime <= $remindTime)
				
	
		} // end of while(($row = mysql_fetch_array($objQuery)) && ($haveRemindedFlag != 1))

		echo json_encode($arr);
		exit();
	}
	

	
	//echo json_encode($arr);


//	mysql_close($objConnect);
	
?>