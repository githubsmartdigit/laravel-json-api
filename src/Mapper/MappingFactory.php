<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 10/16/15
 * Time: 8:59 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\Laravel\JsonApi\Mapper;

use Xooxx\Api\Mapping\Mapping;
use Xooxx\Api\Mapping\MappingException;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
/**
 * Class MappingFactory.
 */
class MappingFactory extends \Xooxx\Api\Mapping\MappingFactory
{
    /**
     * @var array
     */
    protected static $eloquentClasses = [];

    /**
     * @param string $className
     *
     * @return array
     * @throws \ReflectionException
     */
    protected static function getClassProperties($className)
    {
        if (\class_exists($className, true)) {
            $reflection = new ReflectionClass($className);
            $value = $reflection->newInstanceWithoutConstructor();
            if (\is_subclass_of($value, Model::class, true)) {
                $attributes = $value->getConnection()->getSchemaBuilder()->getColumnListing($value->getTable()); //Schema::
                self::$eloquentClasses[$className] = $attributes;
            }
        }
        return !empty(self::$eloquentClasses[$className]) ? self::$eloquentClasses[$className] : parent::getClassProperties($className);
    }


}
