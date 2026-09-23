<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\GrandeurType;
use App\Http\Requests\Metrology\StoreEquipmentRequest;
use App\Http\Requests\Metrology\StoreGrandeurRequest;
use App\Http\Requests\Metrology\UpdateEquipmentRequest;
use App\Http\Requests\Metrology\UpdateGrandeurRequest;
use App\Interfaces\EquipmentRepositoryInterface;
use App\Models\Equipment;
use App\Models\Grandeur;
use App\Services\EquipmentService;
use App\Services\GrandeurService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MetrologyController extends Controller
{
    public function __construct(
        protected EquipmentService $equipmentService,
        protected EquipmentRepositoryInterface $equipmentRepository,
        protected GrandeurService $grandeurService,
    ) {}

    /**
     * Display the Metrology & Equipments dashboard.
     */
    public function index(): View
    {
        Gate::authorize('view metrology');

        return view('metrology.index');
    }

    /**
     * Display the Measuring Instruments explorer.
     */
    public function instruments(): View
    {
        Gate::authorize('view measuring instruments');

        return view('metrology.instruments');
    }

    /**
     * Display the Equipment explorer.
     */
    public function equipment(Request $request): View
    {
        Gate::authorize('view equipment');

        $equipment = $this->equipmentRepository->paginateWithFilter($request->all(), 15);
        $stats = $this->equipmentService->getStatistics();
        $measurementGrandeurs = Grandeur::where('type', GrandeurType::Measurement)->orderBy('name')->get();
        $sourceGrandeurs = Grandeur::where('type', GrandeurType::Source)->orderBy('name')->get();

        return view('metrology.equipment', compact('equipment', 'stats', 'measurementGrandeurs', 'sourceGrandeurs'));
    }

    /**
     * Display the specified equipment details view.
     */
    public function showEquipment(Equipment $equipment): View
    {
        Gate::authorize('view equipment');

        $equipment->load([
            'specifications.grandeur',
            'grandeurs',
            'activities.causer',
            'calibrationCertificates.calibrationPoints',
        ]);

        return view('metrology.equipment.show', compact('equipment'));

    }

    /**
     * Store a newly created equipment record.
     */
    public function storeEquipment(StoreEquipmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $image = $request->file('image');
        $certificate = $request->file('certificate');
        $params = $request->input('params');

        $this->equipmentService->createEquipment($data, $image, $certificate, $params);

        return redirect()->route('metrology.equipment', $request->query())
            ->with('success', __('Equipment created successfully.'));
    }

    /**
     * Update the specified equipment record.
     */
    public function updateEquipment(UpdateEquipmentRequest $request, Equipment $equipment): RedirectResponse
    {
        $data = $request->validated();
        $image = $request->file('image');
        $certificate = $request->file('certificate');
        $params = $request->input('params');
        $removeImage = (bool) $request->boolean('remove_image');
        $removeCertificate = (bool) $request->boolean('remove_certificate');

        $this->equipmentService->updateEquipment(
            $equipment,
            $data,
            $image,
            $certificate,
            $params,
            $removeImage,
            $removeCertificate
        );

        $redirectUrl = $request->input('_redirect') ?: route('metrology.equipment', $request->query());

        return redirect($redirectUrl)
            ->with('success', __('Equipment updated successfully.'));
    }

    /**
     * Remove the specified equipment from storage.
     */
    public function destroyEquipment(Equipment $equipment): RedirectResponse
    {
        Gate::authorize('delete equipment');

        $this->equipmentService->deleteEquipment($equipment);

        return redirect()->route('metrology.equipment', request()->query())
            ->with('success', __('Equipment deleted successfully.'));
    }

    /**
     * Display the Calibrator Movements explorer.
     */
    public function calibratorMovements(): View
    {
        Gate::authorize('view calibrator movements');

        return view('metrology.calibrator-movements');
    }

    /**
     * Display the Calibration Certificates explorer.
     */
    public function calibrationCertificates(): View
    {
        Gate::authorize('view calibration certificates');

        return view('metrology.calibration-certificates');
    }

    /**
     * Display the Quantities & Units explorer.
     */
    public function units(Request $request): View
    {
        Gate::authorize('view quantities units');

        $grandeurs = $this->grandeurService->getPaginatedGrandeurs($request, 15);
        $stats = $this->grandeurService->getStatistics();

        return view('metrology.units', compact('grandeurs', 'stats'));
    }

    /**
     * Store a newly created physical quantity and unit.
     */
    public function storeUnit(StoreGrandeurRequest $request): RedirectResponse
    {
        $this->grandeurService->storeGrandeur($request->validated());

        return redirect()->route('metrology.units', $request->query())
            ->with('success', __('Quantity and unit created successfully.'));
    }

    /**
     * Update the specified physical quantity and unit.
     */
    public function updateUnit(UpdateGrandeurRequest $request, Grandeur $grandeur): RedirectResponse
    {
        $this->grandeurService->updateGrandeur($grandeur, $request->validated());

        return redirect()->route('metrology.units', $request->query())
            ->with('success', __('Quantity and unit updated successfully.'));
    }

    /**
     * Remove the specified physical quantity and unit.
     */
    public function destroyUnit(Grandeur $grandeur): RedirectResponse
    {
        Gate::authorize('delete quantities units');

        try {
            $this->grandeurService->deleteGrandeur($grandeur);

            return redirect()->route('metrology.units', request()->query())
                ->with('success', __('Quantity and unit deleted successfully.'));
        } catch (\DomainException $e) {
            return redirect()->route('metrology.units', request()->query())
                ->with('error', $e->getMessage());
        }
    }
}
