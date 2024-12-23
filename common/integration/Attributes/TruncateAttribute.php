<?php

namespace common\integration\Attributes;
use common\integration\Utility\Str;
use PhpParser\Node\Attribute;

#[Attribute(\Attribute::TARGET_PROPERTY)]

class TruncateAttribute implements AttributeInterface
{
    private int $length;
    public function __construct(int $length) {
        $this->length = $length;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function handle() {
        return function($obj, $prop, $attrObj) {
            if ($attrObj instanceof TruncateAttribute) {
                $name = $prop->getName();
                $obj->{$name} = Str::truncate($obj->{$name}, $attrObj->getLength());
            }
        };
    }


}