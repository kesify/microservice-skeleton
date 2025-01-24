<?php

use Illuminate\Container\Container;

if (!function_exists('OrganizationService')) {
    /**
     * Get the OrganizationService instance from the container.
     *
     * @return \Kesify\MicroserviceSkeleton\Services\OrganizationService
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    function OrganizationService(): mixed
    {
        try {
            return Container::getInstance()->make('OrganizationService');
        } catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
            // Optional: Log or handle the exception
            throw $e;
        }
    }
}

if (!function_exists('FileStorageService')) {
    /**
     * Get the FileStorageService instance from the container.
     * @return \Kesify\MicroserviceSkeleton\Services\FileStorageService
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    function FileStorageService(): mixed
    {
        try {
            return Container::getInstance()->make('FileStorageService');
        } catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
            // Optional: Log or handle the exception
            throw $e;
        }
    }
}

if (!function_exists('KeyService')) {
    /**
     * Get the KeyService instance from the container.
     *
     * @return \Kesify\MicroserviceSkeleton\Services\KeyService
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    function KeyService(): mixed
    {
        try {
            return Container::getInstance()->make('KeyService');
        } catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
            // Optional: Log or handle the exception
            throw $e;
        }
    }
}
