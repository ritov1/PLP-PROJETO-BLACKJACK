<?php
session_start();

// Função para calcular o valor de uma mão de blackjack
function calcularValorMao($mao) {
    $valor = 0;
    $numAses = 0;
    
    foreach ($mao as $carta) {
        if ($carta['valor'] === 'A') {
            $numAses++;
        } elseif (is_numeric($carta['valor'])) {
            $valor += $carta['valor'];
        } else {
            $valor += 10; // Cartas de figura valem 10 pontos
        }
    }
    
    // Trata os Ases para obter o melhor valor possível
    for ($i = 0; $i < $numAses; $i++) {
        if ($valor + 11 <= 21) {
            $valor += 11;
        } else {
            $valor += 1;
        }
    }
    
    return $valor;
}

// Cria um baralho de cartas
$baralho = [];
$naipes = ['Espadas', 'Copas', 'Ouros', 'Paus'];
$valores = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

foreach ($naipes as $naipe) {
    foreach ($valores as $valor) {
        $baralho[] = ['valor' => $valor, 'naipe' => $naipe];
    }
}

shuffle($baralho);

// Inicia uma nova mão
if (!isset($_SESSION['mao_jogador']) || isset($_POST['novo_jogo'])) {
    $_SESSION['mao_jogador'] = [];
    $_SESSION['mao_crupie'] = [];
    
    // Distribui duas cartas para o jogador e uma para o crupiê
    for ($i = 0; $i < 2; $i++) {
        $_SESSION['mao_jogador'][] = array_pop($baralho);
        $_SESSION['mao_crupie'][] = array_pop($baralho);
    }
}

// Lógica do jogo
if (isset($_POST['acao'])) {
    if ($_POST['acao'] === 'hit') {
        $_SESSION['mao_jogador'][] = array_pop($baralho);
        
        // Verifica se o jogador estourou (passou de 21)
        if (calcularValorMao($_SESSION['mao_jogador']) > 21) {
            echo "Você estourou! Crupiê vence.";
            unset($_SESSION['mao_jogador']);
            unset($_SESSION['mao_crupie']);
        }
    } elseif ($_POST['acao'] === 'stand') {
        // Crupiê joga automaticamente até ter pelo menos 17 pontos
        while (calcularValorMao($_SESSION['mao_crupie']) < 17) {
            $_SESSION['mao_crupie'][] = array_pop($baralho);
        }
        
        $valorMaoJogador = calcularValorMao($_SESSION['mao_jogador']);
        $valorMaoCrupie = calcularValorMao($_SESSION['mao_crupie']);
        
        // Verifica quem venceu
        if ($valorMaoCrupie > 21 || $valorMaoJogador > $valorMaoCrupie) {
            echo "Você venceu!";
        } elseif ($valorMaoJogador < $valorMaoCrupie) {
            echo "Crupiê vence.";
        } else {
            echo "Empate.";
        }
        
        unset($_SESSION['mao_jogador']);
        unset($_SESSION['mao_crupie']);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Blackjack</title>
    <link rel="stylesheet" type="text/css" href="style.css">
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
