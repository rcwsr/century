<?php
$page_id = 'add';
include('includes/header.php');

require_once('classes/RideHelper.php');
require_once('classes/Ride.php');

$user_id = rand(1,10); //Hook for user auth.

if(isset($_POST['submit'])){
    $km = $_POST['km'];
    $url = $_POST['url'];
    $date = $_POST['date'];
    $details = $_POST['details'];
    
    $rideHelper = new RideHelper();
    $ride = new Ride();
    $ride = $ride->newRide($user_id, $km, $url, $date, $details);
    $rideHelper->addRide($ride);
}

?>
<div class="row">
    <div class="span12">
        <h1>Add Ride</h1>
    </div>
</div>

<div class="row">
    <div class="span12">
    <form method="post">
      <fieldset>
        <legend>Enter the details of your ride below</legend>
        
        <label>Distance*</label>
        <div class="input-append">
            <input class="input-mini" type="text" name="km" value="<?=rand(100,200)?>">
            <span class="add-on">km</span>
        </div>
        
        <label>Link to ride</label>
        <input type="text" name="url">
        
        <label>Date*</label>
        <input class="datepicker" type="text" name="date" value="2013-0<?=rand(1,2).'-'.rand(1,30)?>" >
        
        <label>Notes</label>
        <textarea name="details" class="input-xxlarge" rows="8"></textarea>
        
        
      </fieldset>
        <button  type="submit" class="btn" name="submit">Submit</button>
        <span class="help-inline">* denotes required field</span>
    </form>
    </div>
</div>
<script>
   $(function(){
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd'
    });
   });
</script>

<?php
include('includes/footer.php');
?>
