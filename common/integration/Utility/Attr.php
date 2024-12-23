<?php

namespace common\integration\Utility;

use common\integration\Attributes\AttributeInterface;

class Attr
{
    public function handle($objectOrClass, array $closures = [])
    {
        try {
            if (!is_object($objectOrClass)) {
                $objectOrClass = new $objectOrClass();
            }

            $refC = new \ReflectionClass($objectOrClass);
            $props = $refC->getProperties();

            foreach ($props as $prop) {
                $refP = new \ReflectionProperty(get_class($objectOrClass), $prop->getName());
                $attrs = $refP->getAttributes();

                foreach ($attrs as $attr) {
                    $refA = new \ReflectionClass($attr->getName());
                    $attrObj = $refA->newInstanceArgs($attr->getArguments());

                    if ($attrObj instanceof AttributeInterface) {
                        $closures [] = $attrObj->handle();
                    }

                    foreach ($closures as $closure) {
                        $closure($objectOrClass, $prop, $attrObj);
                    }
                }
            }
        }catch (\Throwable $th){
            Exception::log($th, "ATTR_HANDLER");
        }
    }

}