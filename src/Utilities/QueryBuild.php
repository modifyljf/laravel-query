<?php

namespace Guesl\Query\Utilities;

use Guesl\Query\Exceptions\BusinessException;
use Guesl\Query\Models\Criterion;
use Guesl\Query\Models\EagerLoading;
use Guesl\Query\Models\Fuzzy;
use Guesl\Query\Models\Pagination;
use Guesl\Query\Models\Scope;
use Guesl\Query\Models\Sort;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Trait QueryBuild
 * @package Guesl\Query\Utilities
 */
trait QueryBuild
{
    /**
     * Get pagination info from request.
     *
     * @param Request $request
     * @return Pagination
     */
    public static function getPageInfo(Request $request): Pagination
    {
        $page = 1;
        $pageSize = Pagination::DEFAULT_PAGE_SIZE;

        if ($request->has('query')) {
            $query = json_decode($request->get('query'), true);

            if (Arr::exists($query, 'pagination')) {
                $pagination = $query['pagination'];

                $page = $pagination['page'];
                $pageSize = $pagination['page_size'];
            }
        }

        return new Pagination($page, $pageSize);
    }

    /**
     * Get filter columns.
     *
     * @param Request $request
     * @return array
     */
    public static function getCriterion(Request $request): array
    {
        $criteria = [];

        // Get the search columns by 'filters' parameters.
        if ($request->has('query')) {
            $query = json_decode($request->get('query'), true);

            if (Arr::exists($query, 'filters')) {
                $filterColumns = $query['filters'];

                $criteria = [];
                foreach ($filterColumns as $filterColumn) {
                    $name = Arr::exists($filterColumn, 'name') ? $filterColumn['name'] : '';
                    $operation = Arr::exists($filterColumn, 'operation') ? $filterColumn['operation'] : '';
                    $value = Arr::exists($filterColumn, 'value') ? $filterColumn['value'] : '';
                    $isExclusive = Arr::exists($filterColumn, 'exclusive') ? $filterColumn['exclusive'] : false;
                    $criterion = new Criterion($name, $operation, $value, $isExclusive);
                    array_push($criteria, $criterion);
                }
            }
        }

        return $criteria;
    }

    /**
     * Get search columns.
     *
     * @param Request $request
     * @return array
     */
    public static function getSearches(Request $request): array
    {
        $fuzzyArray = [];

        # Get the search columns by 'searches' parameters.
        if ($request->has('query')) {
            $query = json_decode($request->get('query'), true);

            if (Arr::exists($query, 'searches')) {
                $searchColumns = $query['searches'];

                foreach ($searchColumns as $searchColumn) {
                    $fuzzy = new Fuzzy($searchColumn['name'], $searchColumn['value']);
                    array_push($fuzzyArray, $fuzzy);
                }
            }
        }
        return $fuzzyArray;
    }

    /**
     * Get sort columns.
     *
     * @param Request $request
     * @return array
     */
    public static function getSorts(Request $request): array
    {
        $result = [];

        # Get the sort columns by 'sorts' parameters.
        if ($request->has('query')) {
            $query = json_decode($request->get('query'), true);

            if (Arr::exists($query, 'sorts')) {
                $sorts = $query['sorts'];

                foreach ($sorts as $sort) {
                    $name = Arr::exists($sort, 'name') ? $sort['name'] : '';
                    $direction = Arr::exists($sort, 'direction') ? $sort['direction'] : 'desc';
                    $sort = new Sort($name, $direction);
                    array_push($result, $sort);
                }
            }
        }

        return $result;
    }

    /**
     * Get eager loading.
     *
     * @param Request $request
     * @return array
     * @throws BusinessException
     */
    public static function getEagerLoading(Request $request): array
    {
        $result = [];

        # Get the sort columns by 'eager_loadings' parameters.
        if ($request->has('query')) {
            $query = json_decode($request->get('query'), true);

            if (Arr::exists($query, 'eager_loadings')) {
                $eagerLoadings = $query['eager_loadings'];

                foreach ($eagerLoadings as $eagerLoading) {
                    $name = Arr::exists($eagerLoading, 'name') ? $eagerLoading['name'] : '';
                    $table = self::getModelTable($name);
                    if (isset($table)) {
                        $el = new EagerLoading($name);
                        array_push($result, $el);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get the scopes.
     *
     * @param Request $request
     * @return array
     */
    public static function getScopes(Request $request): array
    {
        $result = [];

        if ($request->has('query')) {
            $query = json_decode($request->get('query'), true);

            if (Arr::exists($query, 'scopes')) {
                $scopes = $query['scopes'];

                foreach ($scopes as $scope) {
                    $name = Arr::exists($scope, 'name') ? $scope['name'] : '';
                    $parameters = Arr::exists($scope, 'parameters') ? $scope['parameters'] : [];
                    $scope = new Scope($name, $parameters);
                    array_push($result, $scope);
                }
            }
        }

        return $result;
    }

    /**
     * Get the model related table name.
     *
     * @param string $modelRelations
     * @return string
     * @throws BusinessException
     */
    public static function getModelTable(string $modelRelations): string
    {
        if (strpos($modelRelations, '.')) {
            $relations = explode('.', $modelRelations);
            $lastModel = array_pop($relations);
        } else {
            $lastModel = $modelRelations;
        }

        $name = Str::singular($lastModel);
        $modelClassName = config("query.models.$name");

        if (isset($modelClassName)) {
            $model = new $modelClassName;
            $tableName = $model->getTable();
        } else {
            $modelClassName = Container::getInstance()->getNamespace() . 'Models\\' . ucfirst($modelRelations);

            if (class_exists($modelClassName)) {
                $model = new $modelClassName;
                $tableName = $model->getTable();
            } else {
                throw new BusinessException($name, 'Invalid model name.');
            }
        }

        return $tableName;
    }

    /**
     * Get the model related database.
     *
     * @param string $modelRelations
     * @return string
     */
    public static function getModelDB(string $modelRelations): string
    {
        $defaultConnectionName = env('DB_CONNECTION', 'mysql');

        if (strpos($modelRelations, '.')) {
            $relations = explode('.', $modelRelations);
            $lastModel = array_pop($relations);
            $modelClassName = config("query.models.$lastModel");
        } else {
            $modelClassName = config("query.models.$modelRelations");
        }

        if (isset($modelClassName)) {
            $model = new $modelClassName;
            $defaultConnectionName = $model->getConnectionName();
        }

        return config('database.connections.' . $defaultConnectionName . ".database");
    }
}
