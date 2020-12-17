<?php

$servername = "localhost";
$username = "id15266567_heldtraymond3";
$password = "Nnycdttrima8!";
$dbname = "id15266567_heldtraymond";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$seatingChart = array(3,5,4,2,9,1,8,7,12,0,10,11,13,6);

if (isset($_POST['a'])) {
	$gameAction = $_POST['a'];

	if ($gameAction >= 122400000 && $gameAction < 122416384) {
		// New game
		$playersInt = ($gameAction % 100000);
		$mult = 1;
		$currVal = 0;
		$livePlayers = array();

		for ($x = 0; $x < 14; $x++) {
			$p = 13 - $x;
			$playersInt = $playersInt - $currVal;
			$prevMult = $mult;
			$mult = $mult * 2;

			$currVal = $playersInt % $mult;
			if ($currVal === $prevMult) {
				array_push($livePlayers, $p);
			}
		}

		$startingPlayer = $livePlayers[rand(0, count($livePlayers) - 1)];
		$insertSeatsSql = "INSERT INTO Seat (GameId, SeatId, PlayerId) VALUES ";
		$firstOne = true;
		$startingSeat = null;
		$startingUpCardIndex = 6 * count($livePlayers);

		for ($seat = 0; $seat < 14; $seat++) {
			$player = $seatingChart[$seat];
			if (in_array($player, $livePlayers)) {
				if (!$firstOne) { $insertSeatsSql = $insertSeatsSql . ","; }
				$firstOne = false;

				$insertSeatsSql = $insertSeatsSql . "(@GameId, $seat, $player)";
				if ($startingPlayer == $player) {
					$startingSeat = $seat;
				}
			}
		}

		$newGameSql = "INSERT INTO GameStatus(Id, RoundNumber, RoundStartTimeUtc, CurrentSeat, EndingSeat, UpCardIndex) VALUES (1 + SELECT MAX(Id) FROM GameStatus, 0, SELECT DATEADD(minute, 2, GETUTCDATE()), $startingSeat, NULL, $startingUpCardIndex);DECLARE @GameId int = (SELECT MAX(Id) FROM GameStatus)";

		$insertCardsSql = "INSERT INTO Card(GameId, Round, Index, Value) VALUES ";
		$firstOne = true;
		for ($round = 1; $round <= 9; $round++) {
			$deck = array();
			for ($card = -1; $card >= -108; $card--) {
				array_push($deck, $card);
			}

			for ($deckIndex = 0; $deckIndex < 108; $deckIndex++) {
				$randomInt = rand($deckIndex, 107);
				$temp = $deck[$deckIndex];
				$cardVal = $deck[$randomInt];

				if ($deckIndex == $startingUpCardIndex) {
					$cardVal = abs($cardVal);
				}

				$deck[$deckIndex] = $cardVal;
				$deck[$randomInt] = $temp;

				if (!$firstOne) { $insertCardsSql = $insertCardsSql . ","; }
				$firstOne = false;
				$insertCardsSql = $insertCardsSql . "(@GameId, $round, $deckIndex, $cardVal)";
			}
		}

		$allSql = $newGameSql . ";" . $insertSeatsSql . ";" . $insertCardsSql;
		$result = $conn->query($allSql);
	}
	else {
		$action = ($gameAction % 100);
		$player = (($gameAction - $action) / 100);
		$isDrawTwo = false;
		$isPlayersTurn = false;

		$gameStatusSql = "SELECT TOP 1 g.*, s.PlayerId AS PlayerSeat, DATEDIFF(GETUTCDATE(), RoundStartTimeUtc) AS TimeDiff,
							(SELECT COUNT(*) FROM Seat WHERE GameId = g.Id) AS TotalPlayers,
							(SELECT COUNT(*) FROM Seat WHERE GameId = g.Id AND SeatNumber < s.SeatNumber) AS NumPrevPlayers,
							(SELECT MIN(SeatNumber) FROM Seat WHERE GameId = g.Id AND (SeatNumber > s.SeatNumber OR s.SeatNumber = (SELECT MAX(SeatNumber) FROM Seat WHERE GameId = g.Id))) AS NextPlayerSeat,
							(SELECT MIN(SeatNumber) FROM Seat WHERE GameId = g.Id AND (SeatNumber > g.RoundStartSeat OR g.RoundStartSeat = (SELECT MAX(SeatNumber) FROM Seat WHERE GameId = g.Id))) AS NextRoundStartSeat
						  FROM GameStatus g
						  LEFT JOIN Seat s on s.GameId = g.Id AND s.PlayerId = $player
						  WHERE g.Id = (SELECT MAX(Id) FROM GameStatus)";
		$result = $conn->query($gameStatusSql);
		$gameStatus = null;
		$gameId = null;
		$round = null;
		$roundStart = null;
		$timeDiff = null;
		$playerSeat = null;
		$nextPlayerSeat = null;
		$currentSeat = null;
		$endingSeat = null;
		$nextRoundStartSeat = null;
		$totalPlayers = null;
		$numPrevPlayers = null;

		if ($result->num_rows > 0) {
			$gameStatus = $result->fetch_assoc();
			$gameId = $gameStatus["Id"];
			$round = $gameStatus["RoundNumber"];
			$roundStart = $gameStatus["RoundStartTimeUtc"];
			$timeDiff = $gameStatus["TimeDiff"];
			$playerSeat = $gameStatus["PlayerSeat"];
			$nextPlayerSeat = $gameStatus["NextPlayerSeat"];
			$currentSeat = $gameStatus["CurrentSeat"];
			$endingSeat = $gameStatus["EndingSeat"];
			$nextRoundStartSeat = $gameStatus["NextRoundStartSeat"];
			$upCardIndex = $gameStatus["UpCardIndex"];
			$totalPlayers = $gameStatus["TotalPlayers"];
			$numPrevPlayers = $gameStatus["NumPrevPlayers"];
			$totalPlayerCards = $totalPlayers * 6;

			if ($round > 0 && !is_null($roundStart) && $timeDiff >= 0 && $timeDiff <= 70) {
				$isDrawTwo = true;
			}
			if ($round > 0 && (is_null($roundStart) || $timeDiff < 0) && $currentSeat == $playerSeat) {
				$isPlayersTurn = true;
			}
		}

		if ($isPlayersTurn || ($action >= 0 && $action <= 5 && $isDrawTwo)) {
			$updateSql = null;

			$cardIndices = array();
			$playerDeckIndexStart = 6 * $numPrevPlayers;
			for ($i = 0; $i < 6; $i++) {
				array_push($cardIndices, ($playerDeckIndexStart + $i));
			}
			array_push($cardIndices, $upCardIndex);
			array_push($cardIndices, ($upCardIndex + 1));
			$cardIndicesText = implode(",", $cardIndices);

			$cardsSql = "SELECT DeckIndex, Value FROM Card WHERE GameId = $gameId AND RoundNumber = $round
						 AND DeckIndex IN ($cardIndicesText)
						 ORDER BY DeckIndex ASC";
			$cardsResult = $conn->query($cardsSql);
			$cards = array();
			$cardVals = array();
			for ($i = 0; $i < 6; $i++) {
				$nextCard = $cardsResult->fetch_assoc();
				array_push($cards, $nextCard);
				array_push($cardVals, $nextCard["Value"]);
			}
			$upCard = $cardsResult->fetch_assoc();
			$drawCard = $cardsResult->fetch_assoc();

			if ($action >= 0 && $action <= 5) {
				// Flip over card
				$flipping = true;

				$card = $cards[$action];
				if ($card["Value"] > 0) {
					$flipping = false;
				}
				else if ($isDrawTwo) {
					$faceUpCount = 0;
					for ($c = 0; $c < 6; $c++) {
						if ($cards[$c] < 0) {
							$faceUpCount++;
						}
					}

					if ($faceUpCount >= 2) {
						$flipping = false;
					}
				}

				if ($flipping) {
					$newVal = abs($card["Value"]);
					$deckIndex = $card["DeckIndex"];
					$updateSql = "UPDATE Card SET Value = $newVal WHERE GameId = $gameId AND RoundNumber = $round AND DeckIndex = $deckIndex;";
					$cardVals[$action] = $newVal;
				}
			}
			else if ($action == 6) {
				// Flip draw card
				if ($drawCard["Value"] < 0) {
					$newVal = abs($drawCard["Value"]);					
					$deckIndex = $drawCard["DeckIndex"];
					$updateSql = "UPDATE Card SET Value = $newVal WHERE GameId = $gameId AND RoundNumber = $round AND DeckIndex = $deckIndex;";
				}
			}
			else if ($action == 7) {
				// Discard
				if ($drawCard["Value"] > 0) {
					$newUpCardIndex = $upCardIndex + 1;
					if ($newUpCardIndex < 107) {
						$updateSql = "UPDATE GameStatus SET UpCardIndex = $newUpCardIndex WHERE GameId = $gameId;";
					}
					else {
						// Reshuffle since there's no more draw cards
						$newUpCardIndex = $totalPlayers * 6;
						$drawVal = $drawCard["Value"];
						$updateSql = "UPDATE GameStatus SET UpCardIndex = $newUpCardIndex WHERE GameId = $gameId;
									  UPDATE Card SET Value = $drawVal WHERE GameId = $gameId AND RoundNumber = $round AND DeckIndex = $newUpCardIndex;";
						// Fetch the remaining cards and reshuffle them
						$discardsSql = "SELECT Value FROM Card WHERE GameId = $gameId AND RoundNumber = $round
									 AND DeckIndex >= $newUpCardIndex AND DeckIndex < 107";
						$discardsResult = $conn->query($discardsSql);
						$discards = array();
						while($row = $discardsResult->fetch_assoc()) {
							array_push($discards, $row["Value"]);
						}

						for ($discardIndex = 0; $discardIndex < count($discards); $discardIndex++) {
							$randomInt = rand($discardIndex, 107);
							$temp = $discards[$discardIndex];
							$cardVal = -abs($discards[$randomInt]);

							$discards[$discardIndex] = $cardVal;
							$discards[$randomInt] = $temp;
							$newIndex = $newUpCardIndex + $discardIndex + 1;

							$updateSql = $updateSql . "UPDATE CARD SET Value = $cardVal WHERE GameId = $gameId AND RoundNumber = $round AND DeckIndex = $newIndex;";
						}
					}
				}
			}
			else if ($action >= 10 && $action <= 15) {
				// Swap with either discard or draw card
				$card = $cards[$action - 10];
				$cardVal = abs($card["Value"]);
				$deckIndex = $card["DeckIndex"];

				if ($drawCard["Value"] < 0) {
					// Draw card wasn't flipped - swap with discard, no need to increment up-card index
					$swapIndex = $upCardIndex;
					$swapVal = $upCard["Value"];
					$updateSql = "UPDATE CARD SET Value = $swapVal WHERE GameId = $gameId AND RoundNumber = $round AND DeckIndex = $deckIndex;
								  UPDATE CARD SET Value = $cardVal WHERE GameId = $gameId AND RoundNumber = $round AND DeckIndex = $swapIndex;";
					$cardVals[$action - 10] = $swapVal;
				}
				else {
					// Draw card was flipped - swap with draw card
					$swapIndex = $upCardIndex + 1;
					$swapVal = $drawCard["Value"];
					$updateSql = "UPDATE CARD SET Value = $swapVal WHERE GameId = $gameId AND RoundNumber = $round AND DeckIndex = $deckIndex;";
					$cardVals[$action - 10] = $swapVal;

					$newUpCardIndex = $upCardIndex + 1;
					if ($newUpCardIndex < 107) {
						$updateSql = "UPDATE CARD SET Value = $cardVal WHERE GameId = $gameId AND RoundNumber = $round AND DeckIndex = $swapIndex;
									  UPDATE GameStatus SET UpCardIndex = $newUpCardIndex WHERE GameId = $gameId;";
					}
					else {
						// Reshuffle since there's no more draw cards
						$newUpCardIndex = $totalPlayers * 6;
						$newUpCardVal = $cardVal;
						$updateSql = "UPDATE GameStatus SET UpCardIndex = $newUpCardIndex WHERE GameId = $gameId;
									  UPDATE Card SET Value = $cardVal WHERE GameId = $gameId AND RoundNumber = $round AND DeckIndex = $newUpCardIndex;";
						// Fetch the remaining cards and reshuffle them
						$discardsSql = "SELECT Value FROM Card WHERE GameId = $gameId AND RoundNumber = $round
									 AND DeckIndex >= $newUpCardIndex AND DeckIndex < 107";
						$discardsResult = $conn->query($discardsSql);
						$discards = array();
						while($row = $discardsResult->fetch_assoc()) {
							array_push($discards, $row["Value"]);
						}

						for ($discardIndex = 0; $discardIndex < count($discards); $discardIndex++) {
							$randomInt = rand($discardIndex, 107);
							$temp = $discards[$discardIndex];
							$cardVal = -abs($discards[$randomInt]);

							$discards[$discardIndex] = $cardVal;
							$discards[$randomInt] = $temp;
							$newIndex = $newUpCardIndex + $discardIndex + 1;

							$updateSql = $updateSql . "UPDATE CARD SET Value = $cardVal WHERE GameId = $gameId AND RoundNumber = $round AND DeckIndex = $newIndex;";
						}
					}
				}
			}

			if (!$isDrawTwo && $action != 6) {
				// End of player's turn

				$hasDownCard = false;
				for ($i = 0; $i < 6; $i++) {
					if ($cardVals[$i] < 0) {
						$hasDownCard = true;
					}
				}

				if ($hasDownCard) {
					// Normal play - move to next player
					$updateSql = $updateSql . "UPDATE GameStatus SET CurrentSeat = $nextPlayerSeat WHERE GameId = $gameId;";
				}
				else {
					// Player has all cards up - add round's score
					$cardValues = array();
					for ($i = 0; $i < 6; $i++) {
						$val = $cardVals[$i];
						if ($val > 54) {
							$val = $val - 54; // compensate for deck
						}
						if ($val > 52) {
							$val = -3;
						}
						else {
							$val = ($val % 13); // turns kings into zero's
						}

						// Don't convert queens and jacks yet because queens can't cancel with 10's
						array_push($cardValues, $val);
					}

					// $cardValues now holds the point value of every card, ignoring cancellations, and exceptions that J = 11 and Q = 12
					$roundScore = 0;
					for ($i = 0; $i < 6; $i++) {
						$points = $cardValues[$i];

						if ($cardValues[$i] === $cardValues[($i + 3) % 6]) {
							$points = 0;
						}
						else if ($cardValues[$i] === 11) {
							$points = 20;
						}
						else if ($cardValues[$i] === 12) {
							$points = 10;
						}

						$roundScore = $roundScore + $points;
					}
					$updateSql = $updateSql . "INSERT INTO Score(GameId, PlayerId, RoundNumber, RoundScore) VALUES ($gameId, $playerId, $round, $roundScore);";

					// Check for end of round
					if (is_null($endingSeat)) {
						// First player to go out in this round - set EndingSeat and flip over everyone else's cards
						$updateSql = $updateSql . "UPDATE GameStatus SET CurrentSeat = $nextPlayerSeat, EndingSeat = $playerSeat WHERE GameId = $gameId;
												   UPDATE Card SET Value = abs(Value) WHERE GameId = $gameId AND RoundNumber = $round AND DeckIndex < $totalPlayerCards;";
					}
					else if ($endingSeat == $nextPlayerSeat) {
						// End of round
						if ($round < 9) {
							$round++;
							$updateSql = $updateSql . "UPDATE GameStatus SET RoundNumber = $round, RoundStartTimeUtc = DATEADD(second, 160, GETUTCDATE()), RoundStartSeat = $nextRoundStartSeat, CurrentSeat = $nextRoundStartSeat, EndingSeat = NULL WHERE GameId = $gameId;";
						}
					}
				}
			}
			echo $updateResult;
			//$updateResult = $conn->query($updateSql);
		}
	}
	//$request = json_decode($json);

	//if ($request.)
};


