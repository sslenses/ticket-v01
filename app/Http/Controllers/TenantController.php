<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants.
     */
    public function index()
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('dest_manager')) {
            abort(403, 'Unauthorized. Only administrators and destination managers can access the tenant list.');
        }

        $tenants = Tenant::oldest()->get();
        return view('tenants.index', compact('tenants'));
    }

    /**
     * Store a newly created tenant in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('dest_manager')) {
            abort(403, 'Unauthorized.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tenants,name'],
            'code' => ['required', 'string', 'max:50', 'unique:tenants,code'],
        ]);

        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
        ]);

        return response()->json($tenant, 201);
    }

    /**
     * Update the specified tenant in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('dest_manager')) {
            abort(403, 'Unauthorized.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tenants,name,' . $tenant->id],
            'code' => ['required', 'string', 'max:50', 'unique:tenants,code,' . $tenant->id],
        ]);

        $tenant->update([
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
        ]);

        return response()->json($tenant);
    }

    /**
     * Remove the specified tenant from storage.
     */
    public function destroy(Tenant $tenant)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('dest_manager')) {
            abort(403, 'Unauthorized.');
        }

        // Check if there are tickets referencing this tenant
        $hasTickets = \App\Models\Ticket::where('source_tenant_id', $tenant->id)
            ->orWhere('destination_tenant_id', $tenant->id)
            ->exists();

        if ($hasTickets) {
            return response()->json(['message' => 'Cannot delete tenant. It is associated with one or more deployment tickets.'], 422);
        }

        $tenant->delete();

        return response()->json(['message' => 'Tenant deleted successfully.']);
    }
}
