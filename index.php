<html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Test Server</title>
        </head>
        <body>
            <div style="text-align:center; color:red">
            <?php
                $link=mysqli_connect("localhost","meetupap","Hotmail28?");

                if(!$link) echo "MySQL database link error!";
                else echo "MySQL database link success!";
            ?>
            </div>
            <br/>
            <?php phpinfo(); ?>
        </body>
</html>