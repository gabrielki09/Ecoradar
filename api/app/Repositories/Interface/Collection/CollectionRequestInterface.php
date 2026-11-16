<?php

namespace App\Repositories\Interface\Collection;

use App\Repositories\Interface\BaseInterface;

interface CollectionRequestInterface extends BaseInterface
{
    public function allForRequester(int $requesterId, array $parameters = []);

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
