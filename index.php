<?php

class Card {
    private $suit;
    private $rank;

    public function __construct($suit, $rank) {
        $this->suit = $suit;
        $this->rank = $rank;
    }

    public function __toString() {
        return "{$this->rank} of {$this->suit}";
    }

    public function getRank() {
        return $this->rank;
    }

    public function getValue() {
        $valueMap = [
            '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6,
            '7' => 7, '8' => 8, '9' => 9, '10' => 10,
            'Jack' => 10, 'Queen' => 10, 'King' => 10, 'Ace' => 11
        ];

        return $valueMap[$this->rank];
    }
}

class Deck {
    private $cards = [];

    public function __construct() {
        $suits = ['Hearts', 'Diamonds', 'Clubs', 'Spades'];
        $ranks = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'Jack', 'Queen', 'King', 'Ace'];

        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $this->cards[] = new Card($suit, $rank);
            }
        }

        shuffle($this->cards);
    }

    public function drawCard() {
        return array_pop($this->cards);
    }
}

class BlackjackGame {
    private $playerHand = [];
    private $dealerHand = [];
    private $deck;

    public function __construct() {
        $this->deck = new Deck();
        $this->playerHand[] = $this->deck->drawCard();
        $this->dealerHand[] = $this->deck->drawCard();
        $this->playerHand[] = $this->deck->drawCard();
        $this->dealerHand[] = $this->deck->drawCard();
    }

    public function getPlayerHand() {
        return $this->playerHand;
    }

    public function getDealerHand() {
        return $this->dealerHand;
    }

    public function hit() {
        $this->playerHand[] = $this->deck->drawCard();
    }

    public function stand() {
        while ($this->calculateHandValue($this->dealerHand) < 17) {
            $this->dealerHand[] = $this->deck->drawCard();
        }
    }

    public function calculateHandValue($hand) {
        $value = 0;
        $aceCount = 0;

        foreach ($hand as $card) {
            $value += $card->getValue();

            if ($card->getRank() === 'Ace') {
                $aceCount++;
            }
        }

        while ($aceCount > 0 && $value > 21) {
            $value -= 10;
            $aceCount--;
        }

        return $value;
    }

    public function getPlayerHandValue() {
        return $this->calculateHandValue($this->playerHand);
    }

    public function getDealerHandValue() {
        return $this->calculateHandValue($this->dealerHand);
    }

    public function getResult() {
        $playerValue = $this->getPlayerHandValue();
        $dealerValue = $this->getDealerHandValue();

        if ($playerValue > 21) {
            return "Dealer wins. Player busts!";
        } elseif ($dealerValue > 21) {
            return "Player wins. Dealer busts!";
        } elseif ($playerValue > $dealerValue) {
            return "Player wins!";
        } elseif ($playerValue < $dealerValue) {
            return "Dealer wins!";
        } else {
            return "It's a tie!";
        }
    }
}

// Usage example
$game = new BlackjackGame();
$playerHand = $game->getPlayerHand();
$dealerHand = $game->getDealerHand();

// Perform game actions, such as hitting and standing, and calculate the result accordingly.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blackjack Game</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Blackjack Game</h1>
    
    <div id="game-container">
        <div id="player-hand">
            <h2>Player's Hand</h2>
            <ul id="player-cards">
                <?php
                foreach ($playerHand as $card) {
                    echo "<li>{$card}</li>";
                }
                ?>
            </ul>
            <p id="player-value">Value: <?php echo $game->getPlayerHandValue(); ?></p>
        </div>
        
        <div id="dealer-hand">
            <h2>Dealer's Hand</h2>
            <ul id="dealer-cards">
                <?php
                foreach ($dealerHand as $card) {
                    echo "<li>{$card}</li>";
                }
                ?>
            </ul>
            <p id="dealer-value">Value: <?php echo $game->getDealerHandValue(); ?></p>
        </div>
        
        <form method="post">
            <button type="submit" name="action" value="hit">Hit</button>
            <button type="submit" name="action" value="stand">Stand</button>
        </form>
        
        <p id="game-result">
            <?php
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'hit') {
                    $game->hit();
                } elseif ($_POST['action'] === 'stand') {
                    $game->stand();
                }
                echo $game->getResult();
            }
            ?>
        </p>
    </div>
</body>
</html>
