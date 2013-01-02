<?php
$page_id = 'register';
include('includes/header.php');

//require_once('classes/UserHelper.php');
require_once('classes/User.php');
require_once('classes/Registration.php');


if(isset($_POST['submit'])){
    //$userHelper = new UserHelper();
    $register = new Registration();
    
    $username = $_POST['username'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $password_check = $_POST['password_check'];
    $forum_name = $_POST['forum_name'];
    $strava = $_POST['strava'];
    
    
    try{
        if($register->checkPasswords($password, $password_check)){
            $user = new User(   null,
                                $username,
                                $email,
                                $password,
                                null,
                                null,
                                $forum_name,
                                $strava,
                                $name);
            $register->addUser($user);
        }
    }
    catch(Exception $e){
       echo $e->getMessage();
    }
}
   

?>
<div class="row">
    <div class="span12">
        <h1>Register</h1>
    </div>
</div>

<div class="row">
    <div class="span12">
    <form method="post">
      <fieldset>
        <legend>Enter your details below</legend>
        
        <label>Username*</label>
        <input type="text" name="username">
        
        <label>Full name</label>
        <input type="text" name="name">
        
        <label>Email address*</label>
        <input type="text" name="email">
        
        <label>Password*</label>
        <input type="text" name="password">
        <input type="text" name="password_check">
        
        
        
        <label>Leicester Forest CC Forum username</label>
        <input type="text" name="forum_name">
        
        <label>Strava athlete ID</label>
        <div class="input-prepend">
             <span class="add-on">http://app.strava.com/athletes/</span>
            <input class="input-mini" type="text" name="strava">
        </div>
        
        
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