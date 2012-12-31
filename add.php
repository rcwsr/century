<?php
$page_id = 'add';
include('includes/header.php');
?>
<div class="row">
    <h1>Add Ride</h1>
</div>

<div class="row">
    <form>
      <fieldset>
        <legend>Enter the details of your ride below</legend>
        
        <label>Distance</label>
        <div class="input-append">
            <input class="input-mini" type="text" name="km">
            <span class="add-on">km</span>
        </div>
        
        <label>Link to ride</label>
        <input type="text" name="url">
        
        <label>Date</label>
        <input type="text" name="date">
        
        <label>Notes</label>
        <textarea  class="input-xxlarge" rows="8"></textarea>
        
        
      </fieldset>
        <button  type="submit" class="btn" >Submit</button>
    </form>
</div>


<?php
include('includes/footer.php');
?>
