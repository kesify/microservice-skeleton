<?php

namespace Kesify\MicroserviceSkeleton\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class MicroserviceController extends Controller
{
    private $organization;

    public function install(): \Illuminate\Http\JsonResponse
    {
        $this->organization = App::bound('organization') ? App::get('organization') : null;

        if($this->organization){
            $this->runMigration();
            $this->runOrganizationMigration();

            return $this->apiResponse([
                'success'=> true,
                'message'=>'Microservice installation successful'
            ]);
        }

        return $this->apiResponse([
            'success' => false,
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
