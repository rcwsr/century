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
                   <?
                    for($i = 1; $i <= (int)date('j'); $i++){
                        echo '<th>'.date('F', mktime(0, 0, 0, $i)).'</th>';
                    }
                   ?>
                </tr>
            </thead>
            
            <tbody>
                <?
                foreach($users as $u){
                   echo '<tr>
                        <td><a href="#">'.$u->getName().'</a></td>
                        <td>'.$u->getPoints().'</td>';
                        
                     
                    for($i = 1; $i <= (int)date('j'); $i++){
                        echo '<td>'.$u->getPoints($i,date('Y')).'</td>';
                    }

                   echo '</tr>';
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
<?php
include('includes/footer.php');
?>
