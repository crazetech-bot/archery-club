<?php

namespace App\Http\Resources\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms the raw array produced by ScoreProgressionService into a
 * clean, versioned JSON structure safe for API consumers and Inertia pages.
 *
 * Usage:
 *   return new ScoreProgressionResource($reportData);
 *
 * $this->resource is the array returned by ScoreProgressionService::generate().
 */
class ScoreProgressionResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray(Request $request): array
    {
        $archer = $this->resource['archer'];

        return [
            'archer' => [
                'id'       => $archer->id,
                'name'     => $archer->name,
                'category' => $archer->category,
                'gender'   => $archer->gender,
                'age'      => $archer->age,
            ],

            'filters' => $this->resource['filters'],

            // ── Summary card data ──────────────────────────────────────────
            'summary' => $this->resource['summary'],

            // ── Chart: score per session / grouped period ──────────────────
            'data_points' => $this->resource['data_points'],

            // ── Overlaid moving average line ───────────────────────────────
            'trend' => $this->resource['trend'],

            // ── Highlight cards ────────────────────────────────────────────
            'best_session'  => $this->resource['best_session'],
            'worst_session' => $this->resource['worst_session'],

            // ── Breakdown by round type ────────────────────────────────────
            'by_round_type' => $this->resource['by_round_type'],

            // ── Meta ───────────────────────────────────────────────────────
            'generated_at' => now()->toISOString(),
        ];
    }
}
