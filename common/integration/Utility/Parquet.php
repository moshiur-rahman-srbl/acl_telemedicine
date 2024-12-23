<?php

namespace common\integration\Utility;

use codename\parquet\data\DataColumn;
use codename\parquet\data\DataField;
use codename\parquet\data\Schema;
use codename\parquet\ParquetReader;
use codename\parquet\ParquetWriter;
use common\integration\ManipulateDate;

class Parquet
{
    public static function read($file = __DIR__.'/test.parquet')
    {
        $fileStream = fopen($file, 'r');

        $parquetReader = new ParquetReader($fileStream);

        $dataFields = $parquetReader->schema->GetDataFields();

        for($i = 0; $i < $parquetReader->getRowGroupCount(); $i++) {
            $groupReader = $parquetReader->OpenRowGroupReader($i);
            $columns = [];
            foreach ($dataFields as $field) {
                $columns[] = $groupReader->ReadColumn($field);
            }

            foreach($columns as $column){
                $columnData[$column->getField()->name] = $column->getData();
            }

            foreach ($columnData as $columnName => $indices){
                foreach ($indices as $index => $value){
                    $final[$index]["$columnName"] = $value;
                }

            }

        }

        return $final ?? [];

    }


    public static function write($arr, $file, $deleteFile = false)
    {
        if(file_exists($file)){
            if(!$deleteFile) {
                $existing = self::read($file);
                if (!Arr::isIdentical($arr, $existing)) {
                    $arr = Arr::merge($arr, $existing);
                }
            }else{
                @unlink($file);
            }

        }

        $keyVals = Arr::keyVals($arr);
        $columns = [];
        $schemas = [];

        foreach ($keyVals as $k => $v){
            $columns [] = $column = new DataColumn(
                DataField::createFromType($k, Typed::type(Arr::first($v))),
                $v
            );
            $schemas[] = $column->getField();
        }

        $schema = new Schema($schemas);

        $dirname = File::getDirectory($file);
        if (!File::isDirectory($dirname)) {
            File::makeDirectory($dirname, 0777, true);
        }

        $fileStream = fopen($file, 'w+');

        $parquetWriter = new ParquetWriter($schema, $fileStream);

        $metaData = ['author' => config('brand.name_code'), 'date' => ManipulateDate::toIso8601ZuluString(ManipulateDate::toNow())];

        $parquetWriter->setCustomMetadata($metaData);

        $groupWriter = $parquetWriter->CreateRowGroup();

        foreach ($columns as $data_column){
            $groupWriter->WriteColumn($data_column);
        }

        $groupWriter->finish();
        $parquetWriter->finish();
    }

    public static function test($file = null)
    {
        $write = false;
        if(empty($file)) {
            $write = true;
            $file = storage_path("app" . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "btrans" . DIRECTORY_SEPARATOR . 'test.parquet');
        }

        $actual = [
            0 => [
                "column1" => 1,
                "column2" => 2
            ],

            1 => [
                "column1" => 3,
                "column2" => 4
            ]

        ];


        if($write) {
            Parquet::write($actual, $file);
        }

        $expected = Parquet::read($file);

        File::arrToSpreadsheet($expected, $file.".xlsx");



        return Arr::isIdentical($actual, $expected);
    }


}