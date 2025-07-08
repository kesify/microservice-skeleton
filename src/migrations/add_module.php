<?php

use Illuminate\Database\Migrations\Migration;
use Kesify\MicroserviceSkeleton\Enums\ModuleStatus;
use Kesify\MicroserviceSkeleton\Models\Microservice;
use Kesify\MicroserviceSkeleton\Models\Module;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $imageLight = storage_path('app/public/images/icon-light.svg');
        $imageDark = storage_path('app/public/images/icon-dark.svg');

        $ms = Microservice::where('name', 'here_ms_name')->first();

        $module = Module::updateOrCreate(
            [
                'key' => 'vehicle-dealer'
            ],
            [
                'key' => 'here_module_name',
                'label' => 'here_label',
                'description' => 'here_description',
                'status' => ModuleStatus::INACTIVE,
                'image_light_id' => null,
                'image_dark_id' => null,
                'stripe_product_id' => null,
                'metadata' => [
                    'microservices' => [
                        [ 'id' => $ms->id , 'name'=> $ms->name, 'installable' => true ],
                    ],
                    'features' => [
                        [ 'id' => 'f1', 'title' => 'here_feature', 'is_active' => true ],
                    ],
                    'frontendModules' => ['here_frontend_module_names'],
                    'badge' => [
                        'type' => 'success',
                        'label' => 'new',
                    ]
                ]
            ]
        );

        try {
            $imageDarkFile = FileStorageService()->store($imageDark, 'module-image');
            $imageLightFile = FileStorageService()->store($imageLight, 'module-image');
        } catch (\RuntimeException $e) {
            dd($e->getMessage());
        }

        if($imageDarkFile && $imageLightFile){
            $module->image_light_id = $imageLightFile['id'];
            $module->image_dark_id = $imageDarkFile['id'];
            $module->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       //
    }
};
