<!DOCTYPE html>
<html>
<head>
<title>Setting up database</title>
</head>
<body>
<h3>Setting up...</h3>

<?php
require_once 'functions.php';

// Table for users who sign up	
createTable('members',
            'username VARCHAR(30) NOT NULL PRIMARY KEY,
             password VARCHAR(30) NOT NULL,
			 email VARCHAR(30) NOT NULL UNIQUE');
	
// Fixtures table for all matches in Emglish Premier League	
createTable('fixtures',
            'matchID int NOT NULL PRIMARY KEY,
			 date DATETIME NOT NULL,
			 home_team VARCHAR(30) NOT NULL,
             away_team VARCHAR(30) NOT NULL,
             home_score int,
			 away_score int,
			 status VARCHAR(30) NOT NULL');
			 
//  Bridge table holds users' predictions
//  References fixtures table and users table			 
createTable('predictions',
            'predID INT NOT NULL AUTO_INCREMENT,
			 username VARCHAR(30) NOT NULL,
			 matchID int NOT NULL,
             home_prediction int,
			 away_prediction int,
			 points int,
             PRIMARY KEY (username, matchID),
             FOREIGN KEY (username) REFERENCES members(username) ON DELETE CASCADE,
			 FOREIGN KEY (matchID) REFERENCES fixtures(matchID) ON DELETE CASCADE,
			 KEY predID (predID)');

//  Table for leagues created by users			 
createTable('local_leagues',
            'league_name VARCHAR(30) NOT NULL PRIMARY KEY');

//  Bridge Table: Link users and a league name 			
createTable('members_leagues',
            'username VARCHAR(30) NOT NULL,
             league_name VARCHAR(30) NOT NULL,
			 PRIMARY KEY (username, league_name),
             FOREIGN KEY (username) REFERENCES members(username) ON DELETE CASCADE,
			 FOREIGN KEY (league_name) REFERENCES local_leagues(league_name) ON DELETE CASCADE');
		
//  Put Overall League into leagues from the start
//  Everyone who signs up will be entered in this league.		
queryMysql('INSERT INTO local_leagues VALUES ("Overall")');
	
//  Trigger to update points table in predictions table
//  after fixtures table is updated after games are played
//  3 points for a correct result and an exact scoreline
//  1 point for a correct result but inexact scoreline
//  0 points for incorrect result/unsubmitted scores 
queryMysql('create or replace trigger update_points 
			after update on fixtures
            for each row 
            begin 

            declare home_value int;
            declare away_value int;	
            declare pred_value int;				
            declare num_rows int default 0; 
            declare done int default false; 
            declare my_cursor cursor for select 
		        home_prediction, away_prediction, predID from predictions where predictions.matchID = NEW.matchID; 
            declare continue handler for not found set done = true; 

            open my_cursor; 
				
            my_loop: loop

                set done = false;

                fetch my_cursor into home_value, away_value, pred_value;
                	
                if done then
                leave my_loop;
                end if;

                IF((NEW.home_score = home_value) AND 
                    (NEW.away_score = away_value) AND NEW.status="FINISHED")				
					then
                    update predictions					
					set predictions.points = 3
					where pred_value = predictions.predID;    
			    ELSEIF 
				    ((NEW.home_score > NEW.away_score) AND
				     (home_value > away_value) AND NEW.status="FINISHED") 
					  then
                      update predictions
                      set predictions.points = 1
                      where pred_value = predictions.predID;
                ELSEIF 
				    ((NEW.home_score < NEW.away_score) AND
				     (home_value < away_value) AND NEW.status="FINISHED") 
					 then
                     update predictions
                     set predictions.points = 1
                     where pred_value = predictions.predID;
				ELSEIF 
				    ((NEW.home_score = NEW.away_score) AND
				     (home_value = away_value) AND NEW.status="FINISHED") 
					  then
                      update predictions
                      set predictions.points = 1
                      where pred_value = predictions.predID;
                ELSE
                    update predictions
                    set predictions.points = 0
                    where pred_value = predictions.predID;
                end if;
				
            end loop my_loop; 
            close my_cursor;
              		
            end;');		 
?>
<br>...done.
</body>
</html>