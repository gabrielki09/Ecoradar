<?php

namespace App\Models\Collection;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['collection_request_id', 'beneficiary_type', 'beneficiary_id', 'reward_type', 'points'])]
class RewardEntry extends Model
{
    public const USER = 'user';

    public const COMPANY = 'company';

    public const COLLECTION_COMPLETION = 'collection_completion';
}
