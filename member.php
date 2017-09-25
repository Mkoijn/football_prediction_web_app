<?php
require_once 'header.php';

// Make sure user is logged in. If not, redirect to index.php
if (isset($_SESSION['user'])){
    $username = $_SESSION['user'];	
    $loggedin = TRUE;	
}
else $loggedin = FALSE;

if (!$loggedin){
	header("Location: index.php");
    exit();
}

echo '
<div class="container-fluid text-center">	  
  <div class="row" style="padding-bottom:30px; padding-top:20px">	
    
	<!--Show link for leagues page + overall top 10 for the game on the left-->
    <div class="col-sm-2"> 
	  <div style="margin-top:40px;">  
	    <button id="goLeagues" class="btn btn-primary btn-lg btn-block" onclick="window.location.href=&quot;leagues.php&quot;">My Leagues</button>
      </div>
      <h3>Overall Top 10</h3>
	  <div>';
	    $query = "SELECT members.username as m, sum(predictions.points) as s from members_leagues
		          join members on members_leagues.username = members.username 
		          join predictions on members.username=predictions.username 
				  where members_leagues.league_name='Overall'
				  group by m order by s desc limit 10";
        $league = showLeague($query);
echo '</div>
    </div>	
    <!--End left row-->
	
	<!--Middle row - make predictions-->
	<div class="col-sm-6 text-center well well-sm" style="margin-top:20px; padding-top:5px; 
	            padding-bottom:0px; border:2px solid #083045; border-radius:5px">';
	  
      //  Using http://api.football-data.org/ api for football data
      //  The first lines requests the file contents of the api
      //  and decoded it into workable json format	  
	  $url = 'http://api.football-data.org/v1/competitions/426/fixtures';
      $reqPrefs['http']['method'] = 'GET';
      $reqPrefs['http']['header'] = 'X-Auth-Token: c6422f09dd4940e0a2d99bc5f6d52e92';
      $stream_context = stream_context_create($reqPrefs);
      $response = file_get_contents($url, false, $stream_context);
      $data = json_decode($response, true);	
      if(empty($data)){
            die("Matches are being updated, try again soon.");
      };	  
		  
	  // get next matchday number...break when a fixture found
	  // not finished and in_play
      for($i=0; $i<379; $i++) {
	      ${"status" . $i} = $data['fixtures'][$i]['status'];
		  if(${"status" . $i} != "FINISHED" and ${"status" . $i} != "IN_PLAY"){
		      $matchday = $data['fixtures'][$i]['matchday'];
			  break;
		  }
	  }
