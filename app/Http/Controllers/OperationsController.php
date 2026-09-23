<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Operations\StoreWarrantyRequest;
use App\Http\Requests\Operations\UpdateWarrantyRequest;
use App\Interfaces\WarrantyRepositoryInterface;
use App\Models\Warranty;
use App\Services\WarrantyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OperationsController extends Controller
{
    public function __construct(
        protected WarrantyService $warrantyService,
        protected WarrantyRepositoryInterface $warrantyRepository
    ) {}

    /**
     * Display the Operations & Projects dashboard.
     */
    public function index(): View
    {
        Gate::authorize('view operations');

        return view('operations.index');
    }

    /**
     * Display the Mission Management explorer.
     */
    public function missions(): View
    {
        Gate::authorize('view missions');

        return view('operations.missions');
    }

    /**
     * Display the Contracts explorer.
     */
    public function contracts(): View
    {
        Gate::authorize('view contracts');

        return view('operations.contracts');
    }

    /**
     * Display the Attachments List explorer.
     */
    public function attachments(): View
    {
        Gate::authorize('view attachments');

        return view('operations.attachments');
    }

    /**
     * Display the Bank Guarantees explorer.
     */
    public function warranties(Request $request): View
    {
        Gate::authorize('view warranties');

        $filters = $request->only(['search', 'status', 'type']);
        $warranties = $this->warrantyRepository->paginateWithFilter($filters, 15);

        return view('operations.warranties', compact('warranties'));
    }

    /**
     * Store a new bank guarantee.
     */
    public function storeWarranty(StoreWarrantyRequest $request): RedirectResponse
    {
        $this->warrantyService->createWarranty($request->validated());

        return redirect()
            ->route('operations.warranties', $request->query())
            ->with('success', __('Bank guarantee created successfully.'));
    }

    /**
     * Update an existing bank guarantee.
     */
    public function updateWarranty(UpdateWarrantyRequest $request, Warranty $warranty): RedirectResponse
    {
        $this->warrantyService->updateWarranty($warranty, $request->validated());

        return redirect()
            ->route('operations.warranties', $request->query())
            ->with('success', __('Bank guarantee updated successfully.'));
    }

    /**
     * Delete a bank guarantee.
     */
    public function destroyWarranty(Request $request, Warranty $warranty): RedirectResponse
    {
        Gate::authorize('delete warranties');

        $this->warrantyService->deleteWarranty($warranty);

        return redirect()
            ->route('operations.warranties', $request->query())
            ->with('success', __('Bank guarantee deleted successfully.'));
    }

    /**
     * Display the Classification of Articles explorer.
     */
    public function articleTypes(): View
    {
        Gate::authorize('view article types');

        return view('operations.article-types');
    }
}
