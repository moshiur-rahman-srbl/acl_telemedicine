<?php

namespace common\integration\Utility;

class StreamFilter extends \php_user_filter
{
    const NAME = 'php-stream-filter';

    protected $buffer = '';

    public static $registered = [];

    public static function append($resource, string $path)
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        stream_filter_append(
            $resource,
            self::NAME,
            STREAM_FILTER_READ,
            [
                'ext' => $ext,
                'path' => $path,
            ]
        );
    }

    public static function register()
    {
        stream_filter_register(self::NAME, static::class);
    }

    public function filter($in, $out, &$consumed, bool $closing): int
    {

        while ($bucket = stream_bucket_make_writeable($in)) {
            $this->buffer .= $bucket->data;
            $consumed += $bucket->datalen;
        }
        if ($closing) {
            $buffer = $this->inject($this->buffer, $this->params['path'], $this->params['ext']);
            $bucket = stream_bucket_new($this->stream, $buffer);
            stream_bucket_append($out, $bucket);
        }


        return PSFS_PASS_ON;
    }

    private function inject($buffer, $path, $ext): string
    {
        if ('php' !== $ext) {
            return $buffer;
        }


        if (0 !== strpos($buffer, "<?php\n")) {
            return $buffer;
        }



        if (str_contains($path, "vendor")) {
            return $buffer;
        }

        $buffer = str_replace("<?php\n", "<?php\ndeclare(ticks=1);\n", $buffer);


        return $buffer;
    }
}

?>
