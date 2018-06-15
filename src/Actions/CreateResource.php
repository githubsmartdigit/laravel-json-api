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

use Exception;
use Xooxx\JsonApi\Server\Errors\ErrorBag;
/**
 * Class CreateResource.
 */
class CreateResource extends \Xooxx\JsonApi\Server\Actions\CreateResource
{
    /**
     * @param Exception $e
     * @param ErrorBag  $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws Exception
     */
    public function getErrorResponse(Exception $e, ErrorBag $errorBag)
    {
        if (config('app.debug')) {
            throw $e;
        }
        return parent::getErrorResponse($e, $errorBag);
    }
}