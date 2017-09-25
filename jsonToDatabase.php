<!DOCTYPE html>
<html>
<head>
<title>Populating database</title>
</head>
<body>
<h3>Populating fixtures...</h3>
<?php
//  Using http://api.football-data.org/ api for football data
//  The first lines requests the file contents of the api
//  and decoded it into workable json format
require_once 'functions.php';

    $url = 'http://api.football-data.org/v1/competitions/426/fixtures';
    $reqPrefs['http']['method'] = 'GET';
    $reqPrefs['http']['header'] = 'X-Auth-Token: c6422f09dd4940e0a2d99bc5f6d52e92';
    $stream_context = stream_context_create($reqPrefs);
    $response = file_get_contents($url, false, $stream_context);
    $data = json_decode($response, true);
	
	//  loop through each fixture to populate the database with match information
	for($i=0; $i<count($data['fixtures']); $i++) {
		$matchID = $i;
        $home = $data['fixtures'][$i]['homeTeamName'];
	    $away = $data['fixtures'][$i]['awayTeamName'];
	    $homeScore = $data['fixtures'][$i]['result']['goalsHomeTeam'];
	    $awayScore = $data['fixtures'][$i]['result']['goalsAwayTeam'];
	    $datetime = date('Y-m-d H-i-s', strtotime($data['fixtures'][$i]['date']) - 60*60);  
	    $status = $data['fixtures'][$i]['status'];
	    queryMysql("INSERT INTO fixtures VALUES('$matchID','$datetime', '$home', '$away',
						'$homeScore', '$awayScore', '$status') 
						ON DUPLICATE KEY UPDATE date='$datetime', home_score='$homeScore',
						away_score='$awayScore', status='$status'");	
    }
?>
<br>...done.
</body>
</html>