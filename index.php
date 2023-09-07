<?php
session_start();

// Function to calculate the value of a blackjack hand
function calculateHandValue($hand) {
    $value = 0;
    $numAces = 0;

    foreach ($hand as $card) {
        if ($card['value'] === 'A') {
            $numAces++;
        } elseif (is_numeric($card['value'])) {
            $value += $card['value'];
        } else {
            $value += 10; // Face cards are worth 10 points
        }
    }

    // Handle Aces to get the best possible value
    for ($i = 0; $i < $numAces; $i++) {
        if ($value + 11 <= 21) {
            $value += 11;
        } else {
            $value += 1;
        }
    }

    return $value;
}

// Create a deck of cards
$deck = [];
$suits = ['Spades', 'Hearts', 'Diamonds', 'Clubs'];
$values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

foreach ($suits as $suit) {
    foreach ($values as $value) {
        $deck[] = ['value' => $value, 'suit' => $suit];
    }
}

shuffle($deck);

// Start a new hand
if (!isset($_SESSION['player_hand']) || isset($_POST['new_game'])) {
    $_SESSION['player_hand'] = [];
    $_SESSION['dealer_hand'] = [];

    // Deal two cards to the player and one to the dealer
    for ($i = 0; $i < 2; $i++) {
        $_SESSION['player_hand'][] = array_pop($deck);
        $_SESSION['dealer_hand'][] = array_pop($deck);
    }
}

// Game logic
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'hit') {
        $_SESSION['player_hand'][] = array_pop($deck);

        // Check if the player busted (went over 21)
        if (calculateHandValue($_SESSION['player_hand']) > 21) {
            echo "You busted! Dealer wins.";
            unset($_SESSION['player_hand']);
            unset($_SESSION['dealer_hand']);
        }
    } elseif ($_POST['action'] === 'stand') {
        // Dealer plays automatically until they have at least 17 points
        while (calculateHandValue($_SESSION['dealer_hand']) < 17) {
            $_SESSION['dealer_hand'][] = array_pop($deck);
        }

        $playerHandValue = calculateHandValue($_SESSION['player_hand']);
        $dealerHandValue = calculateHandValue($_SESSION['dealer_hand']);

        // Determine the winner
        if ($dealerHandValue > 21 || $playerHandValue > $dealerHandValue) {
            echo "You win!";
        } elseif ($playerHandValue < $dealerHandValue) {
            echo "Dealer wins.";
        } else {
            echo "Tie.";
        }

        unset($_SESSION['player_hand']);
        unset($_SESSION['dealer_hand']);
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
        <?php foreach ($_SESSION['player_hand'] as $card): ?>
            <p><?php echo $card['value'] . ' of ' . $card['suit']; ?></p>
        <?php endforeach; ?>

        <p>Your hand value: <?php echo calculateHandValue($_SESSION['player_hand']); ?></p>

        <form method="post">
            <input type="submit" name="action" value="hit">
            <input type="submit" name="action" value="stand">
        </form>
    <?php endif; ?>
</body>
</html>
