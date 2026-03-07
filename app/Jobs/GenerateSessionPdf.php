<?php

namespace App\Jobs;

use App\Models\TrainingSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateSessionPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly int $trainingSessionId,
        public readonly int $tenantId,
    ) {
        $this->onQueue('pdf');
    }

    public function handle(): void
    {
        // Re-initialise tenant context (jobs run outside tenant middleware)
        $tenant = \Stancl\Tenancy\Database\Models\Tenant::find($this->tenantId);

        if (! $tenant) {
            Log::warning('GenerateSessionPdf: tenant not found', ['tenant_id' => $this->tenantId]);
            return;
        }

        tenancy()->initialize($tenant);

        try {
            $session = TrainingSession::with([
                'archer',
                'coach',
                'liveSession.ends.arrows',
            ])->findOrFail($this->trainingSessionId);

            $liveSession = $session->liveSession;
            $archer      = $session->archer;
            $ends        = $liveSession?->ends ?? collect();

            $pdf = Pdf::loadView('pdf.session-summary', compact('session', 'liveSession', 'archer', 'ends'))
                ->setPaper('a4', 'portrait');

            $path = "pdfs/sessions/{$this->trainingSessionId}.pdf";

            Storage::disk('local')->put($path, $pdf->output());

            Log::info('GenerateSessionPdf: PDF generated', [
                'session_id' => $this->trainingSessionId,
                'path'       => $path,
            ]);
        } finally {
            tenancy()->end();
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateSessionPdf failed', [
            'session_id' => $this->trainingSessionId,
            'error'      => $e->getMessage(),
        ]);
    }
}
