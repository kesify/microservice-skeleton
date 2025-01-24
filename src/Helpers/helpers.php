<?php

use Illuminate\Container\Container;

if (!function_exists('OrganizationService')) {

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    function OrganizationService()
    {
        return Container::getInstance()->make('OrganizationService');
    }
}
