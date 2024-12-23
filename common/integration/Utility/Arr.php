<?php

namespace common\integration\Utility;

use App\Imports\ExcelFileImport;
use common\integration\Utility\Str;
use Maatwebsite\Excel\Facades\Excel;

class Arr
{

    public static function indexToAssoc($array, $delimiter):array
    {
        $associative_array = [];
        foreach ($array as $value){
            $key_value_array = Str::toArr($value, $delimiter);
            $key = $key_value_array[0]??"";
            $value = $key_value_array[1]??"";
            $associative_array[$key] = $value;
        }

        return $associative_array;

    }

    public static function merge(array ...$arrays):array
    {
        return array_merge(...$arrays);

    }

    public static function unset(array $data, array $keys)
    {
        foreach ($keys as $key => $value) {
             unset($data[$value]);
        }
        return $data;
    }

    public static function walkRecursive(&$arr, Callable $callback){
        foreach($arr as $k => &$v){
            if(is_array($v) && !empty($v)){
                 self::walkRecursive($v, $callback);
            }else{
                call_user_func_array($callback,array($k, &$v));
            }
        }
        return $arr;
    }

    public static function first($arr)
    {
        return \Illuminate\Support\Arr::first($arr);
    }

    public static function slice($arr, $offset, $length = null, $preserve_keys = false)
    {
        return array_slice($arr, $offset, $length, $preserve_keys);
    }

    public static function unique($arr)
    {
        return array_unique($arr);
    }

    public static function keys($arr, $filter_value = false, $strict = false)
    {
        if(is_bool($filter_value) && !$filter_value ){
            return array_keys($arr);
        }
       return array_keys($arr, $filter_value, $strict);
    }

    public static function values($arr)
    {
        return array_values($arr);
    }

    public static function unsetRecursive(&$arr, $remove){
        $remove = (array)$remove;
        foreach ($arr as $key => &$value) {
            if (in_array($value, $remove)) {
                unset($arr[$key]);
            } elseif (is_array($value)) {
                self::unsetRecursive($value, $remove);
            }
        }
        return $arr;
    }

    public static function filter($arr, $reIndex = false, $callback = null, $array_filter_use = ARRAY_FILTER_USE_BOTH)
    {
        if (is_array($arr)) {
            $arr = array_filter($arr, $callback, $array_filter_use);
            if ($reIndex) {
                $arr = array_values(array_filter($arr));
            }

            return $arr;
        } else {
            return $arr;
        }
    }

    public static function last($arr)
    {
        if (is_array($arr)) {
            return $arr[array_key_last($arr)];
        } else {
            return null;
        }
    }


    public static function isIdentical($expected, $actual)
    {
        self::recurSort( $expected );
        self::recurSort( $actual );

        return $expected === $actual;
    }

    public static function isAMemberOf(mixed $string, $array, bool $strict= false): bool
    {
       return in_array($string, $array, $strict);
    }

    public static function keyLast(array $array)
    {
        return array_key_last($array);
    }

    public static function keyVals($arr_of_arrs)
    {
        $key_vals = [];
        foreach($arr_of_arrs as $arr){
            foreach($arr as $k => $v){
                $key_vals[$k][] = $v;
            }
        }

        return $key_vals;

    }

    public static function recurSort( &$arr ) {
        foreach ($arr as &$value ) {
            if ( is_array( $value ) ) self::recurSort( $value );
        }

        if ( self::isSequential( $arr ) ) {
            $arr = array_map( function($el ) { return json_encode( $el ); }, $arr  );
            sort( $arr, SORT_STRING );
            $arr = array_map( function($el ) { return json_decode( $el, true ); }, $arr  );
            return;
        } else {
            return ksort( $arr );
        }
    }

    public static function isSequential(&$arr) {
        $n = count($arr);
        for($i=0; $i<$n; $i++) {
            if(!array_key_exists($i, $arr)) {
                return false;
            }
        }
        return true;
    }

    public static function isOfType($array):bool
    {
        return is_array($array);
    }

    public static function count($array)
    {
        return count($array);
    }

    public static function filterByKeys($array, $keys){
        return array_intersect_key($array, array_flip((array) $keys));
    }


    public static function keyExists(int|string $key, array $value): bool
    {
        return array_key_exists($key, $value);
    }
    public static function fromExcel ($file) {
        $import = new ExcelFileImport();
        Excel::import($import, $file);
        $totalRows = $import->getRowCount();
        $excelArray = Excel::toArray(new ExcelFileImport(), $file);
        return [$totalRows, $excelArray];
    }

