<?php

namespace App\Repositories\Interface\Collection;

use App\Models\Collector\CollectorProfile;
use App\Models\Company\Company;
use App\Repositories\Interface\BaseInterface;

interface CollectionRequestInterface extends BaseInterface
{
    public function mine(int $requesterId, array $parameters = []);
    public function allForProvider(CollectorProfile|Company $provider, array $parameters = []);

    /**
     * @param array<int> $cityIds
     * @param array<int> $categoryIds
     */
    public function availableFor(
        array $cityIds,
        array $categoryIds,
        array $parameters = []
    );
}
