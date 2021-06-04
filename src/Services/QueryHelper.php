<?php

namespace Guesl\Query\Services;

use Guesl\Query\Exceptions\BusinessException;
use Guesl\Query\Models\Criterion;
use Guesl\Query\Models\EagerLoading;
use Guesl\Query\Models\Fuzzy;
use Guesl\Query\Models\Pagination;
use Guesl\Query\Models\Scope;
use Guesl\Query\Models\Sort;
use Guesl\Query\Utilities\QueryBuild;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Trait QueryHelper
 * @package Guesl\Query\Services
 */
trait QueryHelper
{
    use QueryBuild;

    /**
     * Fetch page object by table's name , page size, searching info ,and ordering info;
     *
     * $modelClass : The  Class Name of eloquent model.
     *
     * @param string $modelClass
     * @param Pagination|null $pagination
     * @param array<Criterion> $criteria
     * @param array<Sort> $sorts
     * @param array<Fuzzy> $searches
     * @param array<EagerLoading> $eagerLoadings
     * @param array<Scope> $scopes
     * @return LengthAwarePaginator|Collection
     * @throws BusinessException
     */
    public function fetch(string $modelClass, ?Pagination $pagination, array $criteria = [], array $sorts = [], array $searches = [], array $eagerLoadings = [], array $scopes = [])
    {
        Log::debug(get_class($this) . '::fetch => Fetch model page object by model\'s name , page size, searching info and sorting info.');

        $query = $modelClass::query();

        if (isset($scopes) && sizeof($scopes) > 0) {
            foreach ($scopes as $scope) {
                $parameters = $scope->getParameters();
                $scopeName = $scope->getName();
                if (!empty($parameters))
                    $query->{$scopeName}($parameters);
                else
                    $query->{$scopeName}();
            }
        }

        if (isset($criteria) && sizeof($criteria) > 0) {
            $query->where(function ($q) use ($criteria) {
                foreach ($criteria as $criterion) {
                    $name = $criterion->getName();

                    if (str_contains($name, '.')) {
                        $strArr = explode('.', $name);

                        $column = Str::lower(array_pop($strArr));
                        $relations = implode('.', $strArr);

                        $database = $this->getModelDB($relations);
                        $table = $this->getModelTable($relations);
                        $filteringColumn = $table . '.' . $column;

                        if ('' != $criterion->getValue()) {
                            if ($criterion->isExclusive()) {
                                $q->whereDoesntHave($relations, function ($relateQuery) use ($criterion, $database, $table, $filteringColumn) {
                                    $relateQuery->from($database . '.' . $table);
                                    $this->generateCriteria($relateQuery, $criterion, $filteringColumn);
                                });
                            } else {
                                $q->whereHas($relations, function ($relateQuery) use ($criterion, $database, $table, $filteringColumn) {
                                    $relateQuery->from($database . '.' . $table);
                                    $this->generateCriteria($relateQuery, $criterion, $filteringColumn);
                                });
                            }
                        } else {
                            $q->has($relations);
                        }
                    } else {
                        $q = $this->generateCriteria($q, $criterion, $name);
                    }
                }
            });
        }

        if (isset($searches) && sizeof($searches) > 0) {
            $query->where(function ($q) use ($searches) {
                foreach ($searches as $search) {
                    $searchColumn = $search->getName();
                    $searchValue = $search->getValue();
                    if (str_contains($searchColumn, '.')) {
                        $strArr = explode('.', $searchColumn);

                        $searchColumn = array_pop($strArr);
                        $relations = implode('.', $strArr);

                        $database = $this->getModelDB($relations);
                        $table = $this->getModelTable($relations);

                        $q->orWhereHas($relations, function ($relateQuery) use ($searchColumn, $searchValue, $database, $table) {
                            $relateQuery->from($database . '.' . $table);
                            $relateQuery->where($searchColumn, 'like', '%' . $searchValue . '%');
                        });
                    } else {
                        $q->orWhere($searchColumn, 'like', '%' . $searchValue . '%');
                    }
                }
            });
        }

        if (isset($eagerLoadings) && sizeof($eagerLoadings) > 0) {
            foreach ($eagerLoadings as $eagerLoading) {
                $name = $eagerLoading->getName();
                $database = $this->getModelDB($name);
                $table = $this->getModelTable($name);
                $query = $query->with([$eagerLoading->getName() => function ($q) use ($database, $table) {
                    //make sure last column is from the right connection.
                    $q->from($database . '.' . $table);
                }]);
            }
        }

        if (isset($sorts) && sizeof($sorts) > 0) {
            foreach ($sorts as $sort) {
                $sortColumn = $sort->getName();
                $dir = $sort->getDirection();
                if (str_contains($sortColumn, '.')) {
                    $columns = explode('.', $sortColumn);

                    $column = array_pop($columns);
                    $relations = implode('.', $columns);
                    //one to many
                    $query->with([$relations => function ($relateQuery) use ($column, $dir) {
                        $relateQuery->orderBy($column, $dir);
                    }]);
                } else {
                    $query->orderBy($sortColumn, $dir);
                }
            }
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        if (isset($pagination)) { // if the page info exists , then fetch the pagination info.
            $perPage = $pagination->getPageSize();
            $page = $pagination->getPage();
            $result = $query->paginate($perPage, ['*'], 'page', $page);

        } else {
            $result = $query->get();
        }

        return $result;
    }

    /**
     * Generate criteria.
     *
     * @param $q
     * @param Criterion $criterion
     * @param $column
     * @return object
     */
    protected function generateCriteria($q, Criterion $criterion, $column)
    {
        if ('' != $criterion->getValue()) {
            $operation = $criterion->getOperation();
            $value = $criterion->getValue();

            if ('isNull' == $operation) {
                $q->whereNull($column);
            } else if ('isNotNull' == $operation) {
                $q->whereNotNull($column);
            } else if ('in' == $operation && is_array($value)) {
                $q->whereIn($column, $value);
            } else if ('notIn' == $operation && is_array($value)) {
                $q->whereNotIn($column, $value);
            } else if ('between' == $operation && is_array($value)) {
                $q->whereBetween($column, $value);
            } else {
                $q->where($column, $operation, $value);
            }
        } else {
            $q->whereNull($column);
        }

        return $q;
    }

    /**
     * Fetch Model by id.
     * Eager Loading : Eager Loading attributes;
     *
     * @param $modelClass
     * @param $id
     * @param array $eagerLoadings
     * @param string|null $keyName
     * @return Model|null
     * @throws BusinessException
     */
    public function retrieve($modelClass, $id, array $eagerLoadings, string $keyName = null): ?Model
    {
        Log::debug(get_class($this) . '::retrieve => Fetch model data by model\'s name with eager loadings');

        if (isset($keyName)) {
            $query = $modelClass::where($keyName, $id);
        } else {
            $query = $modelClass::where((new $modelClass())->getKeyName(), $id);
        }

        if (isset($eagerLoadings) && sizeof($eagerLoadings) > 0) {
            foreach ($eagerLoadings as $eagerLoading) {
                $relation = $eagerLoading->getName();
                if (method_exists($modelClass, $relation)) {
                    $query = $query->with($eagerLoading->getName());
                } else {
                    throw new BusinessException($relation, 'Invalid model name.');
                }
            }
        }
        return $query->first();
    }

    /**
     * Create a new model(Persistence data).
     *
     * @param $modelClass
     * @param $data
     * @return Model
     */
    public function createModel($modelClass, $data): Model
    {
        $model = new $modelClass();

        foreach ($data as $col => $value) {
            $model->{$col} = $value;
        }
        $model->save();

        return $model;
    }

    /**
     * Update model by id.
     * $data : attributes which should be updated.
     *
     * @param $modelClass
     * @param $id
     * @param $data
     * @param null $keyName
     * @return Model
     */
    public function updateModel($modelClass, $id, $data, $keyName = null): Model
    {
        if (isset($keyName)) {
            $model = $modelClass::where($keyName, $id)->first();
        } else {
            $model = $modelClass::find($id);
        }

        if ($model) {
            foreach ($data as $key => $value) {
                $model->$key = $value;
            }
            $model->save();
        }
        return $model;
    }

    /**
     * Delete the model by id.
     *
     * @param $modelClass
     * @param $id
     * @param null $keyName
     */
    public function deleteModel($modelClass, $id, $keyName = null)
    {
        Log::debug(get_class($this) . '::deleteModel => Delete model data by model\'s name and id');

        if (isset($keyName)) {
            $model = $modelClass::where($keyName, $id);
        } else {
            $model = $modelClass::where((new $modelClass())->getKeyName(), $id);
        }

        $model->delete();
    }
}
