<?php
$page_id = 'home';
include('includes/header.php');

require_once('classes/RideHelper.php');

$rideHelper = new RideHelper();

$rides = $rideHelper->getRides(4);


?>
<div class="row">
    <h1>Leader board</h1>
</div>
<?

foreach($rides as $r){
    echo $r.'<br>';
}

?>

<?php
include('includes/footer.php');
?>
