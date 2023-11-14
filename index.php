<?php
session_start();

function calculateHandValue($hand)
{
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



$deck = [];
$suits = ['&#9824', '&#9829', '&#9830', '&#9827'];
$values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

foreach ($suits as $suit) {
    foreach ($values as $value) {
        $deck[] = ['value' => $value, 'suit' => $suit];
    }
}

shuffle($deck);

if (!isset($_SESSION['player_hand']) || isset($_POST['new_game'])) {
    $_SESSION['player_hand'] = [];
    $_SESSION['dealer_hand'] = [];

    for ($i = 0; $i < 2; $i++) {
        $_SESSION['player_hand'][] = array_pop($deck);
        $_SESSION['dealer_hand'][] = array_pop($deck);
    }
}


if (isset($_POST['action'])) {
    if ($_POST['action'] === 'hit') {
        $_SESSION['player_hand'][] = array_pop($deck);

        if (calculateHandValue($_SESSION['player_hand']) > 21) {
            echo "<p>Bust! Dealer wins.</p>";
            unset($_SESSION['player_hand']);
            unset($_SESSION['dealer_hand']);
        }
    } elseif ($_POST['action'] === 'stand') {
        while (calculateHandValue($_SESSION['dealer_hand']) < 17) {
            $_SESSION['dealer_hand'][] = array_pop($deck);
        }

        $playerHandValue = calculateHandValue($_SESSION['player_hand']);
        $dealerHandValue = calculateHandValue($_SESSION['dealer_hand']);

        if ($dealerHandValue > 21 || $playerHandValue > $dealerHandValue) {
            echo "<p>You win!</p>";
        } elseif ($playerHandValue < $dealerHandValue) {
            echo "<p>Dealer wins.</p>";
        } else {
            echo "<p>It's a tie.</p>";
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
     <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #004225;
            text-align: center;
            margin-top: 8em;
        }

        h1 {
            color: black;
        }

        h2 {
            color: black;
        }

        p {
            margin: 0.5em 0;
            color: black;
            font-size: 2em;
        }

        .result {
            color: #d9534f;
            font-size: 1.2em;
        }

        form {
            margin-top: 1em;
        }

        input[type="submit"] {
            padding: 0.5em 1em;
            font-size: 1em;
            cursor: pointer;
            background-color: #618264;
            color: #fff;
            border: none;
            border-radius: 4px;
        }

        input[type="submit"]:hover {
            background-color: #B0D9B1;
        }
    </style>
</head>

<body>
    <h1>Blackjack</h1>

    <?php if (!isset($_SESSION['player_hand'])) : ?>
        <form method="post">
            <input type="submit" name="new_game" value="New Game">
        </form>
    <?php else : ?>
        <h2>Sua Mão:</h2>
        <?php foreach ($_SESSION['player_hand'] as $card) : ?>
            <p><?php echo $card['value'] . ' ' . $card['suit']; ?></p>
        <?php endforeach; ?>
        <p>Valor da sua mão: <?php echo calculateHandValue($_SESSION['player_hand']); ?></p>
        <h2>Mão do Crupiê:</h2>
        <?php
        $dealerFirstCard = reset($_SESSION['dealer_hand']);
        ?>
        <p><?php echo $dealerFirstCard['value'] . ' ' . $dealerFirstCard['suit']; ?></p>

        <form method="post">
            <input type="submit" name="action" value="hit">
            <input type="submit" name="action" value="stand">
        </form>
    <?php endif; ?>
</body>

</html>