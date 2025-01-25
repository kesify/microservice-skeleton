<?php

namespace Kesify\MicroserviceSkeleton\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Kesify\MicroserviceSkeleton\Services\OrganizationService;
use Symfony\Component\HttpFoundation\Response;

class SetOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorization =  $request->headers->get('Authorization');
        if(!empty($authorization)){
            $token = str_replace("Bearer ",'',$authorization);
            Config::set('session.token', $token);

            $organization = json_decode(Redis::get('at_'.$token.'_organization'),true);
            dd($organization);
            if ($organization) {
                App::instance('organization', $organization);
                (new OrganizationService())->setOrganizationDatabase($organization['database']);
                setPermissionsTeamId($organization['organization_id']);
            }
        }

        return $next($request);
    }
}
