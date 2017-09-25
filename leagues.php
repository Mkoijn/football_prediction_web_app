<?php
require_once 'header.php';

// Make sure user is logged in. If not, redirect to index.php
if (isset($_SESSION['user'])){
	$username = $_SESSION['user'];	
    $loggedin = TRUE;	
}
else $loggedin = FALSE;

if (!$loggedin){
    header("Location: index,php");
    exit();
}

echo "
<div class='container'>
  <div class='row' style='padding: 30px 0px 20px 0px'>
    
	<!--Show My Leagues dropdown button on the left row-->
    <div class='col-sm-4 text-center' style='margin-top:10px; padding-bottom:10px'>
	  <div class='btn-group'>
        <button type='button' class='btn btn-lg btn-primary dropdown-toggle' 
		        data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
         My Leagues&nbsp;&nbsp;&nbsp;&nbsp;<span class='caret'></span>
        </button><ul class='dropdown-menu'>";
	    $query = "select local_leagues.league_name from local_leagues 
	            join members_leagues on local_leagues.league_name = members_leagues.league_name 
			    where members_leagues.username='$username'";
	    $myLeagues = queryMysql($query);
		
		// Create a form for each league that user is in
		// When they click on a league, the table is shown.
		$i=0;
	    while($row = $myLeagues->fetch_array()){			
	        ${"res" . $i} = $row['league_name'];
	        if(${"res" . $i} != 'Overall'){
		    echo '<li><form action="leagues.php" method="post">
		          <input type="hidden" name="res' . $i . '" value="">
                  <button type="submit" name="getleague" value="getleague" class="btn-link">
				  '.$row['league_name'].'</button>
                  </form></li>';
				  $i++;
			    }
        }
		$total = $i;
        echo "</ul> 
	  </div>";
	  
	  // If new user hasn't made a predcition yet warn them that they must make one to appear in leagues
	  $newUser = queryMysql("SELECT * FROM predictions WHERE username='$user'");
      if ($newUser->num_rows == 0) {
          echo "<br><br><h4 style='color:red'>New Users</h4><p>Make at least <a href='member.php' style='color:red'>one prediction</a> to appear in tables</p>";
	  }
	  //  Form data from above is processed here and the selected league is shown
	  for($i=0; $i<$total; $i++) {
	      if (isset($_POST["res" . $i])){
		      $result = ${"res" . $i};
			  if($result != 'Overall'){
				  echo "<div><br><h3>${"res" . $i}</h3>";
			  $thisleague = 'SELECT members.username as m, sum(predictions.points) as s from members_leagues
		                     join members on members_leagues.username = members.username 
		                     join predictions on members.username=predictions.username 
				             where members_leagues.league_name="'.$result.'"
				             group by m order by s desc';
		      $league = showLeague($thisleague); echo "</div>";
			  }
		  } 
	  } echo"
	</div>
	<!--End of left row-->
	
	<!--Show Overall League in the middle row-->
    <div class='col-sm-4 text-center'>   
	  <div>
	    <h3>Overall</h3>";
	    $thisleague = 'SELECT members.username as m, sum(predictions.points) as s from members_leagues
		join members on members_leagues.username = members.username 
		join predictions on members.username=predictions.username 
	    where members_leagues.league_name="Overall"
		group by m order by s desc';
		$league = showLeague($thisleague);
