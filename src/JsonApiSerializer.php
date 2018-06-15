<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 8/16/15
 * Time: 4:43 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\Laravel\JsonApi;

use Xooxx\Serializer\Drivers\Eloquent\EloquentDriver;
/**
 * Class JsonApiSerializer.
 */
class JsonApiSerializer extends \Xooxx\JsonApi\JsonApiSerializer
{
    /**
     * Extract the data from an object.
     *
     * @param mixed $value
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function serializeObject($value)
    {
        $serialized = EloquentDriver::serialize($value);
        return $value !== $serialized ? $serialized : parent::serializeObject($value);
    }
}