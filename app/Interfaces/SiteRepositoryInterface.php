<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Site;

interface SiteRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a site by its site code.
     */
    public function findByCode(string $code): ?Site;
}
