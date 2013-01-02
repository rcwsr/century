<?php

$page_id = 'home';
include('includes/header.php');

require_once('classes/RideHelper.php');
require_once('classes/UserHelper.php');

$userHelper = new UserHelper();
$rideHelper = new RideHelper();
if(isset($_GET['username'])){
    $user_profile = $userHelper->getUser($_GET['username']);
}

?>
<div class="row">
    <div class="span12">
     <h1><?=$user_profile->getName();?>'s Rides</h1>
    </div>
</div>

<div class="row">
    <div class="span12">
        
             <?
             for($i = (int)date('j'); $i >= 1; $i--){
                 echo '<h2>'.date('F', mktime(0, 0, 0, $i)).'</h2>';
                 
                 $rides = $rideHelper->getRides($user_profile->getUser_id(), $i, (int)date('Y'));
                 
                 foreach($rides as $r){
                     echo $r.'<br>';
                 }
             }
             ?>
        
    </div>
</div>

<div class="row">
    <div class="span12">
     
    </div>
</div>





<?php
include('includes/footer.php');
?>
