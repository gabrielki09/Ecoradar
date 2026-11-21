<?php

namespace App\Http\Resources;

use App\Enum\ProfileTypes;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'profile_type' => $this->profile_type,
            'onboarding_completed' => $this->onboarding_completed_at !== null,
            'companies' => $this->profile_type === ProfileTypes::COMPANY
                ? $this->companies()
                    ->withPivot('role')
                    ->get(['companies.id', 'companies.company_name'])
                    ->map(fn ($company) => [
                        'id' => $company->id,
                        'company_name' => $company->company_name,
                        'role' => $company->pivot->role,
                    ])
                    ->values()
                : [],
        ];
    }
}
