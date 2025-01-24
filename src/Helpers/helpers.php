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

if (!function_exists('FileStorageService')) {
    function FileStorageService()
    {
        return Container::getInstance()->make('FileStorageService');
    }
}

if (!function_exists('KeyService')) {
    function KeyService()
    {
        return Container::getInstance()->make('KeyService');
    }
}
