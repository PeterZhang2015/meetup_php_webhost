
<?php  
	
	/*** Connect to the server database. ***/
    $conn = mysqli_connect("localhost","meetupio_test","Hotmail28","meetupio_meetup");

    if (!$conn)
    {
		die('Could not connect: ' . mysql_error());
    }
    

?>