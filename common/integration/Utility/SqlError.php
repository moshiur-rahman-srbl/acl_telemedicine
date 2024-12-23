<?php

namespace common\integration\Utility;

use Illuminate\Database\QueryException;

class SqlError
{
    const MYSQL_QUERY_EXCEPTION_GETCODE = 23000;
    const MYSQL_ERROR_CODE_DUPLICATE_ENTRY = 1062;

    const PGSQL_ERROR_CODE_DUPLICATE_ENTRY = 7;


    public function getDuplicateEntryErrorCode()
    {
        if(SqlBuilder::isMysql()){
            return self::MYSQL_ERROR_CODE_DUPLICATE_ENTRY;
        }

        if(SqlBuilder::isPgsql()){
            return self::PGSQL_ERROR_CODE_DUPLICATE_ENTRY;
        }

        return null;
    }


    public function indicatesIntegrityConstraintViolation_ForDuplicateEntry(\Throwable $throwable, $column = null)
    {
        if($throwable instanceof QueryException){
            $errorCode =  $throwable->errorInfo[1] ?? null;
            $errorDetail = $throwable->errorInfo[2] ?? null;
            $isError = $errorCode == $this->getDuplicateEntryErrorCode();
            if(empty($column)) {
                return $isError;
            }else{
                return $isError && Str::contains($errorDetail, $column);
            }
        }

        return false;

    }

}