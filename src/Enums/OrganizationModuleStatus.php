<?php

namespace Kesify\MicroserviceSkeleton\Enums;

enum OrganizationModuleStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DELETED = 'deleted';
    case  BLOCKED = 'blocked';
}
