<?php
$show_player1_score='';
$show_player2_score='';
if(isset($_REQUEST['player1_score']) && isset($_REQUEST['player2_score'])){
	$show_player1_score=$_REQUEST['player1_score'];
	$show_player2_score=$_REQUEST['player2_score'];
	$match_update=NULL;
	if($show_player1_score == 'Deuce'){
		$match_update='Deuce';
	}
	elseif($show_player1_score == $show_player2_score){
		$match_update = $show_player1_score.' All';
	}
}	
else{
	$match_update='Start Match';
	$show_player1_score=NULL;
	$show_player2_score=NULL;
}

if($show_player1_score == 'WINNER' || $show_player2_score == 'WINNER'){
	$play_new='Yes';
}
else{
	$play_new=NULL;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Tennis Match</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="site.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
</head>
<body>
	<div class="container">
	<div class="row">
		<div class="col"></div>
		<div class="col-6">
			<form action="tennis_check_score.php" method="post">
			<table align="center">
				<?php
				if(empty($match_update)){ ?>
					<tr>
						<th>Player 1</th>
						<th>Player 2</th>
					</tr>
					<tr>
						<td><?php echo $show_player1_score ?></td>
						<td><?php echo $show_player2_score ?></td>
					</tr>
				<?php
				}
				else{ ?>
					<tr>
						<th colspan=2> &nbsp </th>
					</tr>
					<tr>
						<td colspan=2 align="center"><b><?php echo $match_update ?></b></td>
					</tr>
				<?php
				} 
				if(empty($play_new)){ ?>
					<tr>
						<td><input type="submit" name='player' value="Player 1" class="button"></td>
						<td><input type="submit" name='player' value="Player 2" class="button"></td>
					</tr>
				<?php
				}
				else{ ?>
					<tr>
						<td colspan=2 align="center"><a href="tennis_2.php" class="button">Play Again</a></td>
					</tr>				
				<?php
				} ?>
			</table>
		</div>
	</div>

</body>
</html>