<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <form action="test.php" method="post">
            <input type="text" name="km"/>
            <button type="submit" name="submit" >Submit</button>
         
        </form>
        <?php 
           
            require_once('classes/RideHelper.php');
            require_once('classes/Ride.php');
            if(isset($_POST['submit'])){
               $km = $_POST['km'];
               $rideHelper = new RideHelper();
               //$ride = new Ride();
               
              // $user_id= 1;
              // $url = 'http://wesdf.com';
               //$date = date('Y-m-d');
              // $details = 'details about ride';
               
               //$rideHelper->addRide($ride->newRide($user_id, $km, $url, $date, $details));
               
               $ride = $rideHelper->getRide($km);
               
               //print_r($ride);
               
               $ride->setDetails('test');
               $ride->setKm(167);
               
              // echo $ride->getPoints();
               //edit ride.        
               $rideHelper->editRide($ride);
               
           }
            
            
        ?>
        
    </body>
</html>
