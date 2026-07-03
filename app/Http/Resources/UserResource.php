<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The authenticated user shape for the mobile API. Carries everything the app
 * needs to branch its UI by role (internal staff vs client, manager, PM).
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role instanceof \BackedEnum ? $this->role->value : (string) $this->role,
            'type' => $this->type,                                     // internal | client
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'is_internal' => $this->isInternal(),
            'is_client' => $this->isClient(),
            'is_manager' => $this->isManager(),
            'is_project_manager' => $this->isProjectManager(),
            'impact_points' => (int) $this->impact_points,
        ];
    }
}
