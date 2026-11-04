<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);

        $items = collect($cart)->map(function ($item) {
            $competition = DB::table('my_competitions')
                ->where('id', $item['competition_id'])
                ->first();

            if (!$competition) {
                return null;
            }

            return [
                'id' => (int) $competition->id,
                'title' => $competition->title,
                'category' => $item['category'] ?? 'Competition',
                'ticket_price' => (float) $competition->entry_price,
                'quantity' => (int) $item['quantity'],
                'image' => !empty($competition->main_image)
                    ? asset('storage/' . ltrim($competition->main_image, '/'))
                    : null,
                'max_tickets' => $competition->max_entries_per_customer !== null
                    ? (int) $competition->max_entries_per_customer
                    : null,
                'draw_at' => $competition->draw_at,
            ];
        })->filter()->values();

        return Inertia::render('Front/Cart', [
            'items' => $items,
        ]);
    }
    public function add(Request $request)
    {
        $validated = $request->validate([
            'competition_id' => ['required', 'integer', 'exists:my_competitions,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $competition = DB::table('my_competitions')
            ->where('id', $validated['competition_id'])
            ->first();

        if (!$competition) {
            return back()->withErrors([
                'message' => 'Competition not found.',
            ]);
        }

        // Store cart items in the session.
        $cart = session()->get('cart', []);

        $competitionId = (int) $competition->id;
        $quantity = (int) $validated['quantity'];

        if (isset($cart[$competitionId])) {
            $cart[$competitionId]['quantity'] += $quantity;
        } else {
            $cart[$competitionId] = [
                'competition_id' => $competitionId,
                'title' => $competition->title,
                'ticket_price' => (float) $competition->entry_price,
                'quantity' => $quantity,
                'image' => $competition->main_image,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->route('cart.index')
            ->with('success', 'Competition added to your cart.');
    }

    public function update(Request $request, $competitionId)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$competitionId])) {
            return redirect()
                ->route('cart.index')
                ->withErrors([
                    'message' => 'Competition not found in your cart.',
                ]);
        }

        $competition = DB::table('my_competitions')
            ->where('id', $competitionId)
            ->first();

        if (!$competition) {
            unset($cart[$competitionId]);
            session()->put('cart', $cart);

            return redirect()
                ->route('cart.index')
                ->withErrors([
                    'message' => 'This competition is no longer available.',
                ]);
        }

        $quantity = (int) $validated['quantity'];
        $maxTickets = $competition->max_entries_per_customer;

        if ($maxTickets !== null && $quantity > (int) $maxTickets) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantity' => "You can buy a maximum of {$maxTickets} tickets for {$competition->title}.",
            ]);
        }

        $cart[$competitionId]['quantity'] = $quantity;
        $cart[$competitionId]['title'] = $competition->title;
        $cart[$competitionId]['ticket_price'] = (float) $competition->entry_price;
        $cart[$competitionId]['image'] = $competition->main_image;

        session()->put('cart', $cart);

        return redirect()->route('cart.index');
    }

    public function remove($competitionId)
    {
        $cart = session()->get('cart', []);

        if (!isset($cart[$competitionId])) {
            return redirect()->route('cart')
                ->with('error', 'Competition not found in your cart.');
        }

        unset($cart[$competitionId]);

        session()->put('cart', $cart);

        return redirect()->route('cart.index')
            ->with('success', 'Competition removed from your cart.');
    }
}
