<?php

namespace Kesify\MicroserviceSkeleton\Http\Middleware;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Kesify\MicroserviceSkeleton\Services\OrganizationService;
use Laravel\Passport\Exceptions\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class SetOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     * @throws BindingResolutionException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorization =  $request->headers->get('Authorization');
        if(!empty($authorization)){
            $token = str_replace("Bearer ",'',$authorization);
            Config::set('session.token', $token);

            $organizationUserRelation = json_decode(Redis::get('at_'.$token.'_organization'),true);
            if ($organizationUserRelation) {
                $organization = OrganizationService()->getOrganization($organizationUserRelation['organization_id']);
                OrganizationService()->setOrganizationDatabase($organizationUserRelation['database']);
                App::instance('organizationUserRelation', $organizationUserRelation);
                App::instance('organization', $organization);
                setPermissionsTeamId($organization->id);
            }else{
                throw new AuthenticationException('Unauthenticated.');
            }
        }

        return $next($request);
    }
}
