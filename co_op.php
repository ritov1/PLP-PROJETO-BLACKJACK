<?php
session_start();

class Card
{
    public $value;
    public $suit;

    public function __construct($value, $suit)
    {
        $this->value = $value;
        $this->suit = $suit;
    }
}

class Player
{
    public $name;
    public $hand = [];

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function addCard($card)
    {
        $this->hand[] = $card;
    }

    public function getScore()
    {
        $score = 0;
        $numAces = 0;

        foreach ($this->hand as $card) {
            $value = $card->value;

            if ($value === 'Ace') {
                $numAces++;
                $score += 11;
            } elseif (is_numeric($value)) {
                $score += $value;
            } else {
                $score += 10;
            }
        }

        // Adjust score for aces to avoid busting
        while ($score > 21 && $numAces > 0) {
            $score -= 10;
            $numAces--;
        }

        return $score;
    }
}

function initializeDeck()
{
    $suits = ['♥', '♦', '♣', '♠'];
    $values = ['Ace', 2, 3, 4, 5, 6, 7, 8, 9, 10, 10, 10, 10];

    $cards = [];
    foreach ($suits as $suit) {
        foreach ($values as $value) {
            $cards[] = new Card($value, $suit);
        }
    }

    shuffle($cards);
    return $cards;
}

function dealInitialCards($player)
{
    for ($i = 0; $i < 2; $i++) {
        $player->addCard(array_pop($_SESSION['deck']));
    }
}

function aiTurn($aiPlayer)
{
    // Basic AI strategy: Hit until the score is at least 17
    while ($aiPlayer->getScore() < 17) {
        $aiPlayer->addCard(array_pop($_SESSION['deck']));
    }
}

function findWinner()
{
    $targetScore = 42;

    $humanScore = $_SESSION['human']->getScore();
    $aiFriendScore = $_SESSION['aiFriend']->getScore();
    $ai1Score = $_SESSION['ai1']->getScore();
    $ai2Score = $_SESSION['ai2']->getScore();

    $humanTeamScore = $humanScore + $aiFriendScore;
    $aiTeamScore = $ai1Score + $ai2Score;

    // Check if any team has a total score more than 42
    if ($humanTeamScore > $targetScore && $aiTeamScore > $targetScore) {
        return 'Draw';
    } elseif ($humanTeamScore > $targetScore) {
        return 'AI Team';
    } elseif ($aiTeamScore > $targetScore) {
        return 'Human Team';
    }

    // Check which team is closer to 42
    $humanDifference = abs($targetScore - $humanTeamScore);
    $aiDifference = abs($targetScore - $aiTeamScore);

    if ($humanDifference < $aiDifference) {
        return 'Human Team';
    } elseif ($aiDifference < $humanDifference) {
        return 'AI Team';
    } else {
        return 'Draw';
    }
}


if (!isset($_SESSION['deck'])) {
    $_SESSION['deck'] = initializeDeck();
}

if (!isset($_SESSION['human'])) {
    $_SESSION['human'] = new Player('Human');
    dealInitialCards($_SESSION['human']);
}

if (!isset($_SESSION['aiFriend'])) {
    $_SESSION['aiFriend'] = new Player('AI Friend');
    dealInitialCards($_SESSION['aiFriend']);
}

if (!isset($_SESSION['ai1'])) {
    $_SESSION['ai1'] = new Player('AI 1');
    dealInitialCards($_SESSION['ai1']);
}

if (!isset($_SESSION['ai2'])) {
    $_SESSION['ai2'] = new Player('AI 2');
    dealInitialCards($_SESSION['ai2']);
}

