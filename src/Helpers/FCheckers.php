<?php
/*
 * Copyright © 2023. mPhpMaster(https://github.com/mPhpMaster) All rights reserved.
 */

if( !function_exists('isCurrentResource') ) {
    /**
     * Check if current resource is the given resource.
     *
     * @param string $resource Nova resource class FQN
     *
     * @return bool
     */
    function isCurrentResource(string $resource): bool
    {
        return ($currentResource = request('view')) &&
            class_exists($resource) &&
            method_exists($resource, 'uriKey') &&
            $currentResource === 'resources/' . $resource::uriKey();
    }
}

if( !function_exists('isClosure') ) {
    /**
     * Check if the given var is Closure.
     *
     * @param mixed|null $closure
     *
     * @return bool
     */
    function isClosure($closure): bool
    {
        return $closure instanceof Closure;
    }
}
