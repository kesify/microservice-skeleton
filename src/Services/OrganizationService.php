<?php

namespace Kesify\MicroserviceSkeleton\Services;

use App\DTO\OrganizationData;
use App\Mail\InviteToOrganizationMail;
use App\Models\Organization;
use App\Models\OrganizationAddress;
use App\Models\OrganizationDatabase;
use App\Models\OrganizationUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class OrganizationService
{
    public ?string $organizationId;
    public function __construct($organizationId = null)
    {
        $this->organizationId = $organizationId;
    }

    public function create(OrganizationData $organizationData )
    {
        $organization = Organization::create($organizationData->toArray());
        if(!$organization){
            return null;
        }

        $organizationId = $organization['id'];

        $userId = Auth::id();
        if($userId) {
            $this->assignUserToOrganization($userId, $organizationId);
        }

        //Create database
        $organizationDatabase = $this->createDatabase($organizationId);
        if(!$organizationDatabase){
            return null;
        }
        $this->setOrganizationDatabase($organizationDatabase->db_name);
        $this->migrateOrganizationByDatabase($organizationDatabase->db_name);
        Redis::set("organization_seed_data", json_encode(['organization_id' => $organizationId,'user_id' => Auth::id()]));
        $this->seedOrganizationByDatabase($organizationDatabase->db_name);
        Redis::del("organization_seed_data");

        //Add addresses
        if (!empty($requestdata['addresses'])) {
            foreach ($requestdata['addresses'] as $index => $address) {
                $address['organization_id'] = $organizationId;
                $address['is_standard'] = ($index === 0); // True for the first item, false otherwise

                OrganizationAddress::create($address);
            }
        }

        $fileStorageService = new \App\Services\FileStorageService();
        if($organizationData->logoLightId) {
            $fileStorageService->move($organizationData->logoLightId,'organization-logo-light-tmp','organization-logo-light',['organization_id'=>$organizationId]);
        }
        if($organizationData->logoDarkId) {
            $fileStorageService->move($organizationData->logoDarkId,'organization-logo-dark-tmp','organization-logo-dark',['organization_id'=>$organizationId]);
        }

        return $organization;
    }

    /**
     * @throws \Exception
     */
    public function update(array $organizationData, $organizationId = null )
    {
        $organizationId = $organizationId ?? $this->organizationId;
        if($organizationId){
            $organization = Organization::find($organizationId);
            if(!$organization){
                return null;
            }
            $organization->update($organizationData);

            if (!empty($requestdata['addresses'])) {
                foreach ($requestdata['addresses'] as $address) {
                    OrganizationAddress::update($address,$address['id']);
                }
            }

            $fileStorageService = new \App\Services\FileStorageService();
            if (($organizationData['logo_light_id'] ?? null) && $organization->logo_light_id != $organizationData['logo_light_id']) {
                $fileStorageService->move($organizationData['logo_light_id'],'organization-logo-light',['organization_id'=>$organizationId]);
            }
            if (($organizationData['logo_dark_id'] ?? null) && $organization->logo_dark_id != $organizationData['logo_dark_id']) {
                $fileStorageService->move($organizationData['logo_dark_id'],'organization-logo-light',['organization_id'=>$organizationId]);
            }

            return $organization;
        }

        return null;
    }

    public function softDelete($organizationId = null)
    {
        $organizationId = $organizationId ?? $this->organizationId;
        if($organizationId){
            OrganizationUser::where(['organization_id'=>$organizationId])->update(['active'=>false]);
            return Organization::where('id',$organizationId)->update(['deleted'=>true]);
        }

        return null;
    }

    public function hardDelete($organizationId = null)
    {
        $organizationId = $organizationId ?? $this->organizationId;
        if($organizationId){
            $organization = Organization::find($organizationId);
            if(!$organization){
                return null;
            }

            DB::statement("DROP DATABASE IF EXISTS `{$organization['database']}`");
            OrganizationAddress::where(['organization_id'=>$organizationId])->delete();
            OrganizationDatabase::where(['organization_id'=>$organizationId])->delete();
            OrganizationUser::where(['organization_id'=>$organizationId])->delete();
            return Organization::where('id',$organizationId)->delete();
        }

        return null;
    }

    public function getOrganization($organizationId = null)
    {
        $organizationId = $organizationId ?? $this->organizationId;
        if($organizationId){
            return Organization::findOrFail($organizationId);
        }
        return null;
    }

    public function getAssignedOrganizations($userId = null)
    {
        $assignedOrganizations = OrganizationUser::where(['user_id'=>$userId??Auth::id(),'active'=>true])->get();
        return $assignedOrganizations;
    }

    public function assignUserToOrganization($userId = null,$organizationId = null,$isActive = true){
        return $userId&&$organizationId ? OrganizationUser::updateOrCreate([
            'user_id' => $userId ,
            'organization_id'=>$organizationId,
        ],[
            'user_id'=>$userId,
            'organization_id'=>$organizationId,
            'active'=>$isActive,
        ]):null;
    }

    public function activateUserToOrganization($userId = null,$organizationId = null ){
        if(!$userId || !$organizationId) return null;

        $organizationUser = OrganizationUser::where(['user_id'=>$userId,'organization_id'=>$organizationId])->first();

        if(!$organizationUser) return null;

        $organizationUser->update(['active'=>true]);

        return $organizationUser;
    }

    public function deactivateUserToOrganization($userId = null,$organizationId = null ){
        if($userId && $organizationId) return null;

        $organizationUser = OrganizationUser::where(['user_id'=>$userId,'organization_id'=>$organizationId])->get();

        if(!$organizationUser) return null;

        $organizationUser->update(['active'=>false]);

        return $organizationUser;
    }


    public function inviteUserToOrganization($email = null,$organizationId = null){
        $organizationId = $organizationId ?? $this->organizationId;

        if (!$email || !$organizationId) return null;

        //When its already invited
        $invitation = json_decode(Redis::get("inviteToOrganization_{$email}"),true);
        if ($invitation && $invitation['organization_id'] === $organizationId) return null;

        $user = User::where('email',$email)->first();
        if(!$user)
            $user = User::create(['email'=>$email]);

        $organization = Organization::where('id',$organizationId)->first();
        if(!$organization || !$organization->active)
            return null;

        if(!OrganizationUser::where(['user_id'=>$user->id,'organization_id'=>$organizationId])->first())
            $this->assignUserToOrganization($user->id,$organizationId,false);

        $codeService = new \App\Services\CodeService();
        $codeService->userId = $user ? $user->id:null;
        $codeService->activeTill = Carbon::now()->addDay();
        $now = Carbon::now();
        $codeService->activeTill = $now->copy()->addMinutes(30);
        $expirySeconds = (int)round($now->diffInSeconds($codeService->activeTill));
        $code = $codeService->createCodeInToken('inviteToOrganization');

        $subject = 'Invited to '.$organization->name;

        $data = [
            'subject' => $subject,
            'greeting' => $subject,
            'bodyText' => "You have been invited to {$organization->name}. Please confirm your email address by clicking the button below",
            'email' => $email,
            'register_url' => getenv('APP_FRONTEND_ORIGIN').'/auth/invite/'.$code->linkToken,
        ];
        Mail::to($email)->send(new InviteToOrganizationMail($data));

        Redis::set('inviteToOrganization_'.$email, json_encode(['email'=>$email,'organization_id'=>$organizationId,'user_id'=>$user->id]),'EX',$expirySeconds);

        return $user;
    }

    private static function createDatabase($oranization_id = false){
        $databaseUser = getenv('DB_USERNAME');
        $databaseConnection = 'organization';
        $databaseName = "organization_".bin2hex(random_bytes(6));
        $charset = \App\Services\config("database.connections." . $databaseConnection . ".charset", 'utf8mb4');
        $collation = \App\Services\config("database.connections." . $databaseConnection . ".collation", 'utf8mb4_unicode_ci');

        $grantPrivilegesQuery = "GRANT ALL PRIVILEGES ON $databaseName.* TO '$databaseUser'@'%';";
        DB::statement($grantPrivilegesQuery);

        $createDatabaseQuery = "CREATE DATABASE IF NOT EXISTS $databaseName CHARACTER SET $charset COLLATE $collation";
        DB::statement($createDatabaseQuery);

        $organization_database = false;
        if($createDatabaseQuery){
            $values = ['db_name'=>$databaseName];
            if($oranization_id)
                $values['organization_id'] = $oranization_id;

            $organization_database = OrganizationDatabase::create($values);
        }

        return $organization_database;
    }


    public function setOrganizationDatabase($db): ?true
    {
        if($db){
            \App\Services\config(['database.connections.organization.database' => $db]);
            DB::purge('organization'); // Purges the current connection
            DB::reconnect('organization'); // Reconnects using the modified config
            DB::connection('organization')->setPdo(null);
            DB::setDatabaseName($db);
            return true;
        }
        return null;
    }

    public function uploadLogo($file,$type,$organizationId = null): ?array
    {
        $organizationId = $organizationId ?? $this->organizationId;
        if($file && $type && $organizationId){
            $fileStorageService = new \App\Services\FileStorageService();
            return $fileStorageService->store($file, $type === 'light' ? 'organization-logo-light':'organization-logo-dark');
        }

        return null;
    }

    public function uploadLogoTmp($file,$type): ?array
    {
        if($file && $type){
            $fileStorageService = new \App\Services\FileStorageService();
            return  $fileStorageService->store($file, $type === 'light' ? 'organization-logo-light-tmp':'organization-logo-dark-tmp');
        }

        return null;
    }

    public function setOrganizationId($organizationId): void
    {
        $this->organizationId = $organizationId;
    }

    public function migrateAllOrganizations(): void
    {
        $organizations = Organization::all();
        foreach($organizations as $organization){
            $this->migrateOrganizationByDatabase($organization['database']);
        }
    }

    public function seedAllOrganizations(): void
    {
        $organizations = Organization::all();
        foreach($organizations as $organization){
            $this->seedOrganizationByDatabase($organization['database']);
        }
    }

    public function migrateOrganizationByOrganizationId($organizationId = null): ?int
    {
        if($organizationId){
            $organization = $this->getOrganization($organizationId);
            $this->setOrganizationDatabase($organization['database']);
            return Artisan::call('migrate',[
                '--path'=>'/database/migrations/organization',
                '--database'=>'organization',
            ]);
        }

        return null;
    }

    public function migrateOrganizationByDatabase($database = null): ?int
    {
        if($database){
            $this->setOrganizationDatabase($database);
            return Artisan::call('migrate',[
                '--path'=>'/database/migrations/organization',
                '--database'=>'organization',
            ]);
        }
        return null;
    }

    public function seedOrganizationByOrganizationId($organizationId = null): ?int
    {
        if($organizationId && class_exists('Database\Seeders\OrganizationDatabaseSeeder')){
            $organization = $this->getOrganization($organizationId);
            $this->setOrganizationDatabase($organization['database']);
            return Artisan::call('db:seed',[
                '--class'=>'Database\Seeders\OrganizationDatabaseSeeder',
            ]);
        }
        return null;
    }

    public function seedOrganizationByDatabase($database = null): ?int
    {
        if($database && class_exists('Database\Seeders\OrganizationDatabaseSeeder')){
            $this->setOrganizationDatabase($database);
            return Artisan::call('db:seed',[
                '--class'=>'Database\Seeders\OrganizationDatabaseSeeder',
            ]);
        }
        return null;
    }

    public function switchOrganization($organizationId = null): ?array
    {
        $organizationId = $organizationId ?? $this->organizationId;
        $token = \App\Services\config('session.token');
        $organization = $this->getOrganization($organizationId);

        if($token && $organization){
            $key = "at_{$token}_organization";
            $value = json_decode(Redis::get($key),true);
            $organizationUser = OrganizationUser::where(['organization_id'=>$organizationId,'user_id'=>$value['user_id']])->first();
            $ttl = Redis::ttl($key);
            Redis::set($key,json_encode($organizationUser),'EX',$ttl);

            return [
                'organization' => $organization,
                'ttl' => $ttl,
            ];
        }

        return null;
    }
}
