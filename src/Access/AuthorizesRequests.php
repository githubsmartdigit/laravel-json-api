<?php
/**
 * Copyright 2018 xooxx.dev@gmail.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Xooxx\Laravel\JsonApi\Access;

use \Xooxx\Laravel\Access\Contracts;

trait AuthorizesRequests
{
    /**
     * Authorize a given action for the current user.
     *
     * @param  mixed $ability
     * @param  mixed|array $arguments
     *
     * @throws \Xooxx\JsonApi\Server\Actions\Exceptions\ForbiddenException
     */
    public function authorize($ability, $arguments = [])
    {
        list($ability, $arguments) = $this->parseAbilityAndArguments($ability, $arguments);

        /** @var \Xooxx\Laravel\Access\Contracts\Gate $gate */
        $gate = app(Contracts\Gate::class);
        $gate->authorize($ability, $arguments);
    }

    /**
     * Authorize a given action for a user.
     *
     * @param $user
     * @param  mixed $ability
     * @param  mixed|array $arguments
     * @throws \Xooxx\JsonApi\Server\Actions\Exceptions\ForbiddenException
     */
    public function authorizeForUser($user, $ability, $arguments = [])
    {
        list($ability, $arguments) = $this->parseAbilityAndArguments($ability, $arguments);

        /** @var \Xooxx\Laravel\Access\Contracts\Gate $gate */
        $gate = app(Contracts\Gate::class);
        $gate->forUser($user)->authorize($ability, $arguments);
    }

    /**
     * Guesses the ability's name if it wasn't provided.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return array
     */
    protected function parseAbilityAndArguments($ability, $arguments)
    {
        if (is_string($ability) && strpos($ability, '\\') === false) {
            return [$ability, $arguments];
        }
        $method = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];
        return [$this->normalizeGuessedAbilityName($method), $ability];
    }
    /**
     * Normalize the ability name that has been guessed from the method name.
     *
     * @param  string  $ability
     * @return string
     */
    protected function normalizeGuessedAbilityName($ability)
    {
        $map = $this->resourceAbilityMap();
        return isset($map[$ability]) ? $map[$ability] : $ability;
    }

    /**
     * Get the map of resource methods to ability names.
     *
     * @return array
     */
    protected function resourceAbilityMap()
    {
        return [
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'update',
            'update' => 'update',
            'destroy' => 'delete',
        ];
    }

}