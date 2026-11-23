<?php

namespace App\Repositories\Interface\Collection;

use App\Models\Collector\CollectorProfile;
use App\Models\Company\Company;
use App\Repositories\Interface\BaseInterface;

interface CollectionPointInterface extends BaseInterface
{
    public function allForProvider(CollectorProfile|Company $provider, array $parameters = []);
    public function allPublic(array $parameters = []);
}
