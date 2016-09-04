<?php

require('vendor/autoload.php');

use Niisan\Gomoku\libs\Field;

const DIM = 6;

$game = new Field(DIM);

show($game);

while (1) {
    list($x, $y) = explode(' ', fgets(STDIN,4096));
    if (! $game->putStone($x, $y)) {
        echo $game->message . "\n";
    }
    show($game);
    if ($game->isEnd()) {
        echo $game->getWinner . "の勝利!!\n";
        $game->initialize();
        echo "エンターを押してください\n";
        fgets(STDIN,4096);
        show($game);
    }
    //print_r($game->getReward());
}

function show($game)
{
    $state = $game->getState();
    for ($i = 0; $i <= DIM; $i++) {
        echo $i . '  ';
    }
    echo PHP_EOL;

    for ($i = 1; $i <= DIM; $i++) {
        echo $i . '  ';
        for ($j = 1; $j <= DIM; $j++) {
            switch ($state[$i][$j]) {
                case Field::TURN_WHITE:
                    echo '◯';
                    break;
                case Field::TURN_BLACK:
                    echo '●';
                    break;
                default:
                    echo ' ';
            }
            echo '  ';
        }
        echo PHP_EOL;
    }
}
