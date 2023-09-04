<?php
    session_start();
    class Blackjack{
        private $deck;
        private $player_hand;
        private $dealer_hand;

        public function __construct()
        {
            $this->$deck = $this->generateDeck();
            $this->player_hand = [];
            $this->dealer_hand = [];
        }

        public function generateDeck(){
            $deck = [];
            $suits = ['Spades', 'Hearts', 'Diamonds', 'Clubs'];
            $values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

            foreach ($suits as $suit) {
                foreach ($values as $value) {
                    $deck[] = ['value' => $value, 'suit' => $suit];
                }
            }

            shuffle($deck);
            return $deck;
        }

        public function startGame(){
            if(!isset($_SESSION['player_hand']) || isset($_POST['new_game'])){
                $_SESSION['player_hand'] = [];
                $_SESSION['dealer_hand'] = [];

                for ($i=0; $i < 2; $i++) { 
                    $_SESSION['dealer_hand'][] = array_pop($this->deck);
                    $_SESSION['player_hand'][] = array_pop($this->deck);
                }
            }
        }

        public function play($action){
            if($action === 'hit'){
                $_SESSION['player_hand'][] = array_pop($this->deck);
                if($this->calcHandValue($_SESSION['player_hand']) > 21){
                    return "Você estourou! Crupiê Vence.";
                }
                elseif($action === 'stand'){
                    while($this->calcHandValue($_SESSION['dealer_hand']) < 17){
                        $_SESSION['dealer_hand'][] = array_pop($this->deck);
                    }

                    $player_hand_value = $this->calcHandValue($_SESSION['player_hand']);
                    $dealer_hand_value = $this->calcHandValue($_SESSION['dealer_hand']);


                    if($dealer_hand_value > 21 || $player_hand_value > $dealer_hand_value){
                        return "Você venceu!!";
                    }
                    elseif ($player_hand_value < $dealer_hand_value) {
                        return "Crupiê vence.";
                    } 
                    else {
                        return "Empate.";
                    }
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>QUEBRANDO A BANCA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Blackjack</h1>

    <?php if (!isset($_SESSION['mao_jogador'])): ?>
        <form method="post">
            <input type="submit" name="novo_jogo" value="Novo Jogo">
        </form>
    <?php else: ?>
        <h2>Sua Mão:</h2>
        <?php foreach ($_SESSION['mao_jogador'] as $carta): ?>
            <p><?php echo $carta['valor'] . ' de ' . $carta['naipe']; ?></p>
        <?php endforeach; ?>

        <p>Valor da sua mão: <?php echo calcularValorMao($_SESSION['mao_jogador']); ?></p>

        <form method="post">
            <input type="submit" name="acao" value="hit">
            <input type="submit" name="acao" value="stand">
        </form>
    <?php endif; ?>
</body>
</html>
