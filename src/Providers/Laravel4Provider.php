<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 4/01/16
 * Time: 0:06.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\Laravel\JsonApi\Providers;

use \Cache;
use Xooxx\JsonApi\JsonApiTransformer;
use Xooxx\Api\Mapping\Mapping;
use Xooxx\Laravel\JsonApi\JsonApiSerializer;
use Xooxx\Laravel\JsonApi\Mapper\Mapper;
use ReflectionClass;
/**
 * Class Laravel4Provider.
 */
class Laravel4Provider
{
    /**
     * @param array $transformers
     * @return \Closure
     */
    public function provider($transformers)
    {
        return function ($app) use ($transformers) {

            /**@var \Config $config */
            $config = $app['config'];

            if ($config->get('app.debug')) {
                $parsedRoutes = $this->parseRoutes(new Mapper($transformers));
            } else {
                $parsedRoutes = Cache::rememberForever('api20.mapping', function () use($app, $config, $transformers) {
                    return $this->parseRoutes(new Mapper($transformers));
                });
            }
            return new JsonApiSerializer(new JsonApiTransformer($parsedRoutes));
        };
    }

    /**
     * @param Mapper $mapper
     *
     * @return Mapper
     * @throws \ReflectionException
     */
    protected function parseRoutes(Mapper $mapper)
    {
        foreach ($mapper->getClassMap() as &$mapping) {
            $mappingClass = new \ReflectionClass($mapping);
            $this->setUrlWithReflection($mapping, $mappingClass, 'resourceUrlPattern');
            $this->setUrlWithReflection($mapping, $mappingClass, 'selfUrl');
            $mappingProperty = $mappingClass->getProperty('otherUrls');
            $mappingProperty->setAccessible(true);
            $otherUrls = (array) $mappingProperty->getValue($mapping);
            if (!empty($otherUrls)) {
                foreach ($otherUrls as &$url) {
                    if (!empty($url['name'])) {
                        $url = $this->calculateRoute($url);
                    }
                }
            }
            $mappingProperty->setValue($mapping, $otherUrls);
            $this->setJsonApiRelationships($mappingClass, $mapping);
        }
        return $mapper;
    }
    /**
     * @param Mapping         $mapping
     * @param ReflectionClass $mappingClass
     * @param string          $property
     */
    protected function setUrlWithReflection(Mapping $mapping, ReflectionClass $mappingClass, $property)
    {
        $mappingProperty = $mappingClass->getProperty($property);
        $mappingProperty->setAccessible(true);
        $value = $mappingProperty->getValue($mapping);
        if (!empty($value['name'])) {
            $route = $this->calculateRoute($value);
            $mappingProperty->setValue($mapping, $route);
        }
    }
    /**
     * @param ReflectionClass $mappingClass
     * @param                 $mapping
     */
    protected function setJsonApiRelationships(ReflectionClass $mappingClass, $mapping)
    {
        $mappingProperty = $mappingClass->getProperty('relationshipSelfUrl');
        $mappingProperty->setAccessible(true);

        $relationshipSelfUrl = (array) $mappingProperty->getValue($mapping);
        if (!empty($relationshipSelfUrl)) {
            foreach ($relationshipSelfUrl as &$urlMember) {
                if (!empty($urlMember)) {
                    foreach ($urlMember as &$url) {
                        if (!empty($url['name'])) {
                            $url = $this->calculateRoute($url);
                        }
                    }
                }
            }
        }
        $mappingProperty->setValue($mapping, $relationshipSelfUrl);
    }
    /**
     * @param array $value
     *
     * @return mixed|string
     */
    protected function calculateRoute(array $value)
    {
        $route = urldecode(route($value['name']));
        if (!empty($value['as_id'])) {
            preg_match_all('/{(.*?)}/', $route, $matches);
            $route = str_replace($matches[0], '{' . $value['as_id'] . '}', $route);
        }
        return $route;
    }
}