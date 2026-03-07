<?php

namespace App\Http\Controllers\Archer;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\EquipmentMaintenance;
use App\Models\EquipmentSetup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EquipmentMaintenanceController extends Controller
{
    private function archer(): Archer
    {
        return Archer::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * GET /archer/equipment/{setup}/maintenance
     * Show maintenance log for one equipment setup.
     */
    public function index(EquipmentSetup $setup): Response
    {
        $archer = $this->archer();
        abort_if($setup->archer_id !== $archer->id, 403);

        $logs = $setup->maintenanceLogs()
            ->orderByDesc('performed_at')
            ->get()
            ->map(fn (EquipmentMaintenance $m) => [
                'id'               => $m->id,
                'type'             => $m->type,
                'description'      => $m->description,
                'details'          => $m->details,
                'cost'             => $m->cost,
                'performed_at'     => $m->performed_at,
                'next_due_at'      => $m->next_due_at,
                'performed_by'     => $m->performed_by,
                'is_overdue'       => $m->isOverdue(),
            ]);

        $upcoming = $setup->maintenanceLogs()
            ->whereNotNull('next_due_at')
            ->where('next_due_at', '>=', today())
            ->orderBy('next_due_at')
            ->first();

        return Inertia::render('Equipment/Maintenance', [
            'setup' => [
                'id'              => $setup->id,
                'name'            => $setup->name,
                'bow_type'        => $setup->bow_type,
                'bow_brand'       => $setup->bow_brand,
                'bow_model'       => $setup->bow_model,
                'is_current'      => $setup->is_current,
            ],
            'logs'          => $logs,
            'nextDue'       => $upcoming ? [
                'type'        => $upcoming->type,
                'description' => $upcoming->description,
                'next_due_at' => $upcoming->next_due_at,
                'is_overdue'  => $upcoming->isOverdue(),
            ] : null,
        ]);
    }

    /**
     * POST /archer/equipment/{setup}/maintenance
     * Add a maintenance log entry.
     */
    public function store(Request $request, EquipmentSetup $setup): RedirectResponse
    {
        $archer = $this->archer();
        abort_if($setup->archer_id !== $archer->id, 403);

        $validated = $request->validate([
            'type'         => ['required', Rule::in(['check', 'repair', 'replacement', 'tuning', 'cleaning'])],
            'description'  => ['required', 'string', 'max:255'],
            'details'      => ['nullable', 'string', 'max:2000'],
            'cost'         => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'performed_at' => ['required', 'date', 'before_or_equal:today'],
            'next_due_at'  => ['nullable', 'date', 'after:performed_at'],
            'performed_by' => ['nullable', 'string', 'max:255'],
        ]);

        $setup->maintenanceLogs()->create($validated);

        return back()->with('success', 'Maintenance record added.');
    }

    /**
     * DELETE /archer/equipment/{setup}/maintenance/{log}
     * Remove a maintenance log entry.
     */
    public function destroy(EquipmentSetup $setup, EquipmentMaintenance $log): RedirectResponse
    {
        $archer = $this->archer();
        abort_if($setup->archer_id !== $archer->id, 403);
        abort_if($log->equipment_setup_id !== $setup->id, 404);

        $log->delete();

        return back()->with('success', 'Record deleted.');
    }
}
