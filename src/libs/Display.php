<?php

namespace Niisan\Gomoku\libs;

class Display
{

    public $dim = 5;

    public function show($game)
    {
        $state = $game->getState();
        for ($i = 0; $i <= $this->dim; $i++) {
            echo $i . '  ';
        }
        echo PHP_EOL;

        for ($i = 1; $i <= $this->dim; $i++) {
            echo $i . '  ';
            for ($j = 1; $j <= $this->dim; $j++) {
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
}
