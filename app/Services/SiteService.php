<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\SiteRepositoryInterface;
use App\Models\Site;

class SiteService extends BaseService
{
    public function __construct(
        protected SiteRepositoryInterface $siteRepository
    ) {}

    /**
     * Create a new site record.
     *
     * @param  array<string, mixed>  $data
     */
    public function createSite(array $data): Site
    {
        return $this->executeInTransaction(function () use ($data): Site {
            /** @var Site */
            return $this->siteRepository->create($data);
        });
    }

    /**
     * Update an existing site record.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateSite(Site $site, array $data): Site
    {
        return $this->executeInTransaction(function () use ($site, $data): Site {
            $this->siteRepository->update($site->id, $data);

            return $site->refresh();
        });
    }

    /**
     * Delete a site record.
     */
    public function deleteSite(Site $site): bool
    {
        return $this->executeInTransaction(function () use ($site): bool {
            return $this->siteRepository->delete($site->id);
        });
    }
}
