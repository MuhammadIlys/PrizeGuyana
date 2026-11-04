<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EntriesController extends Controller
{
    /**
     * Display assigned competition entries.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'competition' => ['nullable', 'string', 'max:2048'],
            'payment_status' => ['nullable', 'string', 'max:30'],
        ]);

        // Decrypt the selected competition filter.
        $competitionId = null;

        if (!empty($filters['competition'])) {
            $competitionId = $this->decryptId($filters['competition']);
        }

        $query = DB::table('competition_tickets')
            ->leftJoin(
                'my_competitions',
                'competition_tickets.competition_id',
                '=',
                'my_competitions.id'
            )
            ->leftJoin(
                'users',
                'competition_tickets.user_id',
                '=',
                'users.id'
            )
            ->leftJoin(
                'orders',
                'competition_tickets.order_id',
                '=',
                'orders.id'
            )
            ->where('competition_tickets.status', 'assigned')
            ->select(
                'competition_tickets.id',
                'competition_tickets.ticket_number',
                'competition_tickets.competition_id',
                'competition_tickets.user_id',
                'competition_tickets.order_id',
                'competition_tickets.status',
                'competition_tickets.assigned_at',
                'competition_tickets.created_at',
                'my_competitions.title as competition_title',
                'users.name as customer_name',
                'users.email as customer_email',
                'orders.order_number',
                'orders.payment_status',
                'orders.total as order_total',
                'orders.currency as order_currency'
            );

        // Search by ticket number, customer or order number.
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where(
                    'competition_tickets.ticket_number',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('orders.order_number', 'like', "%{$search}%");
            });
        }

        // Filter by competition.
        if ($competitionId !== null) {
            $query->where(
                'competition_tickets.competition_id',
                $competitionId
            );
        }

        // Filter by associated order payment status.
        if (!empty($filters['payment_status'])) {
            $query->where(
                'orders.payment_status',
                $filters['payment_status']
            );
        }

        $entries = $query
            ->orderByDesc('competition_tickets.assigned_at')
            ->paginate(20)
            ->withQueryString();

        // Replace database IDs with encrypted IDs before returning to Vue.
        $entries->getCollection()->transform(function ($entry) {
            return [
                'encrypted_id' => Crypt::encryptString(
                    (string) $entry->id
                ),

                'ticket_number' => $entry->ticket_number,

                'competition_encrypted_id' => $entry->competition_id
                    ? Crypt::encryptString((string) $entry->competition_id)
                    : null,

                'customer_encrypted_id' => $entry->user_id
                    ? Crypt::encryptString((string) $entry->user_id)
                    : null,

                'order_encrypted_id' => $entry->order_id
                    ? Crypt::encryptString((string) $entry->order_id)
                    : null,

                'status' => $entry->status,
                'assigned_at' => $entry->assigned_at,
                'created_at' => $entry->created_at,

                'competition_title' => $entry->competition_title,
                'customer_name' => $entry->customer_name,
                'customer_email' => $entry->customer_email,

                'order_number' => $entry->order_number,
                'payment_status' => $entry->payment_status,
                'order_total' => $entry->order_total,
                'order_currency' => $entry->order_currency,
            ];
        });

        // Entry statistics are based on assigned tickets.
        $stats = DB::table('competition_tickets')
            ->leftJoin(
                'orders',
                'competition_tickets.order_id',
                '=',
                'orders.id'
            )
            ->where('competition_tickets.status', 'assigned')
            ->selectRaw('COUNT(*) as total_entries')
            ->selectRaw("
                SUM(
                    CASE
                        WHEN orders.payment_status = 'paid'
                        THEN 1 ELSE 0
                    END
                ) as paid_entries
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN orders.payment_status = 'pending'
                        THEN 1 ELSE 0
                    END
                ) as pending_entries
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN orders.payment_status IN (
                            'failed',
                            'refunded',
                            'disputed'
                        )
                        THEN 1 ELSE 0
                    END
                ) as other_payment_entries
            ")
            ->first();

        // Competition options also use encrypted IDs.
        $competitions = DB::table('my_competitions')
            ->select('id', 'title')
            ->orderByDesc('id')
            ->get()
            ->map(function ($competition) {
                return [
                    'encrypted_id' => Crypt::encryptString(
                        (string) $competition->id
                    ),
                    'title' => $competition->title,
                ];
            });

        return Inertia::render('Admin/Entries/Index', [
            'entries' => $entries,

            'stats' => [
                'total_entries' => (int) ($stats->total_entries ?? 0),
                'paid_entries' => (int) ($stats->paid_entries ?? 0),
                'pending_entries' => (int) ($stats->pending_entries ?? 0),
                'other_payment_entries' => (int) ($stats->other_payment_entries ?? 0),
            ],

            'competitions' => $competitions,

            'filters' => [
                'search' => $filters['search'] ?? '',
                'competition' => $filters['competition'] ?? '',
                'payment_status' => $filters['payment_status'] ?? '',
            ],
        ]);
    }

    /**
     * Display an assigned entry.
     */
    public function show($id)
    {
        // Decrypt and validate the incoming entry ID.
        $entryId = $this->decryptId($id);

        $entry = DB::table('competition_tickets')
            ->leftJoin(
                'my_competitions',
                'competition_tickets.competition_id',
                '=',
                'my_competitions.id'
            )
            ->leftJoin(
                'users',
                'competition_tickets.user_id',
                '=',
                'users.id'
            )
            ->leftJoin(
                'orders',
                'competition_tickets.order_id',
                '=',
                'orders.id'
            )
            ->where('competition_tickets.id', $entryId)
            ->where('competition_tickets.status', 'assigned')
            ->select(
                'competition_tickets.id',
                'competition_tickets.ticket_number',
                'competition_tickets.competition_id',
                'competition_tickets.user_id',
                'competition_tickets.order_id',
                'competition_tickets.status',
                'competition_tickets.assigned_at',
                'competition_tickets.created_at',
                'competition_tickets.updated_at',
                'my_competitions.title as competition_title',
                'my_competitions.slug as competition_slug',
                'users.name as customer_name',
                'users.email as customer_email',
                'orders.order_number',
                'orders.status as order_status',
                'orders.payment_status',
                'orders.total as order_total',
                'orders.currency as order_currency'
            )
            ->first();

        abort_if(!$entry, 404);

        // Build a frontend-safe response without raw database IDs.
        $entry = [
            'encrypted_id' => Crypt::encryptString(
                (string) $entry->id
            ),

            'ticket_number' => $entry->ticket_number,

            'competition_encrypted_id' => $entry->competition_id
                ? Crypt::encryptString((string) $entry->competition_id)
                : null,

            'customer_encrypted_id' => $entry->user_id
                ? Crypt::encryptString((string) $entry->user_id)
                : null,

            'order_encrypted_id' => $entry->order_id
                ? Crypt::encryptString((string) $entry->order_id)
                : null,

            'status' => $entry->status,
            'assigned_at' => $entry->assigned_at,
            'created_at' => $entry->created_at,
            'updated_at' => $entry->updated_at,

            'competition_title' => $entry->competition_title,
            'competition_slug' => $entry->competition_slug,

            'customer_name' => $entry->customer_name,
            'customer_email' => $entry->customer_email,

            'order_number' => $entry->order_number,
            'order_status' => $entry->order_status,
            'payment_status' => $entry->payment_status,
            'order_total' => $entry->order_total,
            'order_currency' => $entry->order_currency,
        ];

        return Inertia::render('Admin/Entries/Show', [
            'entry' => $entry,
        ]);
    }

    /**
     * Decrypt and validate an encrypted database ID.
     */
    private function decryptId(string $encryptedId): int
    {
        try {
            $id = Crypt::decryptString($encryptedId);
        } catch (DecryptException $exception) {
            abort(404);
        }

        abort_unless(
            ctype_digit($id) && (int) $id > 0,
            404
        );

        return (int) $id;
    }
}
