<?php

require('vendor/autoload.php');

use Niisan\Gomoku\libs\Field;

$dimmension = 7;

$field = new Field($dimmension);

$agent = new Niisan\Gomoku\libs\Agent();

$dimm2 = $dimmension * $dimmension;
$dir = __DIR__ . '/dest/agent';
$agent->init($dimm2 + 1, $dimm2);
for ($i = 1; $i < 50000; $i++) {
    $actions = $field->getProb();
    $keys = array_rand($actions, mt_rand(2, 7));
    foreach ($keys as $key) {
        $field->agentPut($actions[$key]);
    }

    while (true) {
        $state = $field->getState(true);
        $actions = $field->getProb();

        $action = $agent->train($state, $actions);
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
                $agent->setReward(2, -2);
                //echo "白の勝ち\n";
            } elseif ($winner == -1) {
                $agent->setReward(-2, 2);
                //echo "黒の勝ち\n";
            } else {
                $agent->setReward(1, 1.5);
                //echo "引き分け\n";
            }

            //show($field, $dimmension);
            //sleep(1);
            $agent->reset();
            $field->initialize();
            continue 2;
        }

        // $rewards = $field->getReward();
        // $reward = 0;
        // foreach($rewards as $val) {
        //     $reward += $val;
        // }
    }
}

$agent->saveModel($dir, $dimmension);


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
