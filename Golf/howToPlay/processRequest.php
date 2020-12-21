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

$seatingChart = array(6,10,12,4,0,1,3,11,7,8,5,13,9,2);
$debug = isset($_POST['d']);

if (isset($_POST['a'])) {
	$gameAction = $_POST['a'];

	if ($gameAction == 278177) {
		if (!mysqli_query($conn, "UPDATE GameStatus SET Round = 1, RoundStartUtc = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 160 SECOND) WHERE Id = (SELECT MAX(Id) FROM GameStatus)")) {
		    echo "Start round 1 error: (" . $conn->errno . ") " . $conn->error;
		}
	}
	if (($gameAction >= 122300000 && $gameAction < 122316384) || ($gameAction >= 122400000 && $gameAction < 122416384)) {
		// New game
		$playersInt = ($gameAction % 100000);
		$startRoundOne = ($gameAction > 122380000);
		$mult = 1;
		$currVal = 0;
		$livePlayers = array();

		for ($x = 0; $x < 14; $x++) {
			$p = 13 - $x;
			$playersInt = $playersInt - $currVal;
			$prevMult = $mult;
			$mult = $mult * 2;

			$currVal = $playersInt % $mult;
			if ($currVal == $prevMult) {
				array_push($livePlayers, $p);
			}
		}

		$startingPlayer = $livePlayers[rand(0, count($livePlayers) - 1)];
		$insertSeatsSql = "INSERT INTO Seat (GameId, SeatNumber, PlayerId) VALUES ";
		$firstOne = true;
		$startingSeat = null;
		$startingUpCardIndex = 6 * count($livePlayers);

		$maxGameResult = mysqli_query($conn, "SELECT MAX(Id) AS MaxId FROM GameStatus");
		$maxIdRow = $maxGameResult->fetch_assoc();
		$maxId = $maxIdRow["MaxId"];
		$gameId = (is_null($maxId) ? 0 : ($maxId + 1));

		for ($seat = 0; $seat < 14; $seat++) {
			$player = $seatingChart[$seat];
			if (in_array($player, $livePlayers)) {
				if (!$firstOne) { $insertSeatsSql = $insertSeatsSql . ","; }
				$firstOne = false;

				$insertSeatsSql = $insertSeatsSql . "($gameId, $seat, $player)";
				if ($startingPlayer == $player) {
					$startingSeat = $seat;
				}
			}
		}

		$startingRound = ($startRoundOne ? 1 : 0);
		$roundStartText = ($startRoundOne ? "DATE_ADD(UTC_TIMESTAMP(), INTERVAL 160 SECOND)" : "NULL");

		$newGameSql = "INSERT INTO GameStatus(Id, Round, RoundStartUtc, CurrentSeat, EndingSeat, UpCardIndex, RoundStartSeat) VALUES ($gameId, $startingRound, $roundStartText, $startingSeat, NULL, $startingUpCardIndex, $startingSeat)";

		$insertCardsSql = "INSERT INTO Card(GameId, Round, DeckIndex, Value) VALUES ";
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
				$insertCardsSql = $insertCardsSql . "($gameId, $round, $deckIndex, $cardVal)";
			}
		}

		//$testSql = "SELECT 5; SELECT 8;";
		//echo $newGameSql;
