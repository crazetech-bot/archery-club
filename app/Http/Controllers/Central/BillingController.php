<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stancl\Tenancy\Database\Models\Tenant;

class BillingController extends Controller
{
    /**
     * GET /billing
     * Show subscription overview for the current tenant.
     */
    public function index(Request $request): Response
    {
        $tenant = tenant(); // resolved by Stancl Tenancy

        return Inertia::render('Billing/Index', [
            'tenant'       => $tenant ? ['id' => $tenant->id, 'plan' => $tenant->plan, 'status' => $tenant->status] : null,
            'plans'        => $this->availablePlans(),
            'onGracePeriod' => false,
        ]);
    }

    /**
     * POST /billing/subscribe
     * Create a Stripe Checkout session and redirect.
     */
    public function subscribe(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'price_id' => ['required', 'string'],
        ]);

        try {
            $user     = $request->user();
            $checkout = $user->newSubscription('default', $data['price_id'])
                ->checkout([
                    'success_url' => url('/billing?success=1'),
                    'cancel_url'  => url('/billing'),
                ]);

            return redirect($checkout->url);
        } catch (IncompletePayment $e) {
            return redirect()->route('cashier.payment', [$e->payment->id, 'redirect' => url('/billing')]);
        }
    }

    /**
     * POST /billing/portal
     * Redirect to the Stripe Customer Portal.
     */
    public function portal(Request $request): RedirectResponse
    {
        return $request->user()->redirectToBillingPortal(url('/billing'));
    }

    /**
     * POST /billing/webhook
     * Stripe webhook handler (handled automatically by Cashier).
     */
    public function webhook(): HttpResponse
    {
        // Cashier's WebhookController handles this via the registered route.
        // This stub exists only for the route definition.
        abort(404);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function availablePlans(): array
    {
        return [
            ['id' => 'starter', 'name' => 'Starter', 'price' => 19, 'currency' => 'GBP', 'archers' => 25, 'stripe_price_id' => env('STRIPE_PRICE_STARTER', '')],
            ['id' => 'pro',     'name' => 'Pro',     'price' => 49, 'currency' => 'GBP', 'archers' => 100, 'stripe_price_id' => env('STRIPE_PRICE_PRO', '')],
            ['id' => 'enterprise', 'name' => 'Enterprise', 'price' => 149, 'currency' => 'GBP', 'archers' => 999, 'stripe_price_id' => env('STRIPE_PRICE_ENTERPRISE', '')],
        ];
    }
}
