<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 11/27/15
 * Time: 7:47 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\Laravel\JsonApi\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Xooxx\JsonApi\Http\Factory\RequestFactory;
use Xooxx\Laravel\JsonApi\JsonApiSerializer;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EloquentHelper.
 */
trait EloquentHelper
{
    /**
     * @param Model $model
     * @param  array|string  $with
     * @return Model| null
     */
    public static function fresh(&$model, $with = []){
        if (! $model->exists) {
            return null;
        }
        return $model->newQueryWithoutScopes()
            ->with(is_string($with) ? func_get_args() : $with)
            ->where($model->getKeyName(), $model->getKey())
            ->first();
    }

    /**
     * @param JsonApiSerializer $serializer
     * @param Builder $builder
     * @param int $pageSize
     *
     * @return Builder
     * @throws \ReflectionException
     */
    public static function paginate(JsonApiSerializer $serializer, Builder $builder, $pageSize = null)
    {
        self::sort($serializer, $builder, $builder->getModel());
        $request = RequestFactory::create();

        /** @var \Illuminate\Database\DatabaseManager | \Illuminate\Database\Connection $db */
        $db = app('db');
        $db->getPaginator()->setCurrentPage($request->getPage()->number());
        $builder->paginate($request->getPage()->size() ?: $pageSize, self::columns($serializer, $request->getFields()->get()));
        return $builder;
    }

    /**
     * @param JsonApiSerializer $serializer
     * @param Builder $builder
     * @param Model | \Illuminate\Database\Eloquent\Model $model
     *<
     * @return Builder
     * @throws \ReflectionException
     */
    protected static function sort(JsonApiSerializer $serializer, Builder $builder, Model $model)
    {
        /**@var \Illuminate\Database\Query\Builder | \Illuminate\Database\Eloquent\Builder  $builder */
        $mapping = $serializer->getTransformer()->getMappingByClassName(get_class($model));
        $sorts = RequestFactory::create()->getSort()->sorting();
        if (!empty($sorts)) {
            $aliased = $mapping->getAliasedProperties();
            $sortsFields = str_replace(array_values($aliased), array_keys($aliased), array_keys($sorts));
            $sorts = array_combine($sortsFields, array_values($sorts));
            foreach ($sorts as $field => $direction) {
                $builder->orderBy($field, $direction === 'ascending' ? 'ASC' : 'DESC');
            }
        }
        return $builder;
    }
    /**
     * @param JsonApiSerializer $serializer
     * @param array             $fields
     *
     * @return array
     */
    protected static function columns(JsonApiSerializer $serializer, array $fields)
    {
        $filterColumns = [];
        foreach ($serializer->getTransformer()->getMappings() as $mapping) {
            $classAlias = $mapping->getClassAlias();
            if (!empty($fields[$classAlias])) {
                $className = $mapping->getClassName();
                $aliased = $mapping->getAliasedProperties();
                /** @var Model $model * */
                $model = new $className();
                $columns = $fields[$classAlias];
                if (count($aliased) > 0) {
                    $columns = str_replace(array_values($aliased), array_keys($aliased), $columns);
                }
                foreach ($columns as &$column) {
                    $filterColumns[] = sprintf('%s.%s', $model->getTable(), $column);
                }
                $filterColumns[] = sprintf('%s.%s', $model->getTable(), $model->getKeyName());
            }
        }
        return count($filterColumns) > 0 ? $filterColumns : ['*'];
    }
}