echo "</div>
	</div>	
	<!--End of middle row-->
    
	<!--Start of right row,4 main divs: join league, create league, leave league, leave game-->
    <div class='col-sm-4 text-center'>
	  <div class='well well-sm' style='margin-top:10px; border:2px solid #083045; border-radius:5px'>
	    <h2>Join a league</h2>
	    <h4>Enter league name</h4>";
        if (isset($_POST['joinLeague'])){
            $joinleague = sanitizeString($_POST['joinLeague']);
			// $result checks if league exists
            $result = queryMysql("SELECT * FROM local_leagues WHERE league_name='$joinleague'");
			// $result2 checks if user is already a member
            $result2 = queryMysql("SELECT * FROM members_leagues WHERE league_name='$joinleague' and username='$user'");
			
            if ($result->num_rows == 0) {
                echo "<h4 style='color:#EE7600'>League doesn't exist</h4>";
		    }
			elseif ($result2->num_rows){
				echo "<h4 style='color:#EE7600'>Already a member</h4>";
			}
            else {
                queryMysql("INSERT INTO members_leagues VALUES('$user', '$joinleague')");
                echo "
				<script type='text/javascript'>
                 window.location.href = 'leagues.php';
                </script>";
            }
        }
   echo"
	    <form method='post' action='leagues.php' class='form-horizontal'>
        <div class='form-group'>
          <div class='col-xs-8 col-xs-offset-2'>
            <input type='text' class='form-control' name='joinLeague' placeholder='League Name' required>
          </div>
        </div>
	    <div class='form-group'> 
          <div class='col-xs-12'>
            <button type='submit' class='btn btn-primary'>Join League</button>
          </div>
        </div>
        </form>
      </div>
	  
	  <div class='well well-sm' style='margin-top:20px; border:2px solid #083045; border-radius:5px'>
	    <h2>Create a league</h2>
	    <h4>Choose league name</h4>";
		$newleague = "";
        if (isset($_POST['createleague'])){
            $newleague = sanitizeString($_POST['createleague']);
			// $result checks if league exists 
            $result = queryMysql("SELECT * FROM local_leagues WHERE league_name='$newleague'");
            if ($result->num_rows)
                echo "<h4 style='color:#EE7600'>League already exists</h4>";
            else {
                queryMysql("INSERT INTO local_leagues VALUES('$newleague')");
				queryMysql("INSERT INTO members_leagues VALUES('$username', '$newleague')");
                echo "
				<script type='text/javascript'>
                 window.location.href = 'leagues.php';
                </script>";
           }
        }
   echo"<form method='post' action='leagues.php' class='form-horizontal'>
        <div class='form-group'>
          <div class='col-xs-8 col-xs-offset-2'>
            <input type='text' class='form-control' name='createleague' placeholder='League Name' required>
          </div>
        </div>
	    <div class='form-group'> 
          <div class='col-xs-12'>
            <button type='submit' class='btn btn-primary'>Create League</button>
          </div>
        </div>
        </form>
      </div>
	  
	  <div class='well well-sm' style='margin-top:20px; border:2px solid #083045; border-radius:5px'>
	    <h2>Leave a league</h2>
	    <h4>Enter league name</h4>";
        if (isset($_POST['leaveLeague'])){
            $leaveleague = sanitizeString($_POST['leaveLeague']);
			// $result checks if league exists and if user is a member 
            $result = queryMysql("SELECT * FROM members_leagues WHERE username='$username' and league_name='$leaveleague'");
			
            if ($result->num_rows == 0) {
                echo "<h4 style='color:#EE7600'>Doesn't exist/Never joined</h4>";
		    }
            else {
                queryMysql("DELETE FROM members_leagues WHERE username='$username'and league_name='$leaveleague'");
                echo "<script type='text/javascript'>
                 window.location.href = 'leagues.php';
                </script>";
            }
        }
   echo"
	    <form method='post' action='leagues.php' class='form-horizontal'>
        <div class='form-group'>
          <div class='col-xs-8 col-xs-offset-2'>
            <input type='text' class='form-control' name='leaveLeague' placeholder='League Name' required>
          </div>
        </div>
	    <div class='form-group'> 
          <div class='col-xs-12'>
            <button type='submit' class='btn btn-primary'>Leave league</button>
          </div>
        </div>
        </form>
      </div>
	  
	  <div class='well well-sm' style='margin-top:20px; border:2px solid #083045; border-radius:5px'>
	    <h2>Leave the game</h2>
	    <h4>All your records will be deleted</h4>";
        if (isset($_POST['leaveGame'])){
            $password = sanitizeString($_POST['leaveGame']);
			// $result checks if password is correct
            $result = queryMysql("SELECT * FROM members WHERE username='$username' and password='$password'");
            if ($result->num_rows == 0)
                echo "<h4 style='color:#EE7600'>Incorrect Password</h4>";
            else {
                queryMysql("DELETE FROM members WHERE username='$username'and password='$password'");
				session_unset(); 
                session_destroy(); 
                echo "
				<script type='text/javascript'>
                 window.location.href = 'index.php';
                </script>";
           }
        }
   echo"
	    <form method='post' action='leagues.php' class='form-horizontal'>
        <div class='form-group'>
          <div class='col-xs-8 col-xs-offset-2'>
            <input type='password' class='form-control' name='leaveGame' placeholder='Password' required>
          </div>
        </div>
	    <div class='form-group'> 
          <div class='col-xs-12'>
            <button type='submit' class='btn btn-primary'>Goodbye!</button>
          </div>
        </div>
        </form>
      </div>
	</div>
    <!--End of right row-->
	
  </div>
</div>

<div class='container-fluid'>
  <div class='row' style='background-color:#1a1a1a; color:white; padding:0px 70px 0px 70px'>
    <div class='col-sm-12 text-left'>
      <h2><a href='member.php' style='text-decoration:none; color:white'>My Predictions</a>
      <span>&emsp;&ensp;</span>
      <a href='#' style='text-decoration:none; color:white'>Top</a>
      </h2>
    </div> 
  </div>
</div>

</body>
</html>";	
?>