// Process the player's actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Player's turn
    if (isset($_POST['hit'])) {
        $_SESSION['human']->addCard(array_pop($_SESSION['deck']));
        $response = [
            'humanScore' => $_SESSION['human']->getScore(),
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } elseif (isset($_POST['standHuman'])) {
        // AI 1's turn
        aiTurn($_SESSION['ai1']);
        $response = [
            'ai1Score' => $_SESSION['ai1']->getScore(),
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } elseif (isset($_POST['standAI1'])) {
        // AI Friend's turn
        aiTurn($_SESSION['aiFriend']);
        $response = [
            'aiFriendScore' => $_SESSION['aiFriend']->getScore(),
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } elseif (isset($_POST['standAIFriend'])) {
        // AI 2's turn
        aiTurn($_SESSION['ai2']);
        $response = [
            'ai2Score' => $_SESSION['ai2']->getScore(),
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } elseif (isset($_POST['standAI2'])) {
        // Determine the winner after all turns
        $winner = findWinner();
        $response = [
            'winner' => $winner,
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <style>
      body {
          font-family: 'Arial', sans-serif;
          background-color: #2c3e50;
          color: #ecf0f1;
          text-align: center;
          margin: 30px;
      }

      h1 {
          color: #e74c3c;
      }

      p {
          font-size: 18px;
          margin-bottom: 10px;
      }

      #gameForm {
          margin-top: 20px;
      }

      #hitButton, #standButton {
          background-color: #3498db;
          color: #ecf0f1;
          font-size: 16px;
          padding: 10px 20px;
          margin-right: 10px;
          cursor: pointer;
          border: none;
          border-radius: 5px;
      }

      #hitButton:hover, #standButton:hover {
          background-color: #2980b9;
      }

      #humanScore, #aiFriendScore, #ai1Score, #ai2Score {
          font-weight: bold;
          color: #e74c3c;
      }
  </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Co-op Blackjack Game</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>

<h1>Co-op Blackjack Game</h1>

<!-- Display the player and AI players' hands and scores -->
<p>
    <?php
    echo "{$_SESSION['human']->name}'s hand: ";
    foreach ($_SESSION['human']->hand as $card) {
        echo "{$card->value} of {$card->suit}, ";
    }
    echo " (Score: <span id='humanScore'>{$_SESSION['human']->getScore()}</span>)";
    ?>
</p>

<p>
    <?php
    echo "{$_SESSION['aiFriend']->name}'s hand: ";
    foreach ($_SESSION['aiFriend']->hand as $card) {
        echo "{$card->value} of {$card->suit}, ";
    }
    echo " (Score: <span id='aiFriendScore'>{$_SESSION['aiFriend']->getScore()}</span>)";
    ?>
</p>

<p>
    <?php
    echo "{$_SESSION['ai1']->name}'s hand: ";
    foreach ($_SESSION['ai1']->hand as $card) {
        echo "{$card->value} of {$card->suit}, ";
    }
    echo " (Score: <span id='ai1Score'>{$_SESSION['ai1']->getScore()}</span>)";
    ?>
</p>

<p>
    <?php
    echo "{$_SESSION['ai2']->name}'s hand: ";
    foreach ($_SESSION['ai2']->hand as $card) {
        echo "{$card->value} of {$card->suit}, ";
    }
    echo " (Score: <span id='ai2Score'>{$_SESSION['ai2']->getScore()}</span>)";
    ?>
</p>

<!-- Form for player actions -->
<form id="gameForm" method="post">
    <input type="button" id="hitButton" value="Hit">
    <input type="button" id="standButton" value="Stand">
</form>

<script>
$(document).ready(function() {
    $("#hitButton").click(function() {
        // Send a request to hit and update the player's score
        $.post('multiplayer.php', { hit: true }, function(response) {
            $("#humanScore").text(response.humanScore);
        }, 'json');
    });

    $("#standButton").click(function() {
        // Send a request to stand and update the scores for AI 1, AI Friend, and AI 2
        $.post('multiplayer.php', { standHuman: true }, function(response) {
            $("#ai1Score").text(response.ai1Score);
        }, 'json').done(function() {
            $.post('multiplayer.php', { standAI1: true }, function(response) {
                $("#aiFriendScore").text(response.aiFriendScore);
            }, 'json').done(function() {
                $.post('multiplayer.php', { standAIFriend: true }, function(response) {
                    $("#ai2Score").text(response.ai2Score);
                }, 'json').done(function() {
                    // Determine the winner after all turns and show a popup
                    $.post('multiplayer.php', { standAI2: true }, function(response) {
                        alert('Winner: ' + response.winner);
                        location.reload();
                    }, 'json');
                });
            });
        });
    });
});
</script>

</body>
</html>
