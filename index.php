<?php
require_once 'header.php';

// If already logged in skip to main member page
if ($loggedin){
	header("Location: member.php");
    exit();
}

echo "
<div class='container text-center'>
  <div class='row' style='padding: 50px 0px 40px 0px'>
  
    <!--Show Game rules and overall top 3 for the game on the left-->  
    <div class='col-sm-5 text-left'>
      <h3>&nbsp;<img src='img/f2.png' alt='A Football'>&nbsp;&nbsp;&nbsp;English Football Prediction Game</h3><br>
      <ul>
	    <li>0 points for an incorrect result or no scores saved</li>
        <li>1 point for a correct result but an inexact scoreline</li>
	    <li>3 points for a correct result and an exact scoreline</li>
      </ul><br>
      <div style='padding-left:10px; padding-right:10px;margin-top:10px; border:2px solid #083045; border-radius:5px;'>
        <h3 class='text-center'>Overall League Leaders</h3>";
	    $query = "SELECT members.username as m, sum(predictions.points) as s from members_leagues
		          join members on members_leagues.username = members.username 
		          join predictions on members.username=predictions.username 
				  where members_leagues.league_name='Overall'
				  group by m order by s desc
				  limit 3";
        $league = showLeague($query);			
echo "
      </div>
    </div>
	<!--End of left column-->";

	//  The code that deals with the Log In form from header.php
	//  Brings user to logged-in main member page
    $userlog = $passlog = "";
    if (isset($_POST['userlog'])){
        $userlog = sanitizeString($_POST['userlog']);
        $passlog = sanitizeString($_POST['passlog']);

        $result = queryMysql("SELECT username,password FROM members
                          WHERE username='$userlog' AND password='$passlog'");
        if ($result->num_rows == 0){
            echo "<h3 class='text-center'>Invalid Username/Password</h3>";
        } 
        else {
            $_SESSION['user'] = (string)$userlog;  // retrieve numeric names
            $_SESSION['pass'] = (string)$passlog;  // retrieve numeric passwords
            header("Location: member.php");
            exit();
        }
    }
	
echo 
   "<div class='col-sm-2'>
    </div>
    
	<!--Sign up form on the right-->
    <div class='col-sm-5 text-left well well-sm' style='padding-left:30px; padding-right:30px; margin-top:20px; border:2px solid #083045; border-radius:5px'>
      <h1>Create an account</h1>
      <h3>Sign up and start playing</h3><br>";
	  
	  // The following php deals with the sign-up form below it.
      $user = $pass = $email = "";
      if (isset($_SESSION['user'])) destroySession();
      if (isset($_POST['user'])){
          $user = sanitizeString($_POST['user']);
          $pass = sanitizeString($_POST['pass']);
          $email = sanitizeString($_POST['email']);
          $emailResult = queryMysql("SELECT * FROM members WHERE email='$email'");
          $result = queryMysql("SELECT * FROM members WHERE username='$user'");
          if ($result->num_rows) {
              echo "<h4>Username already exists. Choose another.</h4><br>";
          }
          elseif ($emailResult->num_rows) {
              echo "<h4>Account with that email already set up.</h4><br>";
          }
          else {
              queryMysql("INSERT INTO members VALUES('$user', '$pass', '$email')");
		      queryMysql("INSERT INTO members_leagues VALUES('$user', 'Overall')");
              echo "<h4 class='text-left'>Account created<br><br>Log in at the top</h4><br><br>";
          }
      }
echo 
     "<form name='myForm' onsubmit='return validateForm()' method='post' action='index.php' class='form-horizontal'>
        <div class='form-group'>
          <div class='col-xs-12'>
            <input type='text' name='user' class='form-control' placeholder='Create Username' required pattern='^[a-zA-Z0-9_ -]*$'>
          </div>
        </div>
	    <div class='form-group'>
          <div class='col-xs-12'> 
            <input type='email' name='email' class='form-control' placeholder='Enter Email' required>
          </div>
        </div>
	    <div class='form-group'>
          <div class='col-xs-12'> 
            <input type='password' name='pass' class='form-control' placeholder='Create Password' required pattern='^[a-zA-Z0-9_-]*$'>
          </div>
        </div>
	    <div class='form-group'> 
          <div class='col-sm-12'>
            <button type='submit' class='btn btn-primary'>Start Playing</button>
          </div>
        </div>
      </form>
    </div>	
  </div>
</div>
</body>
</html>";
?>