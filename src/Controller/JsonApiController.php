<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 12/7/15
 * Time: 12:17 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\Laravel\JsonApi\Controller;

use Xooxx\JsonApi\JsonApiTransformer;
use Xooxx\Laravel\JsonApi\Access\AuthorizesRequests;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Xooxx\JsonApi\Http\Factory\RequestFactory;
use Xooxx\JsonApi\Http\Response\ResourceNotFound;
use Xooxx\Laravel\JsonApi\Actions\CreateResource;
use Xooxx\Laravel\JsonApi\Actions\DeleteResource;
use Xooxx\Laravel\JsonApi\Actions\GetResource;
use Xooxx\Laravel\JsonApi\Actions\ListResource;
use Symfony\Component\HttpFoundation\Response;
/**
 * Class JsonApiController.
 */
abstract class JsonApiController extends Controller
{
    use JsonApiTrait;
    use AuthorizesRequests;

    /**
     * Get many resources.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $apiRequest = RequestFactory::create();
        $page = $apiRequest->getPage();
        if (!$page->size()) {
            $page->setSize($this->pageSize);
        }

        $fields = $apiRequest->getFields();
        $sorting = $apiRequest->getSort();
        $included = $apiRequest->getIncludedRelationships();
        $filters = $apiRequest->getFilters();
        $resource = new ListResource($this->serializer, $page, $fields, $sorting, $included, $filters);
        $totalAmount = $this->totalAmountResourceCallable();
        $results = $this->listResourceCallable();
        $controllerAction = get_called_class() . '@index';
        $uri = $this->uriGenerator($controllerAction);
        return $this->addHeaders($resource->get($totalAmount, $results, $uri, get_class($this->getDataModel())));
    }
    /**
     * Get single resource.
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($id)
    {
        $apiRequest = RequestFactory::create();
        $resource = new GetResource($this->serializer, $apiRequest->getFields(), $apiRequest->getIncludedRelationships());
        $find = $this->findResourceCallable($id);
        return $this->addHeaders($resource->get($id, get_class($this->getDataModel()), $find));
    }
    /**
     * @return ResourceNotFound
     */
    public function create()
    {
        return new ResourceNotFound();
    }
    /**
     * Post Action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store()
    {
        $createResource = $this->createResourceCallable();
        $resource = new CreateResource($this->serializer);
        $model = $this->getDataModel();
        $data = (array) request()->json(JsonApiTransformer::DATA_KEY, []);

        if (array_key_exists(JsonApiTransformer::ATTRIBUTES_KEY, $data) && $model->timestamps) {
            $data[JsonApiTransformer::ATTRIBUTES_KEY][$model::CREATED_AT] = Carbon::now()->toDateTimeString();
            $data[JsonApiTransformer::ATTRIBUTES_KEY][$model::UPDATED_AT] = Carbon::now()->toDateTimeString();
        }

        return $this->addHeaders($resource->get($data, get_class($this->getDataModel()), $createResource));
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function update($id)
    {
        return strtoupper(request()->getMethod()) === 'PUT' ? $this->putAction($id) : $this->patchAction($id);
    }
    /**
     * @return ResourceNotFound
     */
    public function edit()
    {
        return new ResourceNotFound();
    }
    /**
     * @param $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $find = $this->findResourceCallable($id);
        $delete = $this->deleteResourceCallable($id);
        $resource = new DeleteResource($this->serializer);
        return $this->addHeaders($resource->get($id, get_class($this->getDataModel()), $find, $delete));
    }
}