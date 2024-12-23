<?php

namespace common\integration\Override;

use App\Models\Transaction;
use common\integration\ManipulateDate;
use App\Models\Sale;
use common\integration\Models\Sale as CommonSale;
use common\integration\Utility\SqlBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CustomBuilder extends Builder
{
    // Controlling Variables Start
    private bool $is_enabled_any_value_function_refactoring = false;
    private bool $force_disable_group_by = false;
    private bool $distinct_on_refactor_for_paginated_count_query = false;
    private bool $ignore_assign_distinct_by_columns = false;
    // Controlling Variables End

    // Data Variables Start
    private array $unique_by_columns = [];
    private array $any_value_columns = [];
    private array|bool $distinct_by_columns = true;
    // Data Variables End

    // Overriding Functions Start
    /**
     * @Override
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and'): static
    {
        // Customization -- Replacing 'like' with 'iLike' if db connection is postgres
        if (SqlBuilder::isPgsql() && is_string($operator) && $operator == 'like') {
            $operator = 'ilike';
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    /**
     * @Override
     * Set the columns to be selected.
     *
     * @param  array|mixed  $columns
     * @return $this
     */
    public function select($columns = ['*']): static
    {
        if ($this->is_enabled_any_value_function_refactoring && $columns == ['*']) {
            $columns = $this->unique_by_columns;
        }

        $this->columns = [];
        $this->bindings['select'] = [];

        $columns = is_array($columns) ? $columns : func_get_args();

        foreach ($columns as $as => $column) {
            if (is_string($as) && $this->isQueryable($column)) {
                $this->selectSub($column, $as);
            } else {

                if (
                    $this->is_enabled_any_value_function_refactoring
                    && SqlBuilder::isMysql()
                    && ! in_array($column, $this->unique_by_columns + ['*'], true)
                ) {

                    if (is_string($column)) {
                        $raw_col = $column;
                        $column = DB::raw('any_value(' . $column . ') as ' . Arr::last(explode('.', $column)));
                        $this->any_value_columns[$column->getValue()] = $raw_col;
                    } else if ($column instanceof Expression && array_key_exists($column->getValue(), $this->any_value_columns)) {
                        if (in_array($this->any_value_columns[$column->getValue()], $this->unique_by_columns)) {
                            $column = $this->any_value_columns[$column->getValue()];
                        }
                    }

                }

                $this->columns[] = $column;
            }
        }

        return $this;
    }

    /**
     * @Override
     */
    public function groupBy(...$groups): static
    {
        if ($this->force_disable_group_by) {
            $this->groups = null;
        }

        return parent::groupBy(...$groups);
    }

    /**
     * @Override
     * -- Reasons:
     * -- With depend "$this->distinct_on_refactor_for_paginated_count_query" property, we have to add round bracket for distinct on in count query temporarily
     *
     * @param $columns
     * @return array
     */
    protected function runPaginationCountQuery($columns = ['*']): array
    {
        if ($this->groups || $this->havings) {
            $clone = $this->cloneForPaginationCount();

            if (is_null($clone->columns) && ! empty($this->joins)) {
                $clone->select($this->from.'.*');
            }

            return $this->newQuery()
                ->from(new Expression('('.$clone->toSql().') as '.$this->grammar->wrap('aggregate_table')))
                ->mergeBindings($clone)
                ->setAggregate('count', $this->withoutSelectAliases($columns))
                ->get()->all();
        }

        $without = $this->unions ? ['orders', 'limit', 'offset'] : ['columns', 'orders', 'limit', 'offset'];

        // Customization -- added round bracket for distinct on count query
        if ($this->distinct_on_refactor_for_paginated_count_query) {
            $this->distinct($this->prepareDistinctItems(true));
        }

        $result =  $this->cloneWithout($without)
            ->cloneWithoutBindings($this->unions ? ['order'] : ['select', 'order'])
            ->setAggregate('count', $this->withoutSelectAliases($columns))
            ->get()->all();

        // Customization -- resting round bracket for distinct on
        if ($this->distinct_on_refactor_for_paginated_count_query) {
            $this->distinct($this->prepareDistinctItems());
        }

        return $result;
    }

    /**
     * Add round bracket on count query for distinct on if db connection is postgres
     *
     * @return static
     */
    public function distinctItemsWithRoundBracket(): static
    {
        $this->distinct_on_refactor_for_paginated_count_query = true;
        $this->distinct_by_columns = $this->distinct;
        return $this;
    }

    /**
     * Force the query to only return distinct results.
     *
     * @return $this
     */
    public function distinct(): static
    {
        $columns = func_get_args();

        $builder = parent::distinct(...$columns);

        // Customization -- Temporarily saving distinct columns in an array
        if ($this->distinct_on_refactor_for_paginated_count_query && ! $this->ignore_assign_distinct_by_columns) {
            $this->distinct_by_columns = $this->distinct;
        }

        return $builder;
    }
    // Overriding Functions Start

    // New Builder Functions Start
    /**
     * Set the columns to be
     * For MySQL: group by and wrap with any_value() function of mysql
     * For PGSQL: distinct on
     *
     * @param ...$columns
     * @return $this
     */
    public function uniqueBy(...$columns): static
    {
        $this->force_disable_group_by
            = $this->is_enabled_any_value_function_refactoring
            = true;

        $columns_of_select = $this->columns ?? [];
        foreach ($columns as $column) {
            $this->unique_by_columns = array_merge(
                $this->unique_by_columns,
                Arr::wrap($column)
            );

            if (! in_array($column, $columns_of_select)) {
                $columns_of_select[] = $column;
            }
        }

        if (SqlBuilder::isPgsql()) {
            $this->groupBy(); // Resetting the group by clause. Because, for PG we are managing it with distinct
            $this->distinctItemsWithRoundBracket();
            $this->distinct($this->unique_by_columns);
        } else if (SqlBuilder::isMysql()) {
            $this->select($columns_of_select);

            $this->groupBy(
                $this->unique_by_columns
            );
        } else {
            $this->force_disable_group_by
                = $this->is_enabled_any_value_function_refactoring
                = false;

            $this->groupBy($this->unique_by_columns);
        }

        return $this;
    }
    // New Builder Functions Start

    // Private functions start
    private function prepareDistinctItems($for_count_query = false): Expression|array
    {
        if (SqlBuilder::isPgsql() && $for_count_query) {
            $this->ignore_assign_distinct_by_columns = true;
            $distinct = '';
            foreach ($this->distinct_by_columns as $distinct_by_item) {
                $distinct .= ', ' . $distinct_by_item;
            }
            return DB::raw('(' . substr($distinct, 2) . ')');
        }

        $this->ignore_assign_distinct_by_columns = false;
        return $this->distinct_by_columns;
    }



}