/*		if ($conn->multi_query($testSql)) {
			echo "SUCCESS TEST";
		    do {
		        // store first result set
		        if ($result = mysqli_store_result($conn)) {
		            while ($row = mysqli_fetch_row($result)) {
		                echo $row;
		            }
		            mysqli_free_result($result);
		        }
		    } while (mysqli_next_result($conn));
		}
		else {
		    echo "01 failed: (" . $conn->errno . ") " . $conn->error;
		}*/

		/*$allSql = $newGameSql . "; " . $insertSeatsSql . "; " . $insertCardsSql . ";";
		if ($conn->multi_query($allSql)) {
		    do {
		        // store first result set 
		        if ($result = mysqli_store_result($conn)) {
		            while ($row = mysqli_fetch_row($result)) {
		                printf($row[0]);
		            }
		            mysqli_free_result($result);
		        }
		        // print divider 
		        if (mysqli_more_results($conn)) {
		            printf("-----------------\n");
		        }
		    } while (mysqli_next_result($conn));
		}
		else {
		    echo "1 failed: (" . $conn->errno . ") " . $conn->error;
		}*/
		$newGameSql = $newGameSql . ";";
		$insertSeatsSql = $insertSeatsSql . ";";
		$insertCardsSql = $insertCardsSql . ";";

		//echo $newGameSql;
		//echo $insertSeatsSql;
		//echo $insertCardsSql;

		if (!mysqli_query($conn, $newGameSql)) {
		    echo "Query part 1 failed: (" . $conn->errno . ") " . $conn->error;
		}
		else if (!mysqli_query($conn, $insertSeatsSql)) {
		    echo "Query part 2 failed: (" . $conn->errno . ") " . $conn->error;
		}
		else if (!mysqli_query($conn, $insertCardsSql)) {
		    echo "Query part 3 failed: (" . $conn->errno . ") " . $conn->error;
		}
	}
	else {
		$action = ($gameAction % 100);
		$player = (($gameAction - $action) / 100);

		if ($debug) {
			//echo "action = $action, player = $player";
		}

		$isDrawTwo = false;
		$isPlayersTurn = false;

		$gameStatusSql = "SELECT g.*, s.SeatNumber AS PlayerSeat, TIMESTAMPDIFF(second, UTC_TIMESTAMP(), RoundStartUtc) AS TimeDiff,
							(SELECT COUNT(*) FROM Seat WHERE GameId = g.Id) AS TotalPlayers,
							(SELECT COUNT(*) FROM Seat WHERE GameId = g.Id AND SeatNumber < s.SeatNumber) AS NumPrevPlayers,
							(SELECT MIN(SeatNumber) FROM Seat WHERE GameId = g.Id AND (SeatNumber > s.SeatNumber OR s.SeatNumber = (SELECT MAX(SeatNumber) FROM Seat WHERE GameId = g.Id))) AS NextPlayerSeat,
							(SELECT MIN(SeatNumber) FROM Seat WHERE GameId = g.Id AND (SeatNumber > g.RoundStartSeat OR g.RoundStartSeat = (SELECT MAX(SeatNumber) FROM Seat WHERE GameId = g.Id))) AS NextRoundStartSeat
						  FROM GameStatus g
						  LEFT JOIN Seat s on s.GameId = g.Id AND s.PlayerId = $player
						  WHERE g.Id = (SELECT MAX(Id) FROM GameStatus)
						  LIMIT 1";
		if ($debug) {
			//echo $gameStatusSql;
		}

		$result = mysqli_query($conn, $gameStatusSql);
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

		if ($debug) {
			//echo ("numRows: " . $result->num_rows);
		}

		if ($result->num_rows > 0) {
			$gameStatus = $result->fetch_assoc();
			$gameId = $gameStatus["Id"];
			$round = $gameStatus["Round"];
			$roundStart = $gameStatus["RoundStartUtc"];
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

			if ($debug) {
				//echo "at least one row returned round=$round, roundStart=$roundStart, timeDiff=$timeDiff, currSeat=$currentSeat, playerSeat=$playerSeat";
			}

			if ($round > 0 && !is_null($roundStart) && $timeDiff >= 0 && $timeDiff <= 70) {
				$isDrawTwo = true;
			}
			if ($round > 0 && (is_null($roundStart) || $timeDiff < 0) && $currentSeat == $playerSeat) {
				$isPlayersTurn = true;
			}
		}

		if ($debug) {
			//echo "query done, isDrawTwo = $isDrawTwo, isPlayersTurn = $isPlayersTurn";
		}

		if ($isPlayersTurn || ($action >= 0 && $action <= 5 && $isDrawTwo)) {
			$updateSql = array();

			$cardIndices = array();
			$playerDeckIndexStart = 6 * $numPrevPlayers;
			for ($i = 0; $i < 6; $i++) {
				array_push($cardIndices, ($playerDeckIndexStart + $i));
			}
			array_push($cardIndices, $upCardIndex);
			array_push($cardIndices, ($upCardIndex + 1));
			$cardIndicesText = implode(",", $cardIndices);

			$cardsSql = "SELECT DeckIndex, Value FROM Card WHERE GameId = $gameId AND Round = $round
						 AND DeckIndex IN ($cardIndicesText)
						 ORDER BY DeckIndex ASC";
			$cardsResult = mysqli_query($conn, $cardsSql);
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
				if ($debug) {
					echo "flip card";
				}
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
					array_push($updateSql, "UPDATE Card SET Value = $newVal WHERE GameId = $gameId AND Round = $round AND DeckIndex = $deckIndex;");
					$cardVals[$action] = $newVal;
				}
			}
			else if ($action == 6) {
				// Flip draw card
				if ($debug) {
					echo "flip draw card";
				}
				if ($drawCard["Value"] < 0) {
					$newVal = abs($drawCard["Value"]);					
					$deckIndex = $drawCard["DeckIndex"];
					array_push($updateSql, "UPDATE Card SET Value = $newVal WHERE GameId = $gameId AND Round = $round AND DeckIndex = $deckIndex;");
				}
			}
			else if ($action == 7) {
				// Discard
				if ($debug) {
					echo "discard";
				}
				if ($drawCard["Value"] > 0) {
					$newUpCardIndex = $upCardIndex + 1;
					if ($newUpCardIndex < 107) {
						array_push($updateSql, "UPDATE GameStatus SET UpCardIndex = $newUpCardIndex WHERE Id = $gameId;");
					}
					else {
						// Reshuffle since there's no more draw cards
						$newUpCardIndex = $totalPlayers * 6;
						$drawVal = $drawCard["Value"];
						array_push($updateSql, "UPDATE GameStatus SET UpCardIndex = $newUpCardIndex WHERE Id = $gameId;");
						array_push($updateSql, "UPDATE Card SET Value = $drawVal WHERE GameId = $gameId AND Round = $round AND DeckIndex = $newUpCardIndex;");
						// Fetch the remaining cards and reshuffle them
						$discardsSql = "SELECT Value FROM Card WHERE GameId = $gameId AND Round = $round
									 AND DeckIndex >= $newUpCardIndex AND DeckIndex < 107";

						$discardsResult = mysqli_query($conn, $discardsSql);
						$discards = array();
						while($row = $discardsResult->fetch_assoc()) {
							array_push($discards, $row["Value"]);
						}

						$numDiscards = count($discards);
						for ($discardIndex = 0; $discardIndex < $numDiscards; $discardIndex++) {
							$randomInt = rand($discardIndex, ($numDiscards - 1));
							$temp = $discards[$discardIndex];
							$cardVal = -abs($discards[$randomInt]);

							$discards[$discardIndex] = $cardVal;
							$discards[$randomInt] = $temp;
							$newIndex = $newUpCardIndex + $discardIndex + 1;

							array_push($updateSql, "UPDATE Card SET Value = $cardVal WHERE GameId = $gameId AND Round = $round AND DeckIndex = $newIndex;");
						}
					}
				}
			}
			else if ($action >= 10 && $action <= 15) {
				// Swap with either discard or draw card
				if ($debug) {
					echo "swap with up card";
				}
				$card = $cards[$action - 10];
				$cardVal = abs($card["Value"]);
				$deckIndex = $card["DeckIndex"];

				if ($drawCard["Value"] < 0) {
					// Draw card wasn't flipped - swap with discard, no need to increment up-card index
					$swapIndex = $upCardIndex;
					$swapVal = $upCard["Value"];
					array_push($updateSql, "UPDATE Card SET Value = $swapVal WHERE GameId = $gameId AND Round = $round AND DeckIndex = $deckIndex;");
					array_push($updateSql, "UPDATE Card SET Value = $cardVal WHERE GameId = $gameId AND Round = $round AND DeckIndex = $swapIndex;");
					$cardVals[$action - 10] = $swapVal;
				}
				else {
					// Draw card was flipped - swap with draw card
					$swapIndex = $upCardIndex + 1;
					$swapVal = $drawCard["Value"];
					array_push($updateSql, "UPDATE Card SET Value = $swapVal WHERE GameId = $gameId AND Round = $round AND DeckIndex = $deckIndex;");
					$cardVals[$action - 10] = $swapVal;

					$newUpCardIndex = $upCardIndex + 1;
					if ($newUpCardIndex < 107) {
						array_push($updateSql, "UPDATE Card SET Value = $cardVal WHERE GameId = $gameId AND Round = $round AND DeckIndex = $swapIndex;");
						array_push($updateSql, "UPDATE GameStatus SET UpCardIndex = $newUpCardIndex WHERE Id = $gameId;");
					}
					else {
						// Reshuffle since there's no more draw cards
						$newUpCardIndex = $totalPlayers * 6;
						$newUpCardVal = $cardVal;
						array_push($updateSql, "UPDATE GameStatus SET UpCardIndex = $newUpCardIndex WHERE Id = $gameId;");
						array_push($updateSql, "UPDATE Card SET Value = $cardVal WHERE GameId = $gameId AND Round = $round AND DeckIndex = $newUpCardIndex;");
						// Fetch the remaining cards and reshuffle them
						$discardsSql = "SELECT Value FROM Card WHERE GameId = $gameId AND Round = $round
									 AND DeckIndex >= $newUpCardIndex AND DeckIndex < 107";

						$discardsResult = mysqli_query($conn, $discardsSql);
						$discards = array();
						while($row = $discardsResult->fetch_assoc()) {
							array_push($discards, $row["Value"]);
							if ($debug) {
								echo $row["Value"] . ",";
							}
						}

						if ($debug) {
							echo " " . count($discards) . " rows returned from: $discardsSql";
						}

						$numDiscards = count($discards);
						for ($discardIndex = 0; $discardIndex < $numDiscards; $discardIndex++) {
							$randomInt = rand($discardIndex, ($numDiscards - 1));
							$temp = $discards[$discardIndex];
							$cardVal = -abs($discards[$randomInt]);

							$discards[$discardIndex] = $cardVal;
							$discards[$randomInt] = $temp;
							$newIndex = $newUpCardIndex + $discardIndex + 1;

							array_push($updateSql, "UPDATE Card SET Value = $cardVal WHERE GameId = $gameId AND Round = $round AND DeckIndex = $newIndex;");
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
					array_push($updateSql, "UPDATE GameStatus SET CurrentSeat = $nextPlayerSeat WHERE Id = $gameId;");
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
					$pairedCardCounts = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
					for ($i = 0; $i < 6; $i++) {
						$points = $cardValues[$i];
						$pairIndex = ($points == -3 ? 13 : $points);

						if ($cardValues[$i] == $cardValues[($i + 3) % 6]) {
							$points = 0;
							$pairedCardCounts[$pairIndex] = ($pairedCardCounts[$pairIndex]) + 1;
						}
						else if ($cardValues[$i] == 11) {
							$points = 20;
						}
						else if ($cardValues[$i] == 12) {
							$points = 10;
						}

						$roundScore = $roundScore + $points;
					}

					for ($i = 0; $i < 13; $i++) {
						if ($pairedCardCounts[$i] >= 6) {
							$roundScore = $roundScore - 30;
						}
						else if ($pairedCardCounts[$i] >= 4) {
							$roundScore = $roundScore - 20;
						}
					}

					array_push($updateSql, "INSERT INTO Score(GameId, PlayerId, Round, Score) VALUES ($gameId, $player, $round, $roundScore);");

					// Check for end of round
					if (is_null($endingSeat)) {
						// First player to go out in this round - set EndingSeat and flip over everyone else's cards
						array_push($updateSql, "UPDATE GameStatus SET CurrentSeat = $nextPlayerSeat, EndingSeat = $playerSeat WHERE Id = $gameId;");
						array_push($updateSql, "UPDATE Card SET Value = abs(Value) WHERE GameId = $gameId AND Round = $round AND DeckIndex < $totalPlayerCards;");
					}
					else if ($endingSeat == $nextPlayerSeat) {
						// End of round
						if ($round < 9) {
							$round++;
							array_push($updateSql, "UPDATE GameStatus SET Round = $round, RoundStartUtc = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 160 SECOND), RoundStartSeat = $nextRoundStartSeat, CurrentSeat = $nextRoundStartSeat, EndingSeat = NULL, UpCardIndex = $totalPlayerCards WHERE Id = $gameId;");
						}
						else {
							// End of game
							array_push($updateSql, "UPDATE GameStatus SET RoundStartUtc = NULL, RoundStartSeat = NULL, CurrentSeat = NULL WHERE Id = $gameId;");
						}
					}
					else {
						// Last round, but at least 1 other player still has a turn left - move on
						array_push($updateSql, "UPDATE GameStatus SET CurrentSeat = $nextPlayerSeat WHERE Id = $gameId;");
					}
				}
			}
			for ($i = 0; $i < count($updateSql); $i++) {
				$update = $updateSql[$i];
				if ($debug) {
					echo $update;
				}
				$updateResult = mysqli_query($conn, $update);
				if (!$updateResult) {
					echo $update;
					echo "Sql update failed: (" . $conn->errno . ") " . $conn->error;
				}
			}
		}
	}
};


