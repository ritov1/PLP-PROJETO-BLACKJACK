<?php
session_start();

class BlackjackGame {
    private $deck;
    private $playerHand;
    private $dealerHand;

    public function __construct() {
        $this->deck = [];
        $this->playerHand = [];
        $this->dealerHand = [];
    }

    public function startNewGame() {
        $this->deck = $this->createDeck();
        $this->shuffleDeck();
        $this->dealInitialHands();
    }

    private function createDeck() {
        $suits = ['Spades', 'Hearts', 'Diamonds', 'Clubs'];
        $values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
        $deck = [];

        foreach ($suits as $suit) {
            foreach ($values as $value) {
                $deck[] = ['value' => $value, 'suit' => $suit];
            }
        }

        return $deck;
    }

    private function shuffleDeck() {
        shuffle($this->deck);
    }

    private function dealInitialHands() {
        $this->playerHand = [];
        $this->dealerHand = [];

        for ($i = 0; $i < 2; $i++) {
            $this->playerHand[] = array_pop($this->deck);
            $this->dealerHand[] = array_pop($this->deck);
        }
    }

    public function playerHit() {
        $this->playerHand[] = array_pop($this->deck);

        if ($this->calculateHandValue($this->playerHand) > 21) {
            return "You busted! Dealer wins.";
        }

        return null;
    }

    public function playerStand() {
        while ($this->calculateHandValue($this->dealerHand) < 17) {
            $this->dealerHand[] = array_pop($this->deck);
        }

        $playerHandValue = $this->calculateHandValue($this->playerHand);
        $dealerHandValue = $this->calculateHandValue($this->dealerHand);

        if ($dealerHandValue > 21 || $playerHandValue > $dealerHandValue) {
            return "You win!";
        } elseif ($playerHandValue < $dealerHandValue) {
            return "Dealer wins.";
        } else {
            return "Tie.";
        }
    }

    private function calculateHandValue($hand) {
        $value = 0;
        $numAces = 0;

        foreach ($hand as $card) {
            if ($card['value'] === 'A') {
                $numAces++;
            } elseif (is_numeric($card['value'])) {
                $value += $card['value'];
            } else {
                $value += 10;
            }
        }

        for ($i = 0; $i < $numAces; $i++) {
            if ($value + 11 <= 21) {
                $value += 11;
            } else {
                $value += 1;
            }
        }

        return $value;
    }
}

$game = new BlackjackGame();

if (isset($_POST['new_game'])) {
    $game->startNewGame();
}

if (isset($_POST['action'])) {
    if ($_POST['action'] === 'hit') {
        $message = $game->playerHit();
    } elseif ($_POST['action'] === 'stand') {
        $message = $game->playerStand();
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Blackjack</title>
</head>
<body>
    <h1>Blackjack</h1>

    <?php if (!isset($_SESSION['player_hand'])): ?>
        <form method="post">
            <input type="submit" name="new_game" value="New Game">
        </form>
    <?php else: ?>
        <h2>Your Hand:</h2>
        <?php foreach ($game->getPlayerHand() as $card): ?>
            <p><?php echo $card['value'] . ' of ' . $card['suit']; ?></p>
        <?php endforeach; ?>

        <p>Your hand value: <?php echo $game->calculateHandValue($game->getPlayerHand()); ?></p>

        <form method="post">
            <input type="submit" name="action" value="hit">
            <input type="submit" name="action" value="stand">
        </form>

        <?php if (isset($message)): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>


