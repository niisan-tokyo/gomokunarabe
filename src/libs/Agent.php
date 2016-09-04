<?php

namespace Niisan\Gomoku\libs;

use Niisan\phpnn\layer\Relu;
use Niisan\phpnn\layer\Linear;

class Agent
{

    private $input_dim;
    private $output_dim;
    private $layer = [];
    private $layer_dim = [
        1 => 64,
        2 => 64,
        3 => 64
    ];
    private $layer_count = 3;
    private $action_count = 10;
    private $pass_effect  = 0.9;

    private $value_function;
    private $action;

    // e-greedy
    private $max_epsilon = 10000;
    private $min_epsilon = 5000;
    private $epock = 0;
    private $epock_line = 1000;
    private $epsilon;

    public function init($input_dim, $output_dim)
    {
        $this->layer_dim[0] = $input_dim;
        $this->layer_dim[$this->layer_count] = $output_dim;
        for ($i = 1; $i < $this->layer_count; $i++) {
            $obj = new Relu();
            $obj->init($this->layer_dim[$i - 1], $this->layer_dim[$i]);
            $this->layer[$i] = $obj;
        }
        $obj = new Linear();
        $obj->init($this->layer_dim[$this->layer_count - 1], $this->layer_dim[$this->layer_count]);
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
        //print_r($ret);
        //exit;
    }

    public function train($actions)
    {
        if (mt_rand(1, $this->max_epsilon) < $this->epsilon) {
            $key = array_rand($actions);
            $action = $actions[$key];
        } else {
            //echo "greedy!!\n";
            $action = $this->getAction($actions);
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

    public function getAction($actions)
    {
        $test = null;
        $action = 0;
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
        $los_diff = array_fill(0, $this->output_dim, 0);
        $action_count = count($this->action);
        $action = $this->action[$action_count - 1];
        $value = $this->value_function[$action];
        //echo "$value \n";
        if (mt_rand(1, 100) == 1) {
            $loss = ($value - $reward) * ($value - $reward) / 2;
            echo "loss: $loss \n";
        }
        $loss_diff_val = $value - $reward;
        $loss_diff[$action] = $loss_diff_val;
        for ($i = 0; $i < $action_count; $i++) {
            $res = $loss_diff;
            for ($j = $this->layer_count; $j > 0; $j--) {
                $res = $this->layer[$j]->backProp($res, $i);
            }

            $action = $this->action[$action_count - $i - 1];
            $loss_diff = array_fill(0, $this->output_dim, 0);
            $loss_diff_val = $this->pass_effect * $loss_diff_val;
            $loss_diff[$action] = $loss_diff_val;
        }
    }

    public function reset()
    {
        $this->action = [];
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

    private function getFilename($dir, $dim, $layer)
    {
        $file = 'adim_' . $dim . '_layer_' . $layer . '.txt';
        return $dir . '/' . $file;
    }

}
