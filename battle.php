<?php

require('vendor/autoload.php');

use Niisan\Gomoku\libs\Field;

$dimension = 7;

$field = new Field($dimension);

$agent = new Niisan\Gomoku\libs\Agent();

$display = new Niisan\Gomoku\libs\Display;
$display->dim = $dimension;

$dimm2 = $dimension * $dimension;
$dir = __DIR__ . '/dest/agent';
$agent->init($dimm2 + 1, $dimm2);

$agent->loadModel($dir, $dimension);

$display->show($field);
while(1) {
    $state = $field->getState(true);
    $actions = $field->getProb();

    $action = $agent->getAction($state, $actions);
    $field->agentPut($action);

    $display->show($field);
    sleep(1);

    if ($field->isEnd()) {
        $winner = $field->getWinner();
        if ($winner == 1) {
            echo "白の勝ち\n";
        } elseif ($winner == -1) {
            echo "黒の勝ち\n";
        } else {
            echo "引き分け\n";
        }
        break;
    }
}
