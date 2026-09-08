<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait FilterableTrait
{
    /**
     * Scope a query to apply dynamic filters.
     *
     * @param  Builder<static>  $query
     * @param  array<string, mixed>|Request  $filters
     * @param  list<string>  $allowedFilters
     * @return Builder<static>
     */
    public function scopeFilter(Builder $query, array|Request $filters = [], array $allowedFilters = []): Builder
    {
        $rawFilters = $filters instanceof Request ? $filters->query() : $filters;

        /** @var list<string> $allowed */
        $allowed = ! empty($allowedFilters)
            ? $allowedFilters
            : ($this->filterable ?? []);

        foreach ($rawFilters as $field => $value) {
            if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                continue;
            }

            // 1. Check custom filter hook method: e.g. filterRole(), filterStatus()
            $customMethod = 'filter'.Str::studly((string) $field);
            if (method_exists($this, $customMethod)) {
                $this->{$customMethod}($query, $value);

                continue;
            }

            // 2. Global multi-column keyword search
            if (($field === 'search' || $field === 'q') && is_scalar($value)) {
                $searchable = $this->searchable ?? [];
                if (! empty($searchable)) {
                    $searchTerm = '%'.trim((string) $value).'%';
                    $query->where(function (Builder $q) use ($searchable, $searchTerm): void {
                        foreach ($searchable as $col) {
                            $q->orWhere($col, 'LIKE', $searchTerm);
                        }
                    });
                }

                continue;
            }

            // 3. Dynamic sorting
            if ($field === 'sort_by' && is_string($value)) {
                $direction = strtolower((string) ($rawFilters['sort_direction'] ?? $rawFilters['sort_dir'] ?? 'asc'));
                $validDirection = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';
                if (in_array($value, $allowed, true) || in_array('*', $allowed, true)) {
                    $query->orderBy($value, $validDirection);
                }

                continue;
            }

            if ($field === 'sort_direction' || $field === 'sort_dir') {
                continue;
            }

            // 4. Validate field is in allowed whitelist
            if (! in_array($field, $allowed, true) && ! in_array('*', $allowed, true)) {
                continue;
            }

            // 5. Handle Array (Range or whereIn)
            if (is_array($value)) {
                if (isset($value['from']) || isset($value['to'])) {
                    if (! empty($value['from'])) {
                        $query->where($field, '>=', $value['from']);
                    }
                    if (! empty($value['to'])) {
                        $query->where($field, '<=', $value['to']);
                    }

                    continue;
                }

                if (isset($value['min']) || isset($value['max'])) {
                    if (isset($value['min']) && $value['min'] !== '') {
                        $query->where($field, '>=', $value['min']);
                    }
                    if (isset($value['max']) && $value['max'] !== '') {
                        $query->where($field, '<=', $value['max']);
                    }

                    continue;
                }

                $query->whereIn($field, $value);

                continue;
            }

            // 6. Standard exact equality match
            $query->where($field, $value);
        }

        return $query;
    }
}
