<?php
/*
 * Copyright © 2023. mPhpMaster(https://github.com/mPhpMaster) All rights reserved.
 */

 if( !function_exists('HiddenField') ) {
    /**
     * @param string|\Closure     $attribute
     * @param \Closure|mixed|null $value
     *
     * @return \Laravel\Nova\Fields\Hidden
     */
    function HiddenField($attribute, $value = null)
    {
        return \Laravel\Nova\Fields\Hidden::make($attribute = value($attribute), $attribute)->withMeta([ 'value' => value($value) ]);
    }
}
