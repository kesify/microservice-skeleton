<?php

namespace Kesify\MicroserviceSkeleton\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class MicroserviceController extends Controller
{
    private $organization;

    public function install(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->organization = $request->get('organization_id') ?? null;

        if($this->organization){
            $this->runMigration();
            $this->runOrganizationMigration();

            return $this->apiResponse([
                'success'=> true,
                'result'=> true,
                'message'=>'Microservice installation successful'
            ]);
        }

        return $this->apiResponse([
            'success' => false,
            'result'=> false,
            'message'=>'Microservice installation failed'
        ]);
    }

    private function runMigration()
    {
        Artisan::call('migrate');
    }
    private function runOrganizationMigration()
    {
        Artisan::call('migrate', ['--database' => 'organization', '--path' => 'database/migrations/organization']);
    }


}
