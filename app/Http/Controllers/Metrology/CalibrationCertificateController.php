<?php

declare(strict_types=1);

namespace App\Http\Controllers\Metrology;

use App\Http\Controllers\Controller;
use App\Http\Requests\Metrology\StoreCalibrationCertificateRequest;
use App\Http\Requests\Metrology\UnlockCalibrationCertificateRequest;
use App\Http\Requests\Metrology\UpdateCalibrationCertificateRequest;
use App\Models\CalibrationCertificate;
use App\Models\Equipment;
use App\Services\CalibrationCertificateService;
use App\Services\MetrologyCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CalibrationCertificateController extends Controller
{
    public function __construct(
        protected CalibrationCertificateService $certificateService,
        protected MetrologyCalculationService $calculationService
    ) {}

    /**
     * Display a listing of calibration certificates with metrics and filters.
     */
    public function index(Request $request): View
    {
        Gate::authorize('view calibration certificates');

        $certificates = $this->certificateService->getPaginatedCertificates($request, 15);
        $statistics = $this->certificateService->getStatistics();
        $equipments = Equipment::select(['id', 'full_name', 'short_name', 'internal_code'])
            ->orderBy('short_name')
            ->get();

        return view('metrology.certificates.index', compact('certificates', 'statistics', 'equipments'));
    }

    /**
     * Show the form for creating a new certificate.
     */
    public function create(): View
    {
        Gate::authorize('create calibration certificates');

        $equipments = Equipment::with(['specifications.grandeur'])
            ->where('requires_calibration', true)
            ->orderBy('short_name')
            ->get();

        return view('metrology.certificates.create', compact('equipments'));
    }

    /**
     * Store a newly created certificate.
     */
    public function store(StoreCalibrationCertificateRequest $request): RedirectResponse
    {
        $certificate = $this->certificateService->storeCertificate(
            $request->validated(),
            $request->file('certificate_file'),
            $request->user()
        );

        return redirect()->route('metrology.calibration-certificates.show', $certificate)
            ->with('success', __('Calibration certificate successfully created.'));
    }

    /**
     * Display the specified calibration certificate details and metrology curves.
     */
    public function show(CalibrationCertificate $certificate, Request $request): View
    {
        Gate::authorize('view calibration certificates');

        $certificate->load([
            'equipment.specifications.grandeur',
            'calibrationPoints.equipmentSpecification.grandeur',
            'creator',
            'approver',
            'locker',
            'previousCertificate',
        ]);

        $range = (float) $request->input('range', 0.0);
        $fluid = $request->input('fluid', 'gaz');

        return view('metrology.certificates.show', compact('certificate', 'range', 'fluid'));
    }

    /**
     * Show the form for editing the certificate.
     */
    public function edit(CalibrationCertificate $certificate): View|RedirectResponse
    {
        Gate::authorize('edit calibration certificates');

        if ($certificate->is_locked) {
            return redirect()->route('metrology.calibration-certificates.show', $certificate)
                ->with('error', __('Cannot modify locked certificate. Unlock with authorization first.'));
        }

        $certificate->load(['calibrationPoints']);
        $equipments = Equipment::with(['specifications.grandeur'])
            ->where('requires_calibration', true)
            ->orderBy('short_name')
            ->get();

        return view('metrology.certificates.edit', compact('certificate', 'equipments'));
    }

    /**
     * Update the specified certificate.
     */
    public function update(
        UpdateCalibrationCertificateRequest $request,
        CalibrationCertificate $certificate
    ): RedirectResponse {
        $this->certificateService->updateCertificate(
            $certificate,
            $request->validated(),
            $request->file('certificate_file'),
            $request->user()
        );

        return redirect()->route('metrology.calibration-certificates.show', $certificate)
            ->with('success', __('Calibration certificate updated successfully.'));
    }

    /**
     * Remove the specified certificate.
     */
    public function destroy(CalibrationCertificate $certificate): RedirectResponse
    {
        Gate::authorize('delete calibration certificates');

        $this->certificateService->deleteCertificate($certificate);

        return redirect()->route('metrology.calibration-certificates.index')
            ->with('success', __('Calibration certificate deleted successfully.'));
    }

    /**
     * Approve and officially lock the certificate for operational usage.
     */
    public function approve(CalibrationCertificate $certificate, Request $request): RedirectResponse
    {
        Gate::authorize('edit calibration certificates');

        $this->certificateService->approveAndLock($certificate, $request->user());

        return redirect()->back()
            ->with('success', __('Certificate approved and locked for operational compliance.'));
    }

    /**
     * Unlock certificate for authorized review with mandatory reason.
     */
    public function unlock(
        UnlockCalibrationCertificateRequest $request,
        CalibrationCertificate $certificate
    ): RedirectResponse {
        $this->certificateService->unlock(
            $certificate,
            $request->user(),
            (string) $request->validated('reason')
        );

        return redirect()->back()
            ->with('success', __('Certificate successfully unlocked for revision.'));
    }

    /**
     * Download or stream the certificate PDF document.
     */
    public function download(CalibrationCertificate $certificate): StreamedResponse|BinaryFileResponse|RedirectResponse
    {
        Gate::authorize('view calibration certificates');

        if (blank($certificate->certificate_path) || ! Storage::disk('public')->exists($certificate->certificate_path)) {
            return redirect()->back()->with('error', __('Certificate PDF document not found on storage.'));
        }

        $fileName = ($certificate->reference ?: 'certificate-'.$certificate->id).'.pdf';

        return Storage::disk('public')->download($certificate->certificate_path, $fileName);
    }
}
