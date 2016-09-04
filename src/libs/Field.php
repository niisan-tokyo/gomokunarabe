<?php

namespace Niisan\Gomoku\libs;

class Field
{

    const TURN_WHITE = 1;
    const TURN_BLACK = -1;

    const WIN = 5;

    private $dimmension;
    private $turn;
    private $state;
    private $reward = [];
    private $winner = 0;
    private $count = 0;
    private $max;
    public  $message;

    public function __construct($dimmension = 7)
    {
        $this->dimmension = $dimmension;
        $this->max = $dimmension * $dimmension;
        //echo $this->max . "\n";
        $this->turn = self::TURN_WHITE;
        $this->setInitialState();
    }

    public function getTurn()
    {
        return $this->turn;
    }

    public function putStone($x, $y)
    {
        if (! $this->between($x, $y)) {
            $this->message = 'x, yが入力できない範囲にある';
            return false;
        }

        if ($this->isEnd()) {
            $this->message = '勝負が決まっている';
            return false;
        }

        $index = $this->transIndex($x, $y);
        if ($this->state[$index] === 0) {
            $this->state[$index] = $this->turn;
            $this->updateState($x, $y);
            $this->changeTurn();
            $this->count++;
            //echo $this->count . "\n";
            return true;
        }

        $this->message = '既に石が置かれている';

        return false;
    }

    public function isEnd()
    {
        return ($this->winner === 0 and $this->count < $this->max) ? false : true;
    }

    public function getWinner()
    {
        return $this->winner;
    }

    public function getState($raw = false)
    {
        if ($raw === true) {
            $this->state[0] = $this->turn;
            return $this->state;
        }

        $ret = [];
        $y = 1;
        $x = 1;
        $ind = 1;
        while (isset($this->state[$ind])) {
            $ret[$y][$x] = $this->state[$ind];
            $ind++;
            $x++;
            if ($x > $this->dimmension) {
                $x =1;
                $y++;
            }
        }

        return $ret;
    }

    public function getProb()
    {
        $ret = [];
        foreach ($this->state as $key => $val) {
            if ($val == 0 and $key != 0) {
                $ret[] = $key;
            }
        }

        return $ret;
    }

    public function agentPut($index)
    {
        $vec_x = $index % $this->dimmension;
        if ($vec_x == 0) {
            $vec_x = $this->dimmension;
        }
        $vec_y = ($index - $vec_x) / $this->dimmension + 1;

        //echo "$index : $vec_x , $vec_y \n";

        return $this->putStone($vec_x, $vec_y);
    }


    public function getReward()
    {
        return $this->reward;
    }

    public function initialize()
    {
        $this->winner = 0;
        $this->message = '';
        $this->turn = self::TURN_WHITE;
        $this->setInitialState();
        $this->count = 0;
    }


    private function setInitialState()
    {
        $this->state[0] = $this->turn;
        for ($i = 0; $i < $this->dimmension; $i++) {
            for ($j = 1; $j <= $this->dimmension; $j++) {
                $this->state[$i * $this->dimmension + $j] = 0;
            }
        }
    }

    private function between($x, $y)
    {
        if ($x < 1 or $x > $this->dimmension) {
            return false;
        }

        if ($y < 1 or $y > $this->dimmension) {
            return false;
        }

        return true;
    }

    private function transIndex($vecx, $vecy)
    {
        return ($vecy - 1) * $this->dimmension + $vecx;

    }

    private function changeTurn()
    {
        $this->turn = ($this->turn === self::TURN_WHITE)? self::TURN_BLACK : self::TURN_WHITE;

    }

    private function updateState($x, $y)
    {
        $this->reward = [];
        $vector = [
            [0, 1], [1, 0], [1, 1], [1, -1]
        ];

        $position = [$x, $y];
        foreach ($vector as $vec) {
            $count = 1;
            $count += $this->checkLine($position, $vec);
            $count += $this->checkLine($position, $vec, true);
            if ($count > 2) {
                $this->reward[] = $count;
            }

            if ($count >= self::WIN) {
                $this->winner = $this->turn;
            }
        }
    }

    private function checkLine($position, $vector, $isInverse = false)
    {
        $count = 0;
        $vec = $position;
        while (1) {
            $vec[0] += ($isInverse) ? - $vector[0]: $vector[0];
            $vec[1] += ($isInverse) ? - $vector[1]: $vector[1];

            list($x, $y) = $vec;
            $index = $this->transIndex($x, $y);
            if ($this->between($x, $y) and $this->state[$index] == $this->turn) {
                $count++;
            } else {
                return $count;
            }
        }
    }

}
