<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 5/04/16
 * Time: 0:17.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\Laravel\JsonApi\Actions;

/**
 * Class PatchResource.
 */
class PatchResource extends \Xooxx\JsonApi\Server\Actions\PatchResource
{
    /**
     * @param \Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function getErrorResponse(\Exception $e)
    {
        if (config('app.debug')) {
            throw $e;
        }
        return parent::getErrorResponse($e);
    }
}