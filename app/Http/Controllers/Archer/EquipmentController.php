<?php

namespace App\Http\Controllers\Archer;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\EquipmentSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EquipmentController extends Controller
{
    private function archer(): Archer
    {
        return Archer::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * GET /archer/equipment
     */
    public function index(): Response
    {
        $archer = $this->archer();

        $setups = EquipmentSetup::where('archer_id', $archer->id)
            ->orderByDesc('is_current')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (EquipmentSetup $s) => [
                'id'                 => $s->id,
                'name'               => $s->name,
                'bow_type'           => $s->bow_type,
                'bow_brand'          => $s->bow_brand,
                'bow_model'          => $s->bow_model,
                'draw_weight_lbs'    => $s->draw_weight_lbs,
                'draw_length_inches' => $s->draw_length_inches,
                'arrow_brand'        => $s->arrow_brand,
                'arrow_model'        => $s->arrow_model,
                'arrow_spine'        => $s->arrow_spine,
                'is_current'         => (bool) $s->is_current,
                'created_at'         => $s->created_at,
            ]);

        return Inertia::render('Archer/Equipment', [
            'setups' => $setups,
        ]);
    }

    /**
     * POST /archer/equipment
     */
    public function store(Request $request)
    {
        $archer = $this->archer();
        $data   = $this->validated($request);

        if ($data['is_current']) {
            EquipmentSetup::where('archer_id', $archer->id)->update(['is_current' => false]);
        }

        $archer->equipmentSetups()->create($data);

        return redirect()->route('archer.equipment.index')
            ->with('success', 'Equipment setup added.');
    }

    /**
     * PUT /archer/equipment/{setup}
     */
    public function update(Request $request, EquipmentSetup $setup)
    {
        $archer = $this->archer();
        abort_if($setup->archer_id !== $archer->id, 403);

        $data = $this->validated($request);

        if ($data['is_current']) {
            EquipmentSetup::where('archer_id', $archer->id)
                ->where('id', '!=', $setup->id)
                ->update(['is_current' => false]);
        }

        $setup->update($data);

        return redirect()->route('archer.equipment.index')
            ->with('success', 'Equipment setup updated.');
    }

    /**
     * PUT /archer/equipment/{setup}/set-current
     */
    public function setCurrent(EquipmentSetup $setup)
    {
        $archer = $this->archer();
        abort_if($setup->archer_id !== $archer->id, 403);

        EquipmentSetup::where('archer_id', $archer->id)->update(['is_current' => false]);
        $setup->update(['is_current' => true]);

        return redirect()->back()->with('success', 'Current setup updated.');
    }

    /**
     * DELETE /archer/equipment/{setup}
     */
    public function destroy(EquipmentSetup $setup)
    {
        $archer = $this->archer();
        abort_if($setup->archer_id !== $archer->id, 403);

        $setup->delete();

        return redirect()->route('archer.equipment.index')
            ->with('success', 'Equipment setup deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'               => ['required', 'string', 'max:100'],
            'bow_type'           => ['required', 'string', 'in:Recurve,Compound,Barebow,Longbow'],
            'bow_brand'          => ['nullable', 'string', 'max:100'],
            'bow_model'          => ['nullable', 'string', 'max:100'],
            'draw_weight_lbs'    => ['nullable', 'numeric', 'min:1', 'max:100'],
            'draw_length_inches' => ['nullable', 'numeric', 'min:10', 'max:40'],
            'arrow_brand'        => ['nullable', 'string', 'max:100'],
            'arrow_model'        => ['nullable', 'string', 'max:100'],
            'arrow_spine'        => ['nullable', 'integer', 'min:100', 'max:1200'],
            'is_current'         => ['boolean'],
        ]);
    }
}
