<?php

namespace App\Http\Resources;

use App\Models\Collection\RewardEntry;
use App\Support\CollectionAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RewardEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isRequester = $this->beneficiary_type === RewardEntry::USER
            && $this->beneficiary_id === $this->requester_user_id;

        return [
            'collection_request_id' => $this->collection_request_id,
            'points' => $this->points,
            'reason' => $this->reward_type,
            'description' => $isRequester ? 'Coleta da sua solicitação concluída' : 'Recompensa por coleta realizada',
            'created_at' => $this->created_at,
            'can_view_collection' => $isRequester || CollectionAccess::provider($request->user()),
        ];
    }
}
