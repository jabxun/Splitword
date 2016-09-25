<?php
namespace Split\Stream;

use Split\Stream\Stream;

class FileStream extends Stream
{
    public static function open($filename, $mode, $use_include_path = false, $context = null)
    {
        $type = gettype($context);

        if($type == 'resource') {
            return new self(fopen($filename, $mode, $use_include_path, $context));
        }

        return new static(fopen($filename, $mode, $use_include_path));
    }
}