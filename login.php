<?php


    $conn = mysqli_connect("localhost", "meetupap", "Hotmail28?", "meetupap_meetupdb");

    if (!$conn)
    {
        die('Could not connect: ' . mysql_error());
    }
    
    /*** for Sample 
	$_POST["sEmail"] = "test@gmail.com"; 
	$_POST["sPassword"] = "123";  
	*/
	
	$strEmail	 = $_POST["sEmail"];
	$strPassword = $_POST["sPassword"];
	$strDeviceToken	 = $_POST["devicetoken"];
	
	$iLoginWithFacebook	 = (int)$_POST["iLoginWithFacebook"];
	
//	echo var_dump($var_name1)."<br>";

	$arr["LoginWithFacebook"] = $iLoginWithFacebook;

	if ($iLoginWithFacebook == 1)
	{
		$arr["Success"] = "1";
		$arr["Email"] = $strEmail;
		$arr["error_message"] = "Login Successfully";
		
		/*** Select the record in the accountinfo table according to the received Email and Password. ***/
		$strSQL = "SELECT * FROM accountinfo WHERE 1 
			AND Email = '".$strEmail."'  
			";
	}
	else 
	{

		/*** Select the record in the accountinfo table according to the received Email and Password. ***/
		$strSQL = "SELECT * FROM accountinfo WHERE 1 
			AND Email = '".$strEmail."'  
			AND Password = '".$strPassword."'  
			";
	
	}
	
	$objQuery = mysqli_query($conn, $strSQL);
	$objResult = mysqli_fetch_array($objQuery);
	$intNumRows = mysqli_num_rows($objQuery);
	if($intNumRows==0)   // No record matched!
	{
		$arr["Success"] = "0";
		$arr["Email"] = $strEmail;
		$arr["error_message"] = "Incorrect Username and Password";
		
		echo json_encode($arr);
		echo mysql_error();
		exit();
	}
	else     // Find the mathced record according to the received Email and Password.  
	{
		/*** Update device token in accountinfo. ***/
		if ($objResult["DeviceToken"] != $strDeviceToken)
		{
			
			$strSQL = "UPDATE accountinfo
			SET DeviceToken = '".$strDeviceToken."'
			WHERE 1
			AND Email = '".$strEmail."'
			";
			
			$objQuery = mysqli_query($conn, $strSQL);
			if(!$objQuery)   // Update error!
			{
				$arr["Success"] = "0";
				$arr["Email"] = $strEmail;
				$arr["error_message"] = "Update device token error. ";
				echo json_encode($arr);
				echo mysql_error();
				exit();
			}
			else {
				$arr["Success"] = "1";
				$arr["Email"] = $strEmail;
				$arr["error_message"] = "Login Successfully";
				
				echo json_encode($arr);
				exit();
				
			}
		}
		else {
			
			$arr["Success"] = "1";
			$arr["Email"] = $strEmail;
			$arr["error_message"] = "Login Successfully";
			
			
			date_default_timezone_set('Australia/Adelaide');  // Set time zone.
			$currentTime = date('Y-m-d H:i:s');
			$remindTime = date('Y-m-d H:i:s', strtotime('+1 hour')); // Get one hour after current date and time.
			
			$arr["currentTime"] = $currentTime;
			$arr["remindTime"] = $remindTime;
			
			echo json_encode($arr);
			exit();	
		}
	

	}

	

	/**
	return 
		 // (0=Failed , 1=Complete)
		 // MemberID
		 // Error Message
	*/
	

?>