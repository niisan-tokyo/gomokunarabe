<?php

require('vendor/autoload.php');

use Niisan\Gomoku\libs\Field;

$dimension = 7;

$field = new Field($dimension);

$white = new Niisan\Gomoku\libs\Agent();
$black = new Niisan\Gomoku\libs\Agent();

$display = new Niisan\Gomoku\libs\Display;
$display->dim = $dimension;

$dimm2 = $dimension * $dimension;
$wdir = __DIR__ . '/dest/white';
$bdir = __DIR__ . '/dest/black';
$white->init($dimm2, $dimm2);
$black->init($dimm2, $dimm2);

$white->loadModel($wdir, $dimension);
$black->loadModel($bdir, $dimension);

$display->show($field);
$your_turn = 1;
$obj = ($your_turn === 1) ? $black: $white;
while(1) {
    $state = $field->getState(true);
    $actions = $field->getProb();
    if ($field->getTurn() === $your_turn) {
        list($x, $y) = explode(' ', fgets(STDIN,4096));
        if (! $field->putStone($x, $y)) {
            echo $firld->message . "\n";
        }
    } else {
        $obj->input($state);
        $action = $obj->getAction($actions);
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
