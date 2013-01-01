<?php

$page_id = 'home';
include('includes/header.php');

require_once('classes/RideHelper.php');
require_once('classes/UserHelper.php');
$userHelper = new UserHelper();
$users = $userHelper->getUsers();


$rideHelper = new RideHelper();

$rides = $rideHelper->getRides(4);


?>
<div class="row">
    <div class="span12">
     <h1>Leader board</h1>
    </div>
</div>

<div class="row">
    <div class="span12">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Total</th>
                    <th>January</th>
                    <th>February</th>
                </tr>
            </thead>
            
            <tbody>
                <?
                foreach($users as $u){
                   echo '<tr>
                        <td>'.$u->getName().'</td>
                        <td>'.$u->getPoints().'</td>
                        <td>'.$u->points('1',date('Y')).'</td>
                        <td>'.$u->points('2',date('Y')).'</td>

                    </tr>';
                }
               
                ?>
   
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="span12">
     <h1>Latest Rides</h1>
    </div>
</div>
<?

foreach($rides as $r){
    //echo $r.'<br>';
}

?>



<?php
include('includes/footer.php');
?>
