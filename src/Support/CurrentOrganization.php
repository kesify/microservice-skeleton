<?php

namespace Kesify\MicroserviceSkeleton\Support;

use Illuminate\Http\Request;

class CurrentOrganization
{
    public function __construct(private Request $request) {}

    public function id(): ?string
    {
        return $this->request->attributes->get('organization_id');
    }

    public function userId(): ?string
    {
        return $this->request->attributes->get('organization_user_id');
    }

    public function toArray(): array
    {
        return $this->request->attributes->get('organization', []);
    }

    public function override(array $ctx): void
    {
        $this->request->attributes->add([
            'organization'        => $ctx,
            'organization_id'     => $ctx['organization_id'] ?? null,
            'organization_user_id'=> $ctx['user_id'] ?? null,
        ]);
    }
    public function requireId(): string
    {
        $id = $this->id();
        if (!$id) {
            abort(428, 'Organization required');
        }
        return $id;
    }
}
