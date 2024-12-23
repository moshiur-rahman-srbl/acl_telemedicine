<?php

namespace common\integration\Utility;

const STREAM_OPEN_FOR_INCLUDE = 128;

//example: Debugger::register([Debugger::class,'execFlow'], storage_path("logs/execFlow.log"), false);

final class Debugger
{
    private static $counter = 0;
    private const output = 'php://stdout';
    public static $nbf;
    public static function register(callable $callback, mixed ...$args)
    {
        try {
            register_tick_function($callback, ...$args);
            StreamFilter::register();
            StreamWrapper::register();
        }catch (\Throwable $throwable){
            Exception::log($throwable, "CORE_DEBUG");
            throw $throwable;

        }
    }


    public static function write($output, $traceAll, $file, $line, $code)
    {

        $code = Str::whiteTrim($code);
        if (!$traceAll) {
            $avoidableCodeContains = ["register_tick_function", "declare(ticks=1)", "use "];
            $avoidableCode = [" ", "\n", "\t\n", "{", "}", ")}", ""];
            $avoidableFileContains = ["vendor"];

            if (Str::contains($code, $avoidableCodeContains ?? []) ||
                Arr::isAMemberOf($code, $avoidableCode ?? []) ||
                Str::contains($file, $avoidableFileContains ?? [])) {

                return;
            }
        }

        if (self::$counter == 0 && $output != self::output) {
            if (file_exists($output))
                @unlink($output);
        }

        $info = sprintf(++self::$counter . "|%s:%d|%d|%.4f|%.4fMB|%s\n",
             $file, $line, getmypid(), microtime(), memory_get_usage() / 1024 / 1024, $code);

        file_put_contents($output, $info, FILE_APPEND);
    }

    public static function execFlow($output = self::output, $traceAll = false)
    {
        $backtrace = debug_backtrace();;
        $line = $backtrace[0]['line']-1;
        $file = $backtrace[0]['file'];


          if ($file == __FILE__) return;

        static $fp, $cur, $buf;
        if (!isset($fp[$file])) {
            $stream = fopen($file, 'r');
            $fp[$file] = $stream;
            $cur[$file] = 0;
        }

        if (isset($buf[$file][$line])) {
            $code = $buf[$file][$line];

        } else {
            do {
                $code = fgets($fp[$file]);
                $buf[$file][$cur[$file]] = $code;
            } while (++$cur[$file] <= $line);
        }
        $line++;

        self::write($output, $traceAll, $file, $line, $code);

    }
}