    public static function push(array &$array, $values) {
        return array_push($array, $values);
    }

    public static function implode($separator, $arr)
    {
        return implode($separator, $arr);
    }
    public static function explode($separator, $arr)
    {
        return explode($separator, $arr);
    }

    public static function walk(&$array, callable $callback, $arg = null)
    {
        return array_walk($array, $callback, $arg);
    }

    public static function search($needle, $haystack, $strict = false)
    {
        return array_search($needle, $haystack, $strict);
    }
    
    public static function map($callback,$data){
        return array_map($callback, $data);
    }

    public static function combine($keys, $values){
        return array_combine($keys, $values);
    }
    
    public static function natcasesort(&$arr){
        return natcasesort($arr);
    }

    public function combinations($arr, $i = 0) {
        $results = array(array( ));

        foreach ($arr as $element)
            foreach ($results as $combination)
                array_push($results, array_merge(array($element), $combination));

        return $results;
    }
	
	public static function intersect($arr1, $arr2) {
		
		return array_intersect($arr1,$arr2);
	}

	public static function intersectKeys ($arr1, $arr2)
    {
		return array_intersect_key($arr1, $arr2);
	}

    public static function keyBy($arr , $key_by){
        return \Illuminate\Support\Arr::keyBy($arr, $key_by);
    }

    public static function chunk($arr , $chunkValue){
        return array_chunk($arr, $chunkValue);
    }
	
	public static function getHeaderFormArrayList($files_data){
	
		return  array_keys(call_user_func_array('array_merge', $files_data));
		
	}

    public static function column($arr , $column_key = null, $index_key = null){
        return array_column($arr, $column_key, $index_key);
    }

    public static function get($array, $key, $default = null){
        return \Illuminate\Support\Arr::get($array, $key, $default);
    }

    public static function arrayDiffKey($arr, $filter)
    {
        return array_diff_key($arr, $filter);
    }

    public static function diff($arr, $filter)
    {
        return array_diff($arr, $filter);
    }

	public static function arrayFlip($array)
    {
        return array_flip($array);
    }
    
    public static function changeKeyCase(array $array, int $case_type = CASE_UPPER)
    {
        return array_change_key_case($array, $case_type);
    }

    public static function objectToArray( object $object) : array
    {

        $object = get_object_vars($object);

        return self::map(function ($x){
            if (is_object($x)) {
                $x = get_object_vars($x);
            }
            return $x;
        }, $object);

    }
    public static function appendFirst(array $array, $value): array{
        array_unshift($array, $value);
        return $array;
    }

    public static function isValueUnique(array $value): bool
    {
        if(empty($value)) return false;
        $values = array_values($value);
        return self::count($values) == self::count(array_unique($values));
    }

    public static function splice(array $original, int $push_index, int $length = 0, mixed $push_array = [])
    {
        array_splice($original, $push_index, $length, $push_array);
        return $original;
    }

    public static function getKey(array $array, string $match, bool $strict=true)
    {
        foreach ($array as $key => $value) {
            if($strict == false) {
                $match = Str::toLower($match);
                $value = Str::toLower($value);
            }
            if ($match === $value) {
                return $key;
            }
        }
        return null;
    }

    public static function asort(array $array) :array {
        asort($array);
        return $array;
    }

    public static function end(array $array) :array{
        return end($array);
    }
	
	public static function replace($base, $replacements, $replacements2 = [])
	{
		return array_replace($base, $replacements, $replacements2);
	}
	
	public static function fillKeys(array $keys, mixed $value){
		return array_fill_keys($keys, $value);
	}
	
	public static function unsetByValue(array $data, array $unset_data)
	{
		
		foreach ($unset_data as $value){
			foreach (self::keys($data, $value) as $key) {
				$data = self::unset($data, [$key]);
			}
		}
		
		return $data;

	}
	
	public static function searchAndReplace(array $data, $find, $replacement)
	{
		return self::replace($data,
			self::fillKeys(
				self::keys($data, $find),
				$replacement
			)
		);
	}

    public static function firstCharacterUpper($array,$multidimensional=true): array
    {

        $newArray = [];
        foreach ($array as $key => $value) {
            $newKey = Str::ucFirst($key);
            if (self::isOfType($value) && $multidimensional) {
                $newArray[$newKey] = self::firstCharacterUpper($value);
            } else {
                $newArray[$newKey] = $value;
            }
        }
        return $newArray;
    }

    public static function range($start, $end)
    {
        return range($start, $end);
    }
    public static function pluck($array, $value, $key = null) {
        return array_pluck($array, $value, $key);
    }

}