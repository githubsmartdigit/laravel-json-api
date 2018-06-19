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

if (!function_exists('config')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function config($key, $default = null)
    {
        /**@var \Illuminate\Config\Repository $request */
        $config = app(\Illuminate\Config\Repository::class);
        return $config->get($key, $default);
    }
}

if (!function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param  array|string  $key
     * @param  mixed   $default
     * @return \Illuminate\Http\Request|string|array
     */
    function request($key = null, $default = null)
    {
        /**@var \Illuminate\Http\Request $request */
        $request = app(\Illuminate\Http\Request::class);

        if (is_null($key)) {
            return $request->instance();
        }
        if (is_array($key)) {
            return  $request->only($key);
        }
        $value = $request->get($key);
        return is_null($value) ? value($default) : $value;
    }
}

if (! function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param  mixed  $value
     * @return \Illuminate\Support\Collection
     */
    function collect($value = null)
    {
        return new Illuminate\Support\Collection($value);
    }
}

if (! function_exists('array_merge_kv')) {
    /**
     * Put array keys and values into a single array
     * @param array $array
     * @return array
     */
    function array_merge_kv(array $array)
    {
        return array_merge(array_keys($array), array_values($array));
    }
}