// Get game status
$gameStatusResult = $conn->query("SELECT *, TIMESTAMPDIFF(second, UTC_TIMESTAMP(), RoundStartUtc) AS TimeLeft FROM GameStatus ORDER BY Id DESC LIMIT 1");
$playerSeatsResult = $conn->query("SELECT * FROM Seat WHERE GameId = (SELECT MAX(Id) FROM GameStatus)");
$playerCardsResult = $conn->query("SELECT c.* FROM Card c
							 JOIN GameStatus g on g.Id = c.GameId
							 WHERE g.Id = (SELECT MAX(Id) FROM GameStatus)
							 AND c.Round = (CASE WHEN g.Round = 0 OR g.RoundStartUtc IS NULL OR g.RoundStartUtc < DATE_ADD(UTC_TIMESTAMP(), INTERVAL 71 SECOND) THEN g.Round ELSE (g.Round - 1) END)
							 AND (c.DeckIndex = g.UpCardIndex OR c.DeckIndex = g.UpCardIndex + 1 OR c.DeckIndex < (SELECT (6 * COUNT(*)) FROM Seat WHERE GameId = g.Id))
							 ORDER BY c.DeckIndex");
$playerScoresResult = $conn->query("SELECT * FROM Score WHERE GameId = (SELECT MAX(Id) FROM GameStatus) ORDER BY Round ASC");

if (!$gameStatusResult || !$playerSeatsResult || !$playerCardsResult || !$playerScoresResult) {
    echo "Multi query part 2 failed: (" . $conn->errno . ") " . $conn->error;
}
else {
	$game = $gameStatusResult->fetch_assoc();
	$gameId = $game["Id"];
	$round = $game["Round"];
	$currentSeat = $game["CurrentSeat"];
	$currentSeatString = (is_null($currentSeat) ? "null" : $currentSeat);
	$endingSeat = $game["EndingSeat"];
	$endingSeatString = (is_null($endingSeat) ? "null" : $endingSeat);
	$timeLeft = $game["TimeLeft"];
	$timeLeftString = (is_null($timeLeft) || $timeLeft < 0) ? "null" : $timeLeft;
	$playersInfo = array();
	$allPlayerCards = array();
	$allScores = array();
	$allSeats = array();
	$seatInfoJsonStrings = array();//"null", "null", "null", "null", "null", "null", "null", "null", "null", "null", "null", "null", "null", "null");
	$upCard = "null";
	$drawCard = "null";
	$seatNumbersTaken = array(-70);
	$forceFlipCardIndices = array();
	$everyoneHasTwoFlipped = true;
	$isLiveHand = (is_null($timeLeft) || $timeLeft < 0);
	$isDrawTwo = (!is_null($timeLeft) && $timeLeft <= 70 && $timeLeft > 0);

	$rowsFetched = mysqli_num_rows($playerCardsResult);
	//echo ("Total player cards: $rowsFetched ");

	while($card = $playerCardsResult->fetch_assoc()) {
		if ($game["UpCardIndex"] == $card["DeckIndex"]) {
			$upCard = $card["Value"]; //($isLiveHand ? $card["Value"] : "null");
		}
		else if (($game["UpCardIndex"] + 1) == $card["DeckIndex"]) {
			$drawVal = $card["Value"];

			if ($drawVal < -54) {
				$drawVal = -2;
			}
			else if ($drawVal < 0) {
				$drawVal = -1;
			}

			$drawCard = $drawVal; //($isLiveHand ? $drawVal : "null");
		}
		else {
			$deckIndex = $card["DeckIndex"];
			$cardVal = $card["Value"];
			$handCardIndex = ($deckIndex % 6);

			if ($isLiveHand && $cardVal < 0 && ($handCardIndex >= 4)) {
				$upCount = 0;
				for ($i = 1; $i <= $handCardIndex; $i++) {
					$earlierCardVal = ($allPlayerCards[$deckIndex - $i])["Value"];
					$upCount = $upCount + ($earlierCardVal > 0 ? 1 : 0);
				}

				if (($handCardIndex == 4 && $upCount <= 0) || ($handCardIndex == 5 && $upCount <= 1)) {
					$cardVal = abs($cardVal);
					array_push($forceFlipCardIndices, $deckIndex);
				}
			}
			else if ($isDrawTwo && $handCardIndex == 5) {
				$upCount = ($cardVal > 0 ? 1 : 0);
				for ($i = 1; $i < 6; $i++) {
					$earlierCardVal = ($allPlayerCards[$deckIndex - $i])["Value"];
					$upCount = $upCount + ($earlierCardVal > 0 ? 1 : 0);
				}

				if ($upCount < 2) {
					$everyoneHasTwoFlipped = false;
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
		if ($debug) {
			echo " IN ($cardIndicesText)";
		}
		$forceFlipSql = "UPDATE Card SET Value = abs(Value) WHERE GameId = $gameId AND Round = $round AND DeckIndex IN ($cardIndicesText)";
		if ($debug) {
			echo $forceFlipSql;
		}
		$forceFlipDrawTwoResult = mysqli_query($conn, $forceFlipSql);
	}

	if ($everyoneHasTwoFlipped && $isDrawTwo) {
		$startRoundSql = "UPDATE GameStatus SET RoundStartUtc = UTC_TIMESTAMP() WHERE Id = $gameId";
		if ($debug) {
			echo $startRoundSql;
		}
		$startRoundResult = mysqli_query($conn, $startRoundSql);

		if ($startRoundResult) {
			$timeLeftString = "null";
		}
	}
	else if (!$isLiveHand) {
		$drawCard = "null";
		$upCard = "null";
	}

	while($roundScore = $playerScoresResult->fetch_assoc()) {
		array_push($allScores, $roundScore);
	}
	while($seat = $playerSeatsResult->fetch_assoc()) {
		array_push($seatNumbersTaken, $seat["SeatNumber"]);
		array_push($allSeats, $seat);
		//echo json_encode($seat);
	}

	if ($debug) {
		print_r($seatNumbersTaken);
	}

	for ($s = 0; $s < 14; $s++) {
		$filledSeatIndex = array_search($s, $seatNumbersTaken);
		if ($debug) {
			echo "s = $s, filledSeatIndex = $filledSeatIndex";
		}
		if ($filledSeatIndex >= 1 && $filledSeatIndex <= 14) {
			if ($debug) {
				echo " apparently its not null: [$filledSeatIndex] ";
			}
			$seat = $allSeats[$filledSeatIndex - 1];
			$playerId = $seat["PlayerId"];
			$jsonString = "{\"i\":$playerId,\"s\":[";
			$delimiter = "";
			for ($scoreIndex = 0; $scoreIndex < count($allScores); $scoreIndex++) {
				$score = $allScores[$scoreIndex];
				if ($score["PlayerId"] == $playerId) {
					$jsonString = $jsonString . $delimiter . $score["Score"];
					$delimiter = ",";
				}
			}
			$jsonString = $jsonString . "],\"c\":[";

			$startingCardIndex = (6 * ($filledSeatIndex - 1));
			for ($cardIndex = 0; $cardIndex < 6; $cardIndex++) {
				if (count($allPlayerCards) > ($startingCardIndex + $cardIndex)) {
					$card = $allPlayerCards[$startingCardIndex + $cardIndex];
					if ($cardIndex > 0) {
						$jsonString = $jsonString . ",";
					}
					$jsonString = $jsonString . $card["Value"];
				}
			}
			$jsonString = $jsonString . "]}";

			array_push($seatInfoJsonStrings, $jsonString);
		}
		else {
			if ($debug) {
				echo "push null seat $s: $filledSeatIndex";
			}
			array_push($seatInfoJsonStrings, "null");
		}
		
	}

	if (is_null($round)) {
		$round = 0;
	}
	$jsonReturn = "{\"r\":$round,\"c\":$currentSeatString,\"f\":$endingSeatString,\"t\":$timeLeftString,\"u\":$upCard,\"d\":$drawCard,\"s\":[";
	for ($s = 0; $s < 14; $s++) {
		$jsonReturn = $jsonReturn . ($seatInfoJsonStrings[$s]);
		if ($s < 13) {
			$jsonReturn = $jsonReturn . ",";
		}
	}

	$jsonReturn = $jsonReturn . "]}";
	echo $jsonReturn;
}

$conn->close();
?>