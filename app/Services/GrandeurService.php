<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\GrandeurType;
use App\Interfaces\GrandeurRepositoryInterface;
use App\Models\EquipmentSpecification;
use App\Models\Grandeur;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class GrandeurService extends BaseService
{
    public function __construct(
        protected GrandeurRepositoryInterface $grandeurRepository
    ) {}

    /**
     * Get paginated and filtered quantities and units.
     */
    public function getPaginatedGrandeurs(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $filters = $request->only(['search', 'q', 'type']);

        return $this->grandeurRepository->paginateWithFilter($filters, $perPage);
    }

    /**
     * Create a new physical quantity and unit.
     *
     * @param  array<string, mixed>  $data
     */
    public function storeGrandeur(array $data): Grandeur
    {
        return $this->executeInTransaction(function () use ($data): Grandeur {
            /** @var Grandeur */
            return $this->grandeurRepository->create([
                'name' => trim((string) $data['name']),
                'symbol' => trim((string) $data['symbol']),
                'type' => $data['type'],
            ]);
        });
    }

    /**
     * Update an existing physical quantity and unit.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateGrandeur(Grandeur $grandeur, array $data): bool
    {
        return $this->executeInTransaction(function () use ($grandeur, $data): bool {
            return (bool) $this->grandeurRepository->update($grandeur->id, [
                'name' => trim((string) $data['name']),
                'symbol' => trim((string) $data['symbol']),
                'type' => $data['type'],
            ]);
        });
    }

    /**
     * Delete a physical quantity if it is not linked to equipment specifications.
     *
     * @throws \DomainException
     */
    public function deleteGrandeur(Grandeur $grandeur): bool
    {
        if ($this->grandeurRepository->isLinkedToSpecifications($grandeur->id)) {
            throw new \DomainException(__('Cannot delete physical quantity linked to equipment specifications.'));
        }

        return $this->executeInTransaction(function () use ($grandeur): bool {
            return (bool) $this->grandeurRepository->delete($grandeur->id);
        });
    }

    /**
     * Get aggregate statistics for the units explorer KPI cards.
     *
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->grandeurRepository->all()->count(),
            'measurement' => $this->grandeurRepository->countByType(GrandeurType::Measurement),
            'source' => $this->grandeurRepository->countByType(GrandeurType::Source),
            'linked_specs' => EquipmentSpecification::count(),
        ];
    }
}
