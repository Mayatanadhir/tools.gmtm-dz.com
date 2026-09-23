<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\CannotDeleteAssignedEmployeeException;
use App\Http\Requests\MasterData\StoreCustomerRequest;
use App\Http\Requests\MasterData\StoreEmployeeRequest;
use App\Http\Requests\MasterData\StoreSiteRequest;
use App\Http\Requests\MasterData\UpdateCustomerRequest;
use App\Http\Requests\MasterData\UpdateEmployeeRequest;
use App\Http\Requests\MasterData\UpdateSiteRequest;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\EmployeeRepositoryInterface;
use App\Interfaces\SiteRepositoryInterface;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Site;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\EmployeeService;
use App\Services\SiteService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MasterDataController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService,
        protected EmployeeRepositoryInterface $employeeRepository,
        protected CustomerService $customerService,
        protected CustomerRepositoryInterface $customerRepository,
        protected SiteService $siteService,
        protected SiteRepositoryInterface $siteRepository
    ) {}

    /**
     * Display the Master Data dashboard.
     */
    public function index(): View
    {
        Gate::authorize('view master data');

        return view('master-data.index');
    }

    /**
     * Display the Clients explorer.
     */
    public function clients(Request $request): View
    {
        Gate::authorize('view clients');

        $customers = $this->customerRepository->paginateWithFilter($request->all(), 15);

        return view('master-data.clients', compact('customers'));
    }

    /**
     * Store a newly created client in storage.
     */
    public function storeClient(StoreCustomerRequest $request): RedirectResponse
    {
        $this->customerService->createCustomer($request->validated());

        return redirect()->route('master-data.clients', $request->query())->with('success', __('Customer created successfully.'));
    }

    /**
     * Update the specified client in storage.
     */
    public function updateClient(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->customerService->updateCustomer($customer, $request->validated());

        return redirect()->route('master-data.clients', $request->query())->with('success', __('Customer updated successfully.'));
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroyClient(Customer $customer): RedirectResponse
    {
        Gate::authorize('delete clients');

        $this->customerService->deleteCustomer($customer);

        return redirect()->route('master-data.clients', request()->query())->with('success', __('Customer deleted successfully.'));
    }

    /**
     * Display the Employees explorer.
     */
    public function employees(Request $request): View
    {
        Gate::authorize('view employees');

        $employees = $this->employeeRepository->paginateWithFilter($request->all(), 15);
        $users = User::select(['id', 'name', 'email', 'profile_photo_path'])->orderBy('name')->get();

        return view('master-data.employees', compact('employees', 'users'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function storeEmployee(StoreEmployeeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $photo = $request->file('photo');

        $this->employeeService->createEmployee($data, $photo);

        return redirect()->route('master-data.employees', $request->query())->with('success', __('Employee created successfully.'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function updateEmployee(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $data = $request->validated();
        $photo = $request->file('photo');
        $removePhoto = (bool) $request->boolean('remove_photo');

        $this->employeeService->updateEmployee($employee, $data, $photo, $removePhoto);

        return redirect()->route('master-data.employees', $request->query())->with('success', __('Employee updated successfully.'));
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroyEmployee(Employee $employee): RedirectResponse
    {
        Gate::authorize('delete employees');

        try {
            $this->employeeService->deleteEmployee($employee);

            return redirect()->route('master-data.employees', request()->query())->with('success', __('Employee deleted successfully.'));
        } catch (CannotDeleteAssignedEmployeeException $e) {
            return redirect()->route('master-data.employees', request()->query())->with('error', __($e->getMessage()));
        }
    }

    /**
     * Display the Sites explorer.
     */
    public function sites(Request $request): View
    {
        Gate::authorize('view sites');

        $sites = $this->siteRepository->paginateWithFilter($request->all(), 15);
        $customers = Customer::orderBy('company_name')->get(['id', 'company_name', 'short_name']);

        return view('master-data.sites', compact('sites', 'customers'));
    }

    /**
     * Store a newly created site in storage.
     */
    public function storeSite(StoreSiteRequest $request): RedirectResponse
    {
        $this->siteService->createSite($request->validated());

        return redirect()->route('master-data.sites', $request->query())->with('success', __('Site created successfully.'));
    }

    /**
     * Update the specified site in storage.
     */
    public function updateSite(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $this->siteService->updateSite($site, $request->validated());

        return redirect()->route('master-data.sites', $request->query())->with('success', __('Site updated successfully.'));
    }

    /**
     * Remove the specified site from storage.
     */
    public function destroySite(Site $site): RedirectResponse
    {
        Gate::authorize('delete sites');

        $this->siteService->deleteSite($site);

        return redirect()->route('master-data.sites', request()->query())->with('success', __('Site deleted successfully.'));
    }
}
