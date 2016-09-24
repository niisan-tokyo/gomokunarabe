<?php

namespace Niisan\Gomoku\libs;

use Niisan\phpnn\layer\Relu;
use Niisan\phpnn\layer\Linear;
use Niisan\phpnn\layer\HyperbolicTangent;

class Agent
{

    private $input_dim;
    private $output_dim;
    private $layer = [];
    private $layer_dim = [
        1 => 32,
        2 => 64,
        3 => 128,
        4 => 64,
        5 => 32
    ];
    private $layer_count = 6;
    private $action_count = 7;
    private $pass_effect  = 0.9;

    private $value_function;
    private $action;

    // e-greedy
    private $max_epsilon = 200000;
    private $min_epsilon = 40000;
    private $epock = 0;
    private $epock_line = 50000;
    private $epsilon;

    // 報酬付与数
    private $reward_count = 1;

    // 状態の履歴
    private $states_history = [];

    public function init($input_dim, $output_dim)
    {
        $this->input_dim = $imput_dim;
        $this->output_dim = $output_dim;
        $this->layer_dim[0] = $input_dim;
        $this->layer_dim[$this->layer_count] = $output_dim;
        for ($i = 1; $i < $this->layer_count; $i++) {
            $obj = new Relu();
            $obj->init(
                $this->layer_dim[$i - 1],
                $this->layer_dim[$i],
                ['history_count' => $this->action_count, 'effect' => 0.04]
            );
            $this->layer[$i] = $obj;
        }
        $obj = new HyperbolicTangent();
        $obj->init(
            $this->layer_dim[$this->layer_count - 1],
            $this->layer_dim[$this->layer_count],
            ['history_count' => $this->action_count, 'effect' => 0.04, 'max' => 2]
        );
        $this->layer[$this->layer_count] = $obj;
        $this->epsilon = $this->max_epsilon + 1;
    }

    public function saveModel($dir, $dim)
    {
        foreach ($this->layer as $key => $obj) {
            $obj->save($this->getFilename($dir, $dim, $key));
        }
    }

    public function loadModel($dir, $dim)
    {
        foreach ($this->layer as $key => $obj) {
            $obj->load($this->getFilename($dir, $dim, $key));
        }
    }

    public function input($state)
    {
        $ret = $state;
        for ($i = 1; $i <= $this->layer_count; $i++) {
            $ret = $this->layer[$i]->prop($ret);
        }

        $this->value_function = $ret;
        return $ret;
        //print_r($ret);
        //exit;
    }

    public function train($states, $actions)
    {
        $this->setStates($states);
        if (mt_rand(1, $this->max_epsilon) < $this->epsilon) {
            $key = array_rand($actions);
            $action = $actions[$key];
        } else {
            //echo "greedy!!\n";
            $action = $this->getAction($states, $actions);
            //print_r($this->value_function);
            //exit;
        }

        if ($this->epock > $this->epock_line and $this->epsilon > $this->min_epsilon) {
            $this->epsilon--;
        }

        $this->setAction($action);
        $this->epock++;

        return $action;
    }

    public function getAction($states, $actions)
    {
        $test = null;
        $action = 0;
        $this->input($states);
        foreach ($actions as $val) {
            if ($test === null or $test < $this->value_function[$val]) {
                $test = $this->value_function[$val];
                $action = $val;
            }
        }

        return $action;
    }


    public function setReward($reward)
    {
        //echo "reward: $reward \n";
        $loss_diff = array_fill(0, $this->output_dim, 0);

        $action_count = count($this->action);
        $action = $this->action[$action_count - 1];
        $value = $this->value_function[$action];
        //echo "$value \n";
        if ($this->reward_count % 50 == 0) {
            $loss = ($value - $reward) * ($value - $reward) / 2;
            echo "epock: $this->epock , loss: $loss \n";
        }
        $loss_diff_val = $value - $reward;
        $loss_diff[$action] = $loss_diff_val;
        for ($i = 0; $i < $action_count; $i++) {
            $this->input($this->states_history[$action_count - $i - 1]);
            $res = $loss_diff;
            for ($j = $this->layer_count; $j > 0; $j--) {
                $res = $this->layer[$j]->backProp($res);
            }

            $action = $this->action[$action_count - $i - 2];
            $loss_diff = array_fill(0, $this->output_dim, 0);
            $loss_diff_val = $this->pass_effect * $loss_diff_val;
            $loss_diff[$action] = $loss_diff_val;
        }
        $this->reward_count++;
    }

    public function reset()
    {
        $this->action = [];
        $this->states_history = [];
        foreach ($this->layer as $val) {
            $val->reset();
        }
    }


    private function setAction($action)
    {
        $this->action[] = $action;
        if (count($this->action) > $this->action_count) {
            array_shift($this->action);
        }
    }

    private function setStates($states)
    {
        $this->states_history[] = $states;
        if (count($this->states_history) > $this->action_count) {
            array_shift($this->states_history);
        }
    }

    private function getFilename($dir, $dim, $layer)
    {
        $file = 'bdim_' . $dim . '_layer_' . $layer . '.txt';
        return $dir . '/' . $file;
    }

}
