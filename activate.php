<?php
$page_id = 'activate';
include('includes/header.php');

require_once('classes/UserHelper.php');
require_once('classes/User.php');
require_once('classes/Registration.php');

$key ='';
if(isset($_GET['k'])){
    $register = new Registration();
    $key = $_GET['k'];
    
}
   

?>
<div class="row">
    <div class="span12">
        <h1>Activate your account</h1>
    </div>
</div>

<div class="row">
    <div class="span12">
          <?
            if($key != ''){
                try{
                     $register->activateUser($key);
                     echo "Your account has been activated";
                }
                catch(Exception $e){
                    echo $e->getMessage();
                }
               
                
            }
            else{
                echo "No key";
            }
          ?>
    </div>
</div>

<?php
include('includes/footer.php');
?>