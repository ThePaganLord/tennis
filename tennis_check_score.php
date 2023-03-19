<?php
error_reporting(E_ALL);


include("connection.php");
// Get the player from the user input
$update_player=$_POST['player'];


//****** START FUNCTIONS HERE ******//
function GetPlayerScores(){
	$player_scores=array();
	try	{
		$database = new Connection();
		$connection_tb = $database->openConnection();
		$get_player_scores=$connection_tb->prepare("SELECT player_score.player_desc, player_score.player_score_value,
													score_description.score_desc
													FROM player_score
													JOIN score_description ON (player_score.player_score_value=score_description.score_value)
													ORDER BY player_score.player_desc");
		$get_player_scores->execute();
		
		foreach( new RecursiveArrayIterator($get_player_scores->fetchAll()) as $key=>$value){
			$player_desc=$value["player_desc"];
			$player_score_value=$value["player_score_value"];
			$player_score_desc=$value["score_desc"];
			
			if(array_key_exists($player_desc,$player_scores)){
				$player_scores[$player_desc]=array($player_score_value,$player_score_desc);
			}
			else{
				$player_scores += array($player_desc => array($player_score_value, $player_score_desc));
			}
		}
		return $player_scores;
	}
	catch (PDOException $e)
	{
		echo "There is some problem in connection: " . $e->getMessage();
	}
}

function UpdatePlayerScore($player, $new_score){
	try	{
		$database = new Connection();
		$connection_tb = $database->openConnection();
		$update_player_score=$connection_tb->prepare("UPDATE player_score SET player_score_value='$new_score'
													  WHERE player_desc = '$player'");
		$update_player_score->execute();
	}
	catch (PDOException $e)
	{
		echo "There is some problem in connection: " . $e->getMessage();
	}
	
}
function CheckDeuce($update_player, $new_score){
	$deuce_score=array();
	echo "hello </br>";
	try	{
		$database = new Connection();
		$connection_tb = $database->openConnection();
		// This query will only find the records of the OTHER player IF their score is also 3 which is Deuce
		$check_deuce=$connection_tb->prepare("SELECT player_score.player_desc, player_score.player_score_value,
											  score_description.score_desc
											  FROM player_score 
											  JOIN score_description ON (player_score.player_score_value=score_description.score_value)
											  WHERE player_desc != '$update_player'");
		$check_deuce->execute();
		foreach( new RecursiveArrayIterator($check_deuce->fetchAll()) as $key=>$value){
			$player_desc=$value["player_desc"];
			$player_score_value=$value["player_score_value"];
			$player_score_desc=$value["score_desc"];
		}
		if($player_score_value == 3){
			// Update the array values.
			$player_score_desc='Deuce';
			if(array_key_exists($update_player, $deuce_score)){
				$deuce_score[$update_player]=array($new_score,$player_score_desc);
			}
			else{
				$deuce_score += array($update_player => array($new_score, $player_score_desc));
			}
			// Change the other player
			if(array_key_exists($player_desc,$deuce_score)){
				$deuce_score[$player_desc]=array($player_score_value,$player_score_desc);
			}
			else{
				$deuce_score += array($player_desc => array($player_score_value, $player_score_desc));
			}
		}
		elseif($player_score_value < 3){
			$deuce_score=GetPlayerScores();
		}
		
		return $deuce_score;
			
	}
	catch (PDOException $e)
	{
		echo "There is some problem in connection: " . $e->getMessage();
	}
	
	// Return both players scores
	
}

// Check for Advantage
function CheckAdvantage($update_player, $new_score){
	$advantage_score=array();
	$check_for_advantage=$new_score - 1;
	try	{
		$database = new Connection();
		$connection_tb = $database->openConnection();
		// This query will only find the records of the OTHER player IF their score is also 3 or 4.
		// If the other player has 3 then update_player will receive advantage.
		// If the other player has 4 then both players get set back to 3
		$check_advantage=$connection_tb->prepare("SELECT * FROM player_score WHERE player_desc != '$update_player'
											  AND player_score_value >= '$check_for_advantage'");
		$check_advantage->execute();
		foreach( new RecursiveArrayIterator($check_advantage->fetchAll()) as $key=>$value){
			$player_desc=$value["player_desc"];
			$player_score_value=$value["player_score_value"];
		}
		if(isset($player_score_value)){
			// Update the array values.
			// If the OTHER player is on 3, the the $update_player has Advantage
			if($player_score_value == 3){
				$advantage_desc='Advantage';
				// Change the scoring player
				if(array_key_exists($update_player, $advantage_score)){
					$advantage_score[$update_player]=array($new_score,$advantage_desc);
				}
				else{
					$advantage_score += array($update_player => array($new_score, $advantage_desc));
				}
				// Change the other player
				$advantage_desc='';
				if(array_key_exists($player_desc,$advantage_score)){
					$advantage_score[$player_desc]=array($player_score_value,$advantage_desc);
				}
				else{
					$advantage_score += array($player_desc => array($player_score_value, $advantage_desc));
				}
			}
			if($player_score_value == 4){
				$reset_deuce_score=3;
				$reset_deuce=$connection_tb->prepare("UPDATE player_score SET player_score_value='$reset_deuce_score'
													  WHERE player_score_value = '$player_score_value'");
				$reset_deuce->execute();
				$advantage_score=CheckDeuce($update_player, $reset_deuce_score);
			}
		}
		else{
			// Since there is no score of 4 unless there is advantage, this means that the first one to 4 is actually the winner...
		}
		return $advantage_score;
			
	}
	catch (PDOException $e)
	{
		echo "There is some problem in connection: " . $e->getMessage();
	}
	
	// Return both players scores
	
}

function CheckWinner($update_player, $new_score){
	// This will only come in here if the $update_player gets to 5.
	$winner_score=array();
	// Check the other players score just in case of a mess
	
	try	{
		$database = new Connection();
		$connection_tb = $database->openConnection();
		$check_winner=$connection_tb->prepare("SELECT * FROM player_score WHERE player_desc != '$update_player'");
		$check_winner->execute();
		foreach( new RecursiveArrayIterator($check_winner->fetchAll()) as $key=>$value){
			$player_desc=$value["player_desc"];
			$player_score_value=$value["player_score_value"];
		}
		
		$reset_players_zero=$connection_tb->prepare("UPDATE player_score SET player_score_value = '0'
													  WHERE player_score_value > 0");
		$reset_players_zero->execute();
		
		// Update the winner which is the player that just scored
		$winner_desc="WINNER";
		if(array_key_exists($update_player, $winner_score)){
			$winner_score[$update_player]=array($new_score,$winner_desc);
		}
		else{
			$winner_score += array($update_player => array($new_score, $winner_desc));
		}
		// Update the losing player which is the other player
		$loser_desc="";
		if(array_key_exists($player_desc, $winner_score)){
			$winner_score[$player_desc]=array($player_score_value,$loser_desc);
		}
		else{
			$winner_score += array($player_desc => array($player_score_value, $loser_desc));
		}
	}
	catch (PDOException $e)
	{
		echo "There is some problem in connection: " . $e->getMessage();
	}
	return $winner_score;
}

//****** END FUNCTIONS HERE ******//
$current_player_scores=GetPlayerScores();
// Get the other player's score
$current_score_key = array_keys($current_player_scores);
for($i=0; $i < count($current_score_key); ++$i) {
	$player_desc=$current_score_key[$i];
	if($player_desc != $update_player){
		$other_score=$current_player_scores[$player_desc][0];
	}
}
// new_score is for the player in $update_player
$new_score=($current_player_scores[$update_player][0]) +1;

if($other_score < 3 && $new_score == 4){
	// Add another "point" if the scoring player is on 4 after the new score and the other player is on less than 3
	$new_score++;
}
// Update the player who scored
UpdatePlayerScore($update_player, $new_score);
$new_player_scores=GetPlayerScores();
// Check if the other player is at 3 or more
if($other_score >= 3){
	if($new_score == 3){
		// Check Deuce
		$return_scores=CheckDeuce($update_player, $new_score);
	}
	elseif($new_score == 4){
		// Check Advantage
		$return_scores=CheckAdvantage($update_player, $new_score);	
	}
	else{
		$return_scores=$new_player_scores;
	}
}
else{
	// If the other player is at less than 3 but the scoring player is already 3, then 
	$return_scores=$new_player_scores;
}

if($new_score == 5){
	// Check Winner
	$return_scores=CheckWinner($update_player, $new_score);
}


$size_return_scores=sizeof($return_scores);
$player_key = array_keys($return_scores);
for($i=0; $i < count($player_key); ++$i) {
	$player_desc=$player_key[$i];
	$player_score_value=$return_scores[$player_desc][0];
	$player_score_desc=$return_scores[$player_desc][1];

	if($player_desc == "Player 1"){
		$player1_score=$player_score_value;
		$player1_score_desc=$player_score_desc;
	}
	elseif($player_desc == "Player 2"){
		$player2_score=$player_score_value;		
		$player2_score_desc=$player_score_desc;	
	}
}
// Return to the User Input...
?>
<script language="JavaScript">
	window.location.href="index.php?player1_score=<?php echo $player1_score_desc ?>&player2_score=<?php echo $player2_score_desc ?>";
</script>