<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 8/16/15
 * Time: 4:27 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\Laravel\JsonApi\Facades;

use Illuminate\Support\Facades\Facade;
/**
 * Class JsonApiSerializer.
 */
class JsonApiSerializer extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Xooxx\Laravel\JsonApi\JsonApiSerializer::class;
    }
}