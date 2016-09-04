<?php

require('vendor/autoload.php');

use Niisan\Gomoku\libs\Field;

$dimmension = 7;

$field = new Field($dimmension);

$white = new Niisan\Gomoku\libs\Agent();
$black = new Niisan\Gomoku\libs\Agent();

$display = new Niisan\Gomoku\libs\Display;
$display->dim = $dimmension;

$dimm2 = $dimmension * $dimmension;
$wdir = __DIR__ . '/dest/white';
$bdir = __DIR__ . '/dest/black';
$white->init($dimm2, $dimm2);
$black->init($dimm2, $dimm2);

$white->loadModel($wdir, $dimmension);
$black->loadModel($bdir, $dimmension);

$display->show($field);
while(1) {
    $state = $field->getState(true);
    $actions = $field->getProb();
    if ($field->getTurn() === 1) {
        $obj = $white;
    } else {
        $obj = $black;
    }

    $obj->input($state);
    $action = $obj->getAction($actions);
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
