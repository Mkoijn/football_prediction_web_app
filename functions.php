<?php
// Modify these variables according to your xampp settings
$servername = "localhost";
$username = "root";
$dbname = "footballDB";
$password = "";


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
	
// Create a table if it doesn't exist already	
function createTable($name, $query)
    {
        queryMysql("CREATE TABLE IF NOT EXISTS $name($query)");
        echo "Table '$name' created or already exists.<br>";
    }

// Issue a query to MySQL	
function queryMysql($query)
    {
        global $conn;
        $result = $conn->query($query);
        if (!$result) die($conn->error);
        return $result;
}

// Destroy a PHP session and clear its data to log users out
function destroySession()
    {
        $_SESSION=array();
        if (session_id() != "" || isset($_COOKIE[session_name()]))
        setcookie(session_name(), '', time()-42000, '/');
        session_destroy();
    }

// Remove malicious code or tags from user input	
function sanitizeString($var)
    {
        global $conn;
        $var = strip_tags($var);
        $var = htmlentities($var);
        $var = stripslashes($var);
        return $conn->real_escape_string($var);
    }

// Echo a league table from a query in the game - used in a few places
function showLeague($query)
    {
        $result = queryMysql($query);
		$total = count((array)$result);
		 
		if ($result->num_rows == 0){
            return;
        } 
		
		if($total){
           echo '<table class="table table-hover text-left"><tr><th>#</th><th>Name</th><th>Pts</th></tr>';
		   while($row = $result->fetch_array()){
              $rows[] = $row;
           }
           $i = 1;		
           foreach($rows as $row){
              echo '<tr><td>' . $i . '.</td><td>' . $row['m'] . '</td><td>' . $row['s'] . '</td></tr>';
		      $i++;
           }
           echo '</table>';
		}
    }
?>