<?php

require('vendor/autoload.php');

use Niisan\Gomoku\libs\Field;

$dimension = 7;

$field = new Field($dimension);

$obj = new Niisan\Gomoku\libs\Agent();

$display = new Niisan\Gomoku\libs\Display;
$display->dim = $dimension;

$dimm2 = $dimension * $dimension;
$dir = __DIR__ . '/dest/agent';
$obj->init($dimm2 + 1, $dimm2);

$obj->loadModel($dir, $dimension);

$display->show($field);
$your_turn = -1;

while(1) {
    $state = $field->getState(true);
    $actions = $field->getProb();
    if ($field->getTurn() === $your_turn) {
        list($x, $y) = explode(' ', fgets(STDIN,4096));
        if (! $field->putStone($x, $y)) {
            echo $firld->message . "\n";
        }
    } else {
        $action = $obj->getAction($state, $actions);
        $field->agentPut($action);
    }

    $display->show($field);
    if ($field->isEnd()) {
        echo $field->getWinner . "の勝利!!\n";
        $field->initialize();
        echo "エンターを押してください\n";
        fgets(STDIN,4096);
        $display->show($field);
    }
}