// Get game status
$gameStatusResult = $conn->query("SELECT TOP 1 *, DATEDIFF(second, GETUTCDATE(), RoundStartTimeUtc) AS TimeLeft FROM GameStatus ORDER BY Id DESC");
$flippedUpCardsResult = $conn->query("SELECT TOP 1 *, DATEDIFF(second, GETUTCDATE(), RoundStartTimeUtc) AS TimeLeft FROM GameStatus ORDER BY Id DESC");
$playerSeatsResult = $conn->query("SELECT * FROM Seat WHERE GameId = (SELECT MAX(GameId) FROM GameStatus)");
$playerCardsResult = $conn->query("SELECT * FROM Card c
							 JOIN Game g on g.Id = c.GameId
							 WHERE g.Id = (SELECT MAX(Id) FROM GameStatus)
							 AND c.RoundNumber = (CASE WHEN g.NextRoundStartSeat IS NULL THEN g.RoundNumber ELSE (g.RoundNumber - 1) END)
							 AND c.DeckIndex = g.UpCardIndex OR c.DeckIndex = g.UpCardIndex + 1 OR c.DeckIndex < (6 * SELECT COUNT(*) FROM Seat WHERE GameId = g.Id)
							 ORDER BY c.DeckIndex");
$playerScoresResult = $conn->query("SELECT * FROM Score WHERE GameId = (SELECT MAX(GameId) FROM GameStatus) ORDER BY RoundNumber ASC");

$game = $gameStatusResult->fetch_assoc();
$round = $game["RoundNumber"];
$currentSeat = $game["CurrentSeat"];
$currentSeatString = (is_null($currentSeat) ? "null" : $currentSeat);
$endingSeat = $game["EndingSeat"];
$endingSeatString = (is_null($endingSeat) ? "null" : $endingSeat);
$timeLeft = $game["TimeLeft"];
$timeLeftString = (is_null($timeLeft) ? "null" : $timeLeft);
$playersInfo = array();
$allPlayerCards = array();
$allScores = array();
$allSeats = array();
$seatInfoJsonStrings = array();//"null", "null", "null", "null", "null", "null", "null", "null", "null", "null", "null", "null", "null", "null");
$upCard = "null";
$drawCard = "null";
$seatNumbersTaken = array();
$forceFlipCardIndices = array();
$isLiveHand = ($timeLeft == null || $timeLeft < 0);

while($card = $playerCardsResult->fetch_assoc()) {
	if ($game["UpCardIndex"] == $card["DeckIndex"]) {
		$upCard = ($isLiveHand ? $card["Value"] : "null");
	}
	else if (($game["UpCardIndex"] + 1) == $card["DeckIndex"]) {
		$drawCard = ($isLiveHand ? $card["Value"] : "null");
	}
	else {
		$deckIndex = $card["DeckIndex"];
		$cardVal = $card["Value"];
		$handCardIndex = ($deckIndex % 6);
		if ($isLiveHand && $cardVal < 0 && ($handCardIndex >= 4)) {
			$upCount = 0;
			for ($i = 1; $i <= $handCardIndex; i++) {
				$earlierCardVal = ($allPlayerCards[$deckIndex - $i])["Value"];
				$upCount = $upCount + ($earlierCardVal > 0 ? 1 : 0);
			}

			if (($handCardIndex === 4 && $upCount <= 0) || ($handCardIndex === 5 && $upCount <= 1)) {
				$cardVal = abs($cardVal);
				array_add($forceFlipCardIndices, $deckIndex);
			}
		}

		if ($cardVal < -54) {
			$card["Value"] = -2;
		}
		else if ($cardVal < 0) {
			$card["Value"] = -1;
		}
		else {
			$card["Value"] = $cardVal;
		}
		array_push($allPlayerCards, $card);
	}
}

if (count($forceFlipCardIndices) > 0) {
	$cardIndicesText = implode(",", $forceFlipCardIndices);
	$forceFlipDrawTwoResult = $conn->query("UPDATE Card SET Value = abs(Value) WHERE GameId = $game AND RoundNumber = $round AND DeckIndex IN ($cardIndicesText);");
}

while($roundScore = $playerScoresResult->fetch_assoc()) {
	array_push($allScores, $roundScore);
}
while($seat = $playerSeatsResult->fetch_assoc()) {
	array_push($seatNumbersTaken, $seat["SeatNumber"]);
	array_push($allSeats, $seat);
}
for ($s = 0; $s < 14; $s++) {
	$filledSeatIndex = array_search($seatsTaken, $s);
	if (!$searchIndex) {
		array_push($seatInfoJsonStrings, "null");
	}
	else {
		$seat = $allSeats[$s];
		$playerId = $seat["PlayerId"];
		$jsonString = "{i:$playerId,s:[";
		$delimiter = "";
		for ($scoreIndex = 0; $scoreIndex < $allScores.length; $scoreIndex++) {
			$score = $allScores[$scoreIndex];
			if ($score["PlayerId"] == $playerId) {
				$jsonString = $jsonString . $delimiter . $score["RoundScore"];
				$delimiter = ",";
			}
		}
		$jsonString = $jsonString . "],c:[";

		$startingCardIndex = (6 * $filledSeatIndex);
		for ($cardIndex = 0; $cardIndex < 6; $cardIndex++) {
			$card = $allPlayerCards[$startingCardIndex + $cardIndex];
			if ($cardIndex > 0) {
				$jsonString = $jsonString . ",";
			}
			$jsonString = $jsonString . $card["Value"];
		}
		$jsonString = $jsonString . "]}";

		array_push($seatInfoJsonStrings, $jsonString);
	}
}

$jsonReturn = "{r:$round,c:$currentSeat,f:$endingSeat,t:$timeLeft,u:$upCard,d:$drawCard,s:[";
for ($s = 0; $s < 14; $s++) {
	$jsonReturn = $jsonReturn . ($seatInfoJsonStrings[$s]);
	if ($s < 13) {
		$jsonReturn = $jsonReturn . ",";
	}
}

$jsonReturn = $jsonReturn . "]}";
echo $jsonReturn;

$conn->close();
?>