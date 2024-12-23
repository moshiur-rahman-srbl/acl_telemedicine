<?php

namespace common\integration\Utility;

class Functionality
{
    public function isMethodDefined($class_name, $method_name)
    {
        $reflectionClass = new \ReflectionClass(new $class_name);
        $method = $reflectionClass->getMethod($method_name);
        if ($method->class == $class_name) {
            return true;
        }
        return false;
    }

    public static function isCallableFunction($callback_function){
        return is_callable($callback_function);
    }

}