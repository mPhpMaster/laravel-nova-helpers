<?php
/*
 * Copyright © 2023. mPhpMaster(https://github.com/mPhpMaster) All rights reserved.
 */

 if( !function_exists('toCollect') ) {
    /**
     * Returns $var as collection if it wasn't collection
     *
     * @param mixed $var
     *
     * @return \Illuminate\Support\Collection
     */
    function toCollect($var): \Illuminate\Support\Collection
    {
        return is_collection($var) ? $var : collect($var);
    }
}

if( !function_exists('currentLocale') ) {
    /**
     * return app locale
     *
     * @param bool $full
     *
     * @return string
     */
    function currentLocale($full = false): string
    {
        if( $full )
            return (string) app()->getLocale();

        $locale = str_replace('_', '-', app()->getLocale());
        $locale = current(explode("-", $locale));

        return $locale ?: "";
    }
}

if( !function_exists('formatAttributeAsCurrency') ) {
    /**
     * @param string|\Closure            $attribute
     * @param \App\Models\Model|\Closure $model
     * @param string|\Closure|null       $locale Default: currentLocale()
     *
     * @return string
     */
    function formatAttributeAsCurrency($attribute, $model, $locale = null)
    {
        $attribute = value($attribute);
        $model = value($model);
        $locale = value($locale, currentLocale());

        return \Laravel\Nova\Fields\Currency::make('Element', $attribute)
                                            ->formatMoney($model->$attribute, null, $locale);
    }
}

if( !function_exists('formatValueAsCurrency') ) {
    /**
     * @param string|\Closure      $value
     * @param string|\Closure|null $locale Default: config(nova.money_locale)
     *
     * @return string
     */
    function formatValueAsCurrency($value, $locale = null)
    {
        $value = value($value);
        $locale = value($locale, currentLocale());
        $locale ??= config('nova.money_locale', currentLocale());

        return \Laravel\Nova\Fields\Currency::make('Element', $value)
                                            ->formatMoney($value, null, $locale);
    }
}

if( !function_exists('makeFormatValueAsCurrency') ) {
    /**
     * @return \Closure
     */
    function makeFormatValueAsCurrency(): Closure
    {
        return static fn($v, $l = null) => formatValueAsCurrency($v, $l);
    }
}

if( !function_exists('includeIfExists') ) {
    /**
     * Include file if exist
     *
     * @param string              $file
     * @param callable|mixed|null $when_not_exists
     *
     * @return null|mixed
     */
    function includeIfExists($file, $when_not_exists = null)
    {
        return file_exists($file) ? include($file) : getValue($when_not_exists);
    }
}

if( !function_exists('includeOnceIfExists') ) {
    /**
     * Include file Once if exist
     *
     * @param string              $file
     * @param callable|mixed|null $when_not_exists
     * @param callable|mixed|null $when_already_included
     *
     * @return bool|mixed
     */
    function includeOnceIfExists($file, $when_not_exists = null, $when_already_included = null)
    {
        if( file_exists($file) ) {
            if( ($return = include_once($file)) === true ) {
                $return = isClosure($when_already_included) ? getValue($when_already_included, ...[ $file ]) : $when_already_included;
            }
        } else {
            $return = $when_not_exists = isClosure($when_not_exists) ? getValue($when_not_exists, ...[ $file ]) : $when_not_exists;
        }

        return getValue($return, ...[ $file ]);
    }
}