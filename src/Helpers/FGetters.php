<?php
/*
 * Copyright © 2023. mPhpMaster(https://github.com/mPhpMaster) All rights reserved.
 */

 if( !function_exists('getNovaRequest') ) {
    /**
     * Returns NovaRequest object.
     * 
     * @return \Illuminate\Contracts\Foundation\Application|\Laravel\Nova\Http\Requests\NovaRequest|mixed
     */
    function getNovaRequest()
    {
        return app(\Laravel\Nova\Http\Requests\NovaRequest::class);
    }
}

 if( !function_exists('getNovaResourceId') ) {
    /**
     * Get $request->resourceId value
     * 
     * @param \Illuminate\Http\Request|null $request
     *
     * @return int|double|string|mixed|null
     */
    function getNovaResourceId(\Illuminate\Http\Request $request = null)
    {
        $resourceId = ($request ?? request())->resourceId;
        if( is_numeric($resourceId) ) {
            $resourceId += 0;
        }

        return $resourceId;
    }
}

if( !function_exists('getNovaParentResource') ) {
    /**
     * Returns the parent resource for which the current item is being created.
     * Get $request->viaResource value
     *
     * @param \Illuminate\Http\Request|null $request
     *
     * @return string|null
     */
    function getNovaParentResource(\Illuminate\Http\Request $request = null): ?string
    {
        return ($request ?? request())->viaResource;
    }
}

if( !function_exists('getNovaParentResourceId') ) {
    /**
     * Returns the parent resource id for which the current item is being created.
     * Get $request->viaResourceId value
     *
     * @param \Illuminate\Http\Request|null $request
     *
     * @return int|double|string|mixed|null
     */
    function getNovaParentResourceId(\Illuminate\Http\Request $request = null)
    {
        $resourceId = ($request ?? request())->viaResourceId;
        if( is_numeric($resourceId) ) {
            $resourceId += 0;
        }

        return $resourceId;
    }
}

if( !function_exists('getNovaResources') ) {
    /**
     * Get All nova resources.
     *
     * @param string $app_dir
     * @param string $parent_class Mine is \App\Nova\Resource::class
     *
     * @return array
     */
    function getNovaResources(string $app_dir = 'Nova', string $parent_class = \Laravel\Nova\Resource::class): array
    {
        $resources = glob(app_path($app_dir) . DIRECTORY_SEPARATOR . '*.php');

        return toCollect($resources)
            ->map(function($resource) use ($parent_class) {
                $resource_class = str_ireplace(
                    [ "/", ".php" ],
                    [ "\\", "" ],
                    "App" . str_after($resource, app_path())
                );
                $is_nova_resource =
                    $resource_class !== $parent_class && class_exists($resource_class) && is_subclass_of(
                        $resource_class,
                        $parent_class
                    );

                return $is_nova_resource ? $resource_class : null;
            })
            ->filter()
            ->toArray();
    }
}

if( !function_exists('getNovaResourcesAsOptions') ) {
    /**
     * Get All nova resources as options.
     *
     * @param string $app_dir
     * @param string $parent_class
     *
     * @return array
     */
    function getNovaResourcesAsOptions(
        string $app_dir = 'Nova',
        string $parent_class = \Laravel\Nova\Resource::class
    ): array {
        return collect(getNovaResources($app_dir, $parent_class))
            ->filter(fn($f) => class_exists($f) && is_subclass_of($f, $parent_class))
            ->mapWithKeys(fn($r) => [
                /** @var \Laravel\Nova\Resource $r */
                $r::singularLabel() => $r::singularLabel() . ' - ' . $r::label(),
            ])
            ->toArray();
    }
}

if( !function_exists('getNovaUrlPath') ) {
    /**
     * @param string|\Closure $config_path
     *
     * @return string
     */
    function getNovaUrlPath(string|\Closure $config_path): string
    {
        $config_path = (string) value($config_path);

        return (string) config('nova.path', $config_path ? data_get(array_wrap(includeIfExists($config_path)), 'path') : "");
    }
}

if( !function_exists('getValue') ) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @param mixed ...$args
     *
     * @return mixed
     * @see \value()
     */
    function getValue($value, ...$args): mixed
    {
        return is_callable($value) && !is_string($value) ? $value(...$args) : value($value, ...$args);
    }
}

if( !function_exists('getNovaResourceInfoFromRequest') ) {
    /**
     * @param \Illuminate\Http\Request|null $request
     * @param string|null                   $key
     *
     * @return array|string|null|mixed
     */
    function getNovaResourceInfoFromRequest(?\Illuminate\Http\Request $request = null, ?string $key = null)
    {
        $resourceInfo = [
            'resource' => null,
            'resourceName' => null,
            'resourceId' => null,
            'mode' => null,
        ];

        try {
            $request ??= getNovaRequest();

            if( $request->segment(2) === 'resources' ) {
                $resourceInfo = [
                    'resource' => Nova::resourceForKey($resourceName = $request->segment(3)),
                    'resourceName' => $resourceName,
                    'resourceId' => $resourceId = $request->segment(4),
                    'mode' => $request->segment(5) ?? ($resourceId ? 'view' : 'index'),
                ];
            }
        } catch(Exception $exception) {
        }

        return is_null($key) ? $resourceInfo : data_get($resourceInfo, $key);
    }
}

if( !function_exists('getNovaRequestParameters') ) {
    /**
     * @param \Illuminate\Http\Request|null $request
     * @param array|string|null             $key
     *
     * @return array|object|string|null|mixed
     */
    function getNovaRequestParameters(?\Illuminate\Http\Request $request = null, $key = null)
    {
        $results = [];
        try {
            $request ??= getNovaRequest();

            /** @var \Illuminate\Routing\Route $route */
            $route = call_user_func($request->getRouteResolver());

            if( is_null($route) ) {
                return $results;
            }

            $results = $route->parameters();
            if( is_null($key) ) {
                if( is_array($results) && isset($results[ 'resource' ]) ) {
                    $results[ 'resource_class' ] = Nova::resourceForKey($results[ 'resource' ]);
                    $results[ 'resource_model' ] = Nova::modelInstanceForKey($results[ 'resource' ]);
                    $results[ 'model' ] = fn() => isset($results[ 'resourceId' ]) && isset($results[ 'resource_model' ]) && class_exists($results[ 'resource_model' ]) ?
                        $results[ 'resource_model' ]::find($results[ 'resourceId' ]) : null;
                }
            } else {
                $key = (array) $key;
                $results = blank($key) ? $results : array_only($results, $key);
            }

        } catch(Exception $exception) {
        }

        return $results;
    }
}

if( !function_exists('getNovaResource') ) {
    /**
     * Get current browsing nova resource via link.
     *
     * @return string|null
     */
    function getNovaResource(): ?string
    {
        try {
            $resource = getNovaResourceInfoFromRequest(null, 'resource');
        } catch(Exception $exception) {
            $resource = null;
        }

        return $resource;
    }
}