echo "
      <h3>Week ".$matchday.":&nbsp;&nbsp;&nbsp;Predict + SAVE</h3>
	  <div class='table-responsive'>
	    <form method='post' action='member.php'>
		  <table class='table table-hover table-condensed text-left'>";
		  
		  // loop through all fixtures from api
		  // extract the data, put into variables and display
		  // Users can input predictions for each of next week's fixtures
	      for($i=0; $i<count($data['fixtures']); $i++) {
			  
		    ${"matchID" . $i} = $i;
    	    ${"home" . $i} = $data['fixtures'][$i]['homeTeamName'];
	        ${"away" . $i} = $data['fixtures'][$i]['awayTeamName'];
	        ${"homeScore" . $i} = $data['fixtures'][$i]['result']['goalsHomeTeam'];
	        ${"awayScore" . $i} = $data['fixtures'][$i]['result']['goalsAwayTeam'];
	        ${"date" . $i} = date('d-M H:i', strtotime($data['fixtures'][$i]['date']) - 60*60);  
	        ${"status" . $i} = $data['fixtures'][$i]['status'];
			${"resH" . $i} = "";  // will hold user home prediction
			${"resA" . $i} = "";  // will hold user away prediction
			${"week" . $i} = $data['fixtures'][$i]['matchday'];
			
			// If user's have already made a prediction the prediction
			// should be known to be displayed (later).
			// The following if/else conditions holds the 
			// prediction variable (either from $_POST or database). 
			if(isset($_POST["homP" . $i])){
		        ${"resH" . $i} = $_POST["homP" . $i];
			}else{
			   $sqlHome = ("select home_prediction from predictions  
							where predictions.matchID = $i and predictions.username = '$username'");
			   $submittedHome = queryMysql($sqlHome);
			   while ($row = $submittedHome->fetch_assoc()) {
                   ${"resH" . $i} = $row['home_prediction'];
			   }
			}
			
			if(isset($_POST["awaP" . $i])){
		        ${"resA" . $i} = $_POST["awaP" . $i];
			}else{
			    $sqlAway = ("select away_prediction from predictions  
							 where predictions.matchID = $i and predictions.username = '$username'");
			    $submittedAway = queryMysql($sqlAway);
			    while ($row = $submittedAway->fetch_assoc()) {
                    ${"resA" . $i} = $row['away_prediction'];
			    }
			}
			//  Create input forms for each fixture for the upcoming week.
		    if(${"status" . $i} != "FINISHED" and ${"status" . $i} != "IN_PLAY" and ${"week" . $i} === $matchday){  
	            echo '<tr><td>' . ${"date" . $i} . '</td><td>' . ${"home" . $i} . '</td>' . 
                     '<td><input style="width: 40px;" type="number" min="0" max="15" id="homeP" name="homP' . $i . '" value="'.${"resH" . $i}.'"></td> 
	                  <td><input style="width: 40px;" type="number" min="0" max="15" id="awayP" name="awaP' . $i . '" value="'.${"resA" . $i}.'"></td>
                      <td>' . ${"away" . $i} . '</td></tr>';
		    }
		  }
		  // end of for loop
		  
	echo '<tr>
		  <td><input type="submit" class="btn btn-primary btn-md btn-block" value="Save"></td>
		  <td colspan="4"><h4>You can resave changes until close to kick-off time</h4></td></tr>
		  </table>
		</form>';
		
		// The code for dealing with submitted predictions from the above form.
		// Populate the database with users' predictions.
		for($i=0; $i<379; $i++) {
	        if (isset($_POST["homP" . $i])){
			    $homePred = $_POST["homP" . $i];
                $awayPred = $_POST["awaP" . $i];
			    $points = null;
			// No input -> continue, no submission
	        if(!is_numeric($homePred) and !is_numeric($awayPred)){
		        continue;
			}
			// Home score inputted, but no away score -> give away team 0 as score
			if(is_numeric($homePred) and !is_numeric($awayPred)){
			    $awayPred = 0;
			    echo "
				  <script type='text/javascript'>
                   window.location.href = 'member.php';
				   alert('Predictions Saved!')
                  </script>";
			}
			// Away score inputted, but no home score -> give home team 0 as score
			if(!is_numeric($homePred) and is_numeric($awayPred)){
		        $homePred = 0;
				echo "
				  <script type='text/javascript'>
                   window.location.href = 'member.php';
				   alert('Predictions Saved!')
                  </script>";
			}
			
            queryMysql("INSERT INTO predictions VALUES('','$username','${"matchID" . $i}',
					   '$homePred','$awayPred','$points') ON DUPLICATE KEY UPDATE home_prediction='$homePred',
				        away_prediction='$awayPred'");					   
            }
          }
		  // Alert customers that their predictions have been saved
          if( $_POST  ) {
		      echo "<script type='text/javascript'>alert('Predictions Saved!')</script>";
		  }							
echo '</div> 
    </div>
	<!--End of middle row-->
	   
	<!--Right row - show live games and results from this week and last gameweek-->
    <div class="col-sm-4 text-center">
      <div style="padding-top:20px;">	
	    <h3>Live Scores and Results</h3>
      </div>
	  
      <div class="table-responsive">
	  <table class="table text-left table-hover table-condensed" id="resultsTable">
		            <tr><th>Week</th><th>Home</th>
		            <th colspan="2">Score</th><th>Away</th><th colspan="2">You</th></tr>';
		// Show live scores and recent result in table with users' predictions.
	    for($i=379; $i>-1; $i--) {
		    if(${"status" . $i} == "IN_PLAY"){
		      echo '<tr style="background-color:#EED369"><td>Live</td><td>' . ${"home" . $i} . '</td><td>' 
			       . ${"homeScore" . $i} 
				   . '</td><td>' . ${"awayScore" . $i} . '</td><td>' . ${"away" . $i}  
				   . '</td><td>' . ${"resH" . $i} . '</td><td>' . ${"resA" . $i} . '</td></tr>';
		    }
		    else if(${"status" . $i} == "FINISHED" and (${"week" . $i} === $matchday or ${"week" . $i} === $matchday - 1)){
		      echo '<tr><td>' . ${"week" . $i} . '</td><td>' . ${"home" . $i} . '</td><td>' 
			       . ${"homeScore" . $i} 
				   . '</td><td>' . ${"awayScore" . $i} . '</td><td>' . ${"away" . $i}  
				   . '</td><td>' . ${"resH" . $i} . '</td><td>' . ${"resA" . $i} . '</td></tr>';
		    }
		}
		
  echo '</table>	
      </div> 
    </div> 
	<!--End of right row-->
	
  </div>
</div>

<div class="container-fluid">
  <div class="row" style="background-color:#1a1a1a; color:white; padding:0px 70px 0px 70px">
    <div class="col-sm-12 text-left">
      <h2><a href="leagues.php" style="text-decoration:none; color:white">My Leagues</a>
      <span>&emsp;&ensp;</span>
      <a href="#" style="text-decoration:none; color:white">Top</a>
      </h2>
    </div> 
  </div>
</div>

</body>
</html>';
?>