<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Stripe\StripeClient;
use Illuminate\Support\Str;

use Throwable;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $items = collect($cart)->map(function ($item) {
            $competition = DB::table('my_competitions')
                ->where('id', $item['competition_id'])
                ->first();

            if (!$competition) {
                return null;
            }

            $quantity = (int) ($item['quantity'] ?? 0);
            $price = (float) $competition->entry_price;

            return [
                'id' => (int) $competition->id,
                'title' => $competition->title,
                'ticket_price' => $price,
                'quantity' => $quantity,
                'line_total' => round($price * $quantity, 2),
                'currency' => $competition->currency ?? 'USD',
                'image' => !empty($competition->main_image)
                    ? asset('storage/' . ltrim($competition->main_image, '/'))
                    : null,
                'draw_at' => $competition->draw_at,
            ];
        })->filter()->values();


        if ($items->isEmpty()) {
            session()->forget('cart');

            return redirect()
                ->route('cart.index')
                ->with('error', 'Your cart no longer contains valid competitions.');
        }

        $currencies = $items->pluck('currency')->unique();

        if ($currencies->count() !== 1) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Please purchase competitions using the same currency in one order.');
        }

        return Inertia::render('Front/Checkout', [
            'items' => $items,
            'currency' => $currencies->first(),
            'subtotal' => round($items->sum('line_total'), 2),
            'totalTickets' => $items->sum('quantity'),
            'customer' => [
                'first_name' => Auth::user()->first_name,
                'last_name' => Auth::user()->last_name,
                'email' => Auth::user()->email,
                'country' => Auth::user()->country,
                'phone_number' => Auth::user()->phone_number,
            ],
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'terms_accepted' => ['required', 'accepted'],
        ]);

        $user = Auth::user();
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $order = null;
        $payment = null;

        try {
            /*
         * Validate competitions and calculate totals from the database.
         */
            $items = collect($cart)->map(function ($item) use ($user) {
                $competitionId = (int) ($item['competition_id'] ?? 0);
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($competitionId < 1 || $quantity < 1) {
                    throw new \RuntimeException(
                        'Your cart contains an invalid item.'
                    );
                }

                $competition = DB::table('my_competitions')
                    ->where('id', $competitionId)
                    ->first();

                if (!$competition || $competition->status !== 'active') {
                    throw new \RuntimeException(
                        'A competition in your cart is no longer available.'
                    );
                }

                $now = now();

                if (
                    $competition->starts_at &&
                    $now->lt(Carbon::parse($competition->starts_at))
                ) {
                    throw new \RuntimeException(
                        "{$competition->title} has not started yet."
                    );
                }

                if (
                    $competition->ends_at &&
                    $now->gte(Carbon::parse($competition->ends_at))
                ) {
                    throw new \RuntimeException(
                        "{$competition->title} has ended."
                    );
                }

                /*
             * Check minimum age.
             */
                if (
                    !$user->date_of_birth ||
                    Carbon::parse($user->date_of_birth)->age
                    < (int) $competition->minimum_age
                ) {
                    throw new \RuntimeException(
                        "You do not meet the age requirement for {$competition->title}."
                    );
                }

                /*
             * Check country restrictions.
             * This assumes users.country stores the same ISO country
             * code format as competition_country.country_code.
             */
                $allowedCountries = DB::table('competition_country')
                    ->where('competition_id', $competitionId)
                    ->exists();

                if ($allowedCountries) {
                    $countryAllowed = DB::table('competition_country')
                        ->where('competition_id', $competitionId)
                        ->where(
                            'country_code',
                            strtoupper($user->country)
                        )
                        ->exists();

                    if (!$countryAllowed) {
                        throw new \RuntimeException(
                            "You are not eligible for {$competition->title} in your country."
                        );
                    }
                }

                /*
             * Enforce per-customer purchase limits.
             */
                $maxPerCustomer = $competition->max_entries_per_customer;

                if ($maxPerCustomer !== null) {
                    $alreadyAssigned = DB::table('competition_tickets')
                        ->where('competition_id', $competitionId)
                        ->where('user_id', $user->id)
                        ->where('status', 'assigned')
                        ->count();

                    if ($alreadyAssigned + $quantity > $maxPerCustomer) {
                        throw new \RuntimeException(
                            "The purchase limit for {$competition->title} has been reached."
                        );
                    }
                }

                /*
             * Check available pre-generated tickets.
             */
                $availableTickets = DB::table('competition_tickets')
                    ->where('competition_id', $competitionId)
                    ->where('status', 'available')
                    ->count();

                if ($availableTickets < $quantity) {
                    throw new \RuntimeException(
                        "Not enough tickets are available for {$competition->title}."
                    );
                }

                return [
                    'competition_id' => $competitionId,
                    'title' => $competition->title,
                    'quantity' => $quantity,
                    'unit_price' => (float) $competition->entry_price,
                    'line_total' => round(
                        (float) $competition->entry_price * $quantity,
                        2
                    ),
                    'currency' => strtoupper(
                        $competition->currency ?: 'USD'
                    ),
                ];
            })->values();

            if ($items->isEmpty()) {
                return redirect()
                    ->route('cart.index')
                    ->with('error', 'Your cart contains no valid items.');
            }

            $currencies = $items->pluck('currency')->unique();

            if ($currencies->count() !== 1) {
                return redirect()
                    ->route('cart.index')
                    ->with(
                        'error',
                        'All competitions in one order must use the same currency.'
                    );
            }

            $currency = $currencies->first();
            $subtotal = round($items->sum('line_total'), 2);

            if ($subtotal <= 0) {
                return redirect()
                    ->route('cart.index')
                    ->with('error', 'The order total must be greater than zero.');
            }

            /*
         * Create the pending order and payment records.
         */
            [$order, $payment] = DB::transaction(function () use (
                $user,
                $items,
                $currency,
                $subtotal
            ) {
                $order = DB::table('orders')->insertGetId([
                    'user_id' => $user->id,
                    'order_number' => 'PG-' . Str::upper(Str::random(16)),
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                    'currency' => $currency,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($items as $item) {
                    DB::table('order_items')->insert([
                        'order_id' => $order,
                        'competition_id' => $item['competition_id'],
                        'competition_title' => $item['title'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'line_total' => $item['line_total'],
                        'currency' => $item['currency'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $payment = DB::table('payments')->insertGetId([
                    'order_id' => $order,
                    'provider' => 'stripe',
                    'amount' => $subtotal,
                    'currency' => $currency,
                    'status' => 'pending',
                    'metadata' => json_encode([
                        'user_id' => $user->id,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return [$order, $payment];
            });

            /*
         * Start Stripe Checkout using database-calculated prices.
         */
            $stripe = new StripeClient(
                config('services.stripe.secret')
            );

            $lineItems = $items->map(function ($item) use ($currency) {
                /*
             * Stripe unit_amount uses the currency's smallest unit.
             * This conversion is for two-decimal currencies such as USD.
             */
                return [
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'unit_amount' => (int) round(
                            $item['unit_price'] * 100
                        ),
                        'product_data' => [
                            'name' => $item['title'],
                        ],
                    ],
                    'quantity' => $item['quantity'],
                ];
            })->all();

            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'customer_email' => $user->email,
                'client_reference_id' => (string) $order,
                'line_items' => $lineItems,
                'metadata' => [
                    'order_id' => (string) $order,
                    'payment_id' => (string) $payment,
                    'user_id' => (string) $user->id,
                ],
                'payment_intent_data' => [
                    'metadata' => [
                        'order_id' => (string) $order,
                        'payment_id' => (string) $payment,
                    ],
                ],
                'success_url' => route('checkout.index')
                    . '?payment=success&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout.index')
                    . '?payment=cancelled',
            ]);

            DB::table('payments')
                ->where('id', $payment)
                ->update([
                    'stripe_checkout_session_id' => $session->id,
                    'updated_at' => now(),
                ]);

            return Inertia::location($session->url);
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('checkout.index')
                ->withErrors([
                    'message' => $exception->getMessage(),
                ]);
        } catch (Throwable $exception) {
            report($exception);

            /*
         * Mark the order as cancelled if Stripe session creation fails.
         * A verified payment webhook will handle successful payments.
         */
            if ($order && $payment) {
                DB::table('payments')
                    ->where('id', $payment)
                    ->whereNull('stripe_checkout_session_id')
                    ->update([
                        'status' => 'failed',
                        'updated_at' => now(),
                    ]);

                DB::table('orders')
                    ->where('id', $order)
                    ->where('payment_status', 'pending')
                    ->update([
                        'status' => 'cancelled',
                        'payment_status' => 'failed',
                        'updated_at' => now(),
                    ]);
            }

            return redirect()
                ->route('checkout.index')
                ->withErrors([
                    'message' => 'We could not start your payment. Please try again.',
                ]);
        }
    }
}
