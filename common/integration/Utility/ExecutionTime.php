<?php

namespace common\integration\Utility;

use phpseclib3\Crypt\EC\Curves\secp112r1;

class ExecutionTime
{
    private $startTime;

    private $mark;

    private $endTime;

    private $start;

    /** @var ExecutionTime[] $children */
    private $children = [];

    private $end;
    /** @var ExecutionTime[] $instances */

    private static $instances = [];

    private static $counters = [];

    private $result = [];

    public static function start($mark = null, mixed $loopMarker = "", $parent = null) : self
    {
        $instance = new self();

        $counter = self::$counters[$mark] ?? 1;

        if(is_bool($loopMarker) && $loopMarker == true){
            self::$counters[$mark] = $counter + 1;
            $mark = $mark."_".$counter;

        }else if (is_string($loopMarker)){
            if(!empty($loopMarker)){
                $mark = $mark."_".$loopMarker;
            }
        }

        $instance->mark = $mark;

        $instance->startTime = microtime(true);

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $instance->start = $backtrace[0]['file']. ":".$backtrace[0]['line'];

        if($parent == null) {
            self::$instances[] = $instance;
        }

        return $instance;
    }

    public function end(): self
    {
        $this->endTime = microtime(true);

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $this->end = $backtrace[0]['file']. ":".$backtrace[0]['line'];

        return $this;
    }

    public function executionTime()
    {
        return  ($this->endTime - $this->startTime)*1000;
    }


    public static function show($dd = false)
    {
        $self = new self();
        foreach (self::$instances as $instance) {
             $self->result($instance);
        }

        if($dd){
            dd($self->result);
        }

        return $self->result;

    }

    private function result(ExecutionTime $instance, ExecutionTime $parent = null)
    {
        $result = [
            "from" => $instance->start,
            "to" => $instance->end,
            "time" => $instance->executionTime()." milliseconds"
        ];

        if($instance->hasChildren()) {
            $instance->result["parent"] = $result;
        }else{
            $instance->result[] = $result;
        }

        if($instance->hasChildren()){
            foreach ($instance->children as $child){
                $this->result($child, $instance);
            }
        }

        if($instance->mark != null) {
            if ($parent == null) {
                $this->result[$instance->mark] = $instance->result;
            } else {
                $parent->result["children"][$instance->mark] = $instance->result;
            }
        }
        else {
            if($parent == null) {
                $this->result[] = $instance->result;
            }else{
                $parent->result["children"][] = $instance->result;
            }
        }

        return $instance->result;
    }

    private function hasChildren()
    {
        return !empty($this->children);
    }

    public static function refresh()
    {
        self::$instances = [];
        self::$counters = [];
    }

    public function child($mark = null, mixed $loopMarker = "") : self
    {
        $child =  self::start($mark, $loopMarker, $this);
        $this->children [] = $child;
        return $child;
    }


}