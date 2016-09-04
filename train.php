<?php

require('vendor/autoload.php');

use Niisan\Gomoku\libs\Field;

$dimmension = 7;

$field = new Field($dimmension);

$white = new Niisan\Gomoku\libs\Agent();
$black = new Niisan\Gomoku\libs\Agent();

$dimm2 = $dimmension * $dimmension;
$wdir = __DIR__ . '/dest/white';
$bdir = __DIR__ . '/dest/black';
$white->init($dimm2 + 1, $dimm2);
$black->init($dimm2 + 1, $dimm2);
for ($i = 1; $i < 20000; $i++) {
    $state = $field->getState(true);
    $actions = $field->getProb();
    if ($field->getTurn() === 1) {
        $obj = $white;
    } else {
        $obj = $black;
    }

    $obj->input($state);
    $action = $obj->train($actions);
    $field->agentPut($action);
    if (!empty($field->message)) {
        print_r($field->message);
        print_r($actions);
        print_r($action);
        print_r($field->getState());
        exit;
    }

    if ($field->isEnd()) {
        $winner = $field->getWinner();
        if ($winner == 1) {
            $white->setReward(2);
            $black->setReward(-2);
            echo "白の勝ち\n";
        } elseif ($winner == -1) {
            $white->setReward(-2);
            $black->setReward(2);
            echo "黒の勝ち\n";
        } else {
            $white->setReward(1);
            $black->setReward(1);
            echo "引き分け\n";
        }

        //show($field, $dimmension);
        //sleep(1);
        $white->reset();
        $black->reset();
        $field->initialize();
        continue;
    }

    // $rewards = $field->getReward();
    // $reward = 0;
    // foreach($rewards as $val) {
    //     $reward += $val;
    // }

    if ($reward > 0) {
        $obj->setReward($reward);
    }
}

$white->saveModel($wdir, $dimmension);
$black->saveModel($bdir, $dimmension);


function show($game, $dim)
{
    $state = $game->getState();
    for ($i = 0; $i <= $dim; $i++) {
        echo $i . '  ';
    }
    echo PHP_EOL;

    for ($i = 1; $i <= $dim; $i++) {
        echo $i . '  ';
        for ($j = 1; $j <= $dim; $j++) {
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
