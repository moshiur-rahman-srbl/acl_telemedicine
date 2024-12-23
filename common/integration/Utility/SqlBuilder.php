<?php

namespace common\integration\Utility;

use App\Models\Merchant;
use common\integration\ManipulateDate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SqlBuilder
{
    private $params = [];
    private $modified_params = [];
    private $type;

    const MYSQL_DUPLICATE_ERROR_CODE = 23000;
    const PGSQL_DUPLICATE_ERROR_CODE = 23505;
    const MYSQL_SQLSTATE_TIMEOUT_ERROR_CODE = "HY000";
    const MYSQL_SQLSTATE_COLUMN_NOT_FOUND = "42S22";

    public static function isPgsql() : bool
    {
        return config('database.default') == config('database.connections.pgsql.driver');
    }

    public static function isMysql() : bool
    {
        return config('database.default') == config('database.connections.mysql.driver');
    }

    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setParams(...$params)
    {
        $this->params = $params;
        return $this;
    }

    public function modifyParams ($offset = 0, $length = 1)
    {
        if($this->type == "groupBy") {
            if (self::isPgsql()) {
                 $this->modified_params = $this->params;
            }

            if (self::isMysql()) {
                 $this->modified_params = Arr::slice($this->params, $offset, $length);
            }
        }

        return $this;

    }

    public function getModifiedParams()
    {
        return $this->modified_params;
    }

    public static function likeOperator() : string {
        $like_operator = 'LIKE';

        if (self::isPgsql()) {
            $like_operator = 'ILIKE';
        }

        return $like_operator;
    }

    /**
     * timeDiff
     *
     * @param  string $unit unit 
     * @param  string $first_date
     * @param  string $second_date
     * @param  string $operation 
     * @param  mix $time_to_compare
     * @return string
     */
    public static function timestampDiffBuilder(string $unit, string $first_date, string $second_date, string $operation, int $time_to_compare) : string
    {
        if (self::isPgsql()) {
            return "AGE(" . $second_date . ", " . $first_date . ") " . $operation . " interval'" . $time_to_compare . " " . $unit . "S'";
        }

        return "abs(TIMESTAMPDIFF(" . $unit . ",`" . $first_date . "`, `" . $second_date . "`)) " . $operation . " " . $time_to_compare;
    }


    public static function groupBy($query, $distinct_column = null)
    {
        if (self::isPgsql()) {
            if ($query instanceof \Illuminate\Database\Eloquent\Builder) {
                if(!empty($distinct_column)) {
                    $query = self::modifyDistinct($query, $distinct_column);
                }
                $query = self::modifyOrders($query, $distinct_column);
                $query = self::modifyGroups($query, $distinct_column);
                $query = self::asSub($query);
            }
        }


        return $query;
    }

    private static function modifyDistinct($query, $distinct_column)
    {
        if (!empty($distinct_column)) {
            if ($query instanceof \Illuminate\Database\Eloquent\Builder){
                $query->distinct(1);
                $query->distinct($distinct_column);
            }
        }
        return $query;
    }

    private static function modifyOrders($query, $distinct_column = null)
    {
        $orders = $query->getQuery()->orders;
        $query->original_orders_of_order_by = $orders;
        $query->getQuery()->orders = [];
        if(!empty($distinct_column)) {
            $query->orderBy($distinct_column);
        }
        foreach ($orders as $order) {
            $column = $order["column"] ?? "";
            $direction = $order["direction"] ?? "";
            if (!empty($column) && $column != $distinct_column) {
                $query->orderBy($column, $direction);
            }
        }
        return $query;
    }

    private static function modifyGroups($query, $distinct_column = null)
    {
        $columns = $query->getQuery()->columns;

        $modified_params = [];



        if(!empty($distinct_column)) {
            $modified_params [] = $distinct_column;
        }

        foreach ($columns as $column) {
            if(Str::contains($column, $query->getQuery()->from.".*")){
                $modified_params [] = $query->getQuery()->from.".id";
            }

            if (!Str::contains($column, ".*")) {
                $filtered_column = self::filterColumn($column);
                if ($filtered_column != $distinct_column) {
                    $modified_params [] = $filtered_column;
                }
            }

        }

        $query->groupBy($modified_params);
        $groups = Arr::unique($query->getQuery()->groups);
        $query->getQuery()->groups = $groups;

        return $query;
    }

    public static function asSub($query)
    {
        $eagerLoads = [];

        if ($query instanceof \Illuminate\Database\Eloquent\Builder) {
            $eagerLoads = $query->getEagerLoads();
        }

        $as_sub = $query->getModel()::query()->from($query, $query->getQuery()->from)
            ->select('*');
        foreach($query->original_orders_of_order_by as $order){
            $as_sub->orderBy($order["column"], $order["direction"]);
        }

        if ($as_sub instanceof \Illuminate\Database\Eloquent\Builder) {
            $as_sub->setEagerLoads($eagerLoads);
        }

        return $as_sub;

    }

    private static function filterColumn($column)
    {
        $position_of_as = Str::position($column, " as ");
        return Str::sartEndSubStr($column, 0, $position_of_as);

    }

    public static function rollback($action = "SQL_BUILDER_ROLLBACK")
    {
        try {
         return DB::rollBack();
        }catch (\Throwable $throwable){
            \common\integration\Utility\Exception::log($throwable, $action);
        }
    }
	/*
	 * TABLE CREATED_AT_INT AND UPDATED_AT_INT MANIPULATION
	 * moved from SaleTransaction class
	 */
	public static function createdAtIntAndUpdatedAtIntManipulation($search): array
	{
		$from_date_int = null;
		$to_date_int = null;
		if ((isset($search['daterange']) || isset($search['date_range'])) && isset($search['from_date']) && !empty($search['from_date']) && isset($search['to_date']) && !empty($search['to_date'])) {
			$from_date_int = Str::replace(['/', '-'], '', ManipulateDate::getDateFormat($search['from_date'], 'Ymd'));
			$to_date_int = Str::replace(['/', '-'], '', ManipulateDate::getDateFormat($search['to_date'], 'Ymd'));
		}
		return [intval($from_date_int), intval($to_date_int)];
	}

    public static function isDuplicateError($exception)
    {
        $is_duplicate = false;

        if(($exception instanceof \Throwable)){
            if (self::isMysql()) {
                $is_duplicate = $exception->getCode() == self::MYSQL_DUPLICATE_ERROR_CODE;
            }

            if (self::isPgsql()) {
                $is_duplicate = $exception->getCode() == self::PGSQL_DUPLICATE_ERROR_CODE;
            }
        }

        return $is_duplicate;
    }

    public static function isSqlStateTimeout($exception)
    {
        if ($exception instanceof QueryException) {
            if (self::isMysql()){
                $errorCode =  $exception->errorInfo[1] ?? null;
                return $errorCode == self::MYSQL_SQLSTATE_TIMEOUT_ERROR_CODE;
            }
            return false;
        }
        return false;
    }

}