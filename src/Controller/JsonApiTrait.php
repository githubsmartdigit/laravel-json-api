<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 13/01/16
 * Time: 19:56.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\Laravel\JsonApi\Controller;

use Xooxx\JsonApi\JsonApiTransformer;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Xooxx\Laravel\JsonApi\Actions\PatchResource;
use Xooxx\Laravel\JsonApi\Actions\PutResource;
use Xooxx\JsonApi\Server\Errors\Error;
use Xooxx\JsonApi\Server\Errors\ErrorBag;
use Xooxx\Laravel\JsonApi\Eloquent\EloquentHelper;
use Xooxx\Laravel\JsonApi\JsonApiSerializer;
use Symfony\Component\HttpFoundation\Response;
use URL;

trait JsonApiTrait
{
    /**
     * @var JsonApiSerializer
     */
    protected $serializer;
    /**
     * @var int
     */
    protected $pageSize = 10;
    /**
     * @param JsonApiSerializer $serializer
     */
    public function __construct(JsonApiSerializer $serializer)
    {
        $this->serializer = $serializer;
    }
    /**
     * @param $controllerAction
     *
     * @return mixed
     */
    protected function uriGenerator($controllerAction)
    {
        return  URL::action($controllerAction, [], true);
    }
    /**
     * Returns the total number of results available for the current resource.
     *
     * @return callable
     * @codeCoverageIgnore
     */
    protected function totalAmountResourceCallable()
    {
        return function () {
            $idKey = $this->getDataModel()->getKeyName();
            return $this->getDataModel()->count([$idKey]);
        };
    }
    /**
     * Returns an Eloquent Model.
     *
     * @return Model | \Illuminate\Database\Query\Builder
     */
    public abstract function getDataModel();


    /**
     * Returns a list of resources based on pagination criteria.
     *
     * @return callable
     * @codeCoverageIgnore
     */
    protected function listResourceCallable()
    {
        return function (array $filters) {
            return EloquentHelper::paginate($this->serializer, $this->getDataModel()->query(), $this->pageSize)->get();
        };
    }
    /**
     * @param Response $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function addHeaders(Response $response)
    {
        return $response;
    }

    /**
     * @param $id
     *
     * @return callable
     * @codeCoverageIgnore
     */
    protected function findResourceCallable($id)
    {
        return function () use($id) {
            $idKey = $this->getDataModel()->getKeyName();
            $model = $this->getDataModel()->query()->where($idKey, $id)->first();
            return $model;
        };
    }
    /**
     * Reads the input and creates and saves a new Eloquent Model.
     *
     * @return callable
     * @codeCoverageIgnore
     */
    protected function createResourceCallable()
    {
        return function (array $data, array $values, ErrorBag $errorBag) {
            /**@var Model $model */
            $model = $this->getDataModel()->newInstance();
            foreach ($values as $attribute => $value) {
                $model->setAttribute($attribute, $value);
            }
            if (!empty($data['id'])) {
                $model->setAttribute($model->getKeyName(), $data['id']);
            }
            try {
                $model->save();
                //We need to load the model from the DB in case the user is utilizing getRequiredFields() on the transformer.
                EloquentHelper::fresh($model);
            } catch (\Exception $e) {
                $errorBag[] = new Error('creation_error', 'Resource could not be created');
                throw $e;
            }
            return $model;
        };
    }

    /**
     * @param $id
     * @return Response
     */
    protected function putAction($id)
    {
        $find = $this->findResourceCallable($id);
        $update = $this->updateResourceCallable();
        $resource = new PutResource($this->serializer);
        $model = $this->getDataModel();
        $data = (array) request()->json(JsonApiTransformer::DATA_KEY);

        if (array_key_exists(JsonApiTransformer::ATTRIBUTES_KEY, $data) && $model->timestamps) {
            $data[JsonApiTransformer::ATTRIBUTES_KEY][$model::UPDATED_AT] = Carbon::now()->toDateTimeString();
        }

        return $this->addHeaders($resource->get($id, $data, get_class($model), $find, $update));
    }
    /**
     * @return callable
     * @codeCoverageIgnore
     */
    protected function updateResourceCallable()
    {
        return function (Model $model, array $data, array $values, ErrorBag $errorBag) {
            foreach ($values as $attribute => $value) {
                $model->{$attribute} = $value;
            }
            try {
                $model->update();
            } catch (\Exception $e) {
                $errorBag[] = new Error('update_failed', 'Could not update resource.');
                throw $e;
            }
        };
    }

    /**
     * @param $id
     * @return Response
     */
    protected function patchAction($id)
    {
        $find = $this->findResourceCallable($id);
        $update = $this->updateResourceCallable();
        $resource = new PatchResource($this->serializer);
        $model = $this->getDataModel();
        $data = (array) request()->json(JsonApiTransformer::DATA_KEY);

        if (array_key_exists(JsonApiTransformer::ATTRIBUTES_KEY, $data) && $model->timestamps) {
            $data[JsonApiTransformer::ATTRIBUTES_KEY][$model::UPDATED_AT] = Carbon::now()->toDateTimeString();
        }
        return $this->addHeaders($resource->get($id, $data, get_class($model), $find, $update));
    }
    /**
     * @param $id
     *
     * @return \Closure
     */
    protected function deleteResourceCallable($id)
    {
        return function () use($id) {
            $idKey = $this->getDataModel()->getKeyName();
            $model = $this->getDataModel()->query()->where($idKey, $id)->first();
            return $model->delete();
        };
    }
}