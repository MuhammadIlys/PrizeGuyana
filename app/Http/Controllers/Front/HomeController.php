<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HomeController extends Controller
{


    public function index()
    {
        $title = 'PrizeGuyana';

        /*
    |--------------------------------------------------------------------------
    | Static homepage content
    |--------------------------------------------------------------------------
    */


        $updates = [
            [
                'id' => 1,
                'badge' => 'Announcement',
                'time_ago' => '4 weeks ago',
                'title' => 'Official Partner',
                'subtitle' => 'UFC & ZUFFA BOXING',
                'description' => "There's a reason why we are considered the best in the business...and we work with the best in the business.",
                'image' => '/images/latest-news/comp-1.jpg',
            ],
            [
                'id' => 2,
                'badge' => 'Winner',
                'time_ago' => '1 month ago',
                'title' => 'Brand New House Winner!',
                'subtitle' => 'Mary Jo Mccourt',
                'description' => 'Huge Congratulations to Mary who was our latest house winner, £20k cash and a Range Rover Sport',
                'image' => '/images/latest-news/comp-2.jpg',
            ],
            [
                'id' => 3,
                'badge' => 'Announcement',
                'time_ago' => '4 weeks ago',
                'title' => 'Official Partner',
                'subtitle' => 'UFC & ZUFFA BOXING',
                'description' => "There's a reason why we are considered the best in the business...and we work with the best in the business.",
                'image' => '/images/latest-news/comp-1.jpg',
            ],
        ];

        $stats = [
            [
                'value' => '4.8',
                'label' => 'on Trustpilot',
            ],
            [
                'value' => '£250M+',
                'label' => 'in prizes',
            ],
            [
                'value' => '£1m+',
                'label' => 'in charity donations',
            ],
        ];

        $faqs = [
            [
                'id' => 1,
                'question' => 'How do I enter a competition?',
                'answer' => 'Select any active competition from our homepage, pick your desired number of tickets (or use Quick Pick), answer the qualifying entry question, and proceed to checkout.',
            ],
            [
                'id' => 2,
                'question' => 'How are winners selected?',
                'answer' => 'All competition draws are broadcast live using a certified random number generator. Winners are determined completely transparently on the scheduled draw date regardless of ticket sales.',
            ],
            [
                'id' => 3,
                'question' => 'When will I receive my prize if I win?',
                'answer' => 'Cash prizes are transferred within 24–48 hours of winner verification. Physical rewards, such as vehicles or tech, are arranged for delivery or collection immediately after contact.',
            ],
            [
                'id' => 4,
                'question' => 'Where can I see my purchased tickets?',
                'answer' => 'All your active and past tickets are stored in your account dashboard under "My Tickets". You will also receive an instant confirmation email with your ticket numbers upon purchase.',
            ],
        ];

        /*
    |--------------------------------------------------------------------------
    | Date boundaries
    |--------------------------------------------------------------------------
    */

        $now = \Carbon\Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $tomorrowStart = $todayStart->copy()->addDay();
        $dayAfterTomorrow = $tomorrowStart->copy()->addDay();

        $visibleStatuses = ['active'];

        /*
    |--------------------------------------------------------------------------
    | Ticket counts by competition
    |--------------------------------------------------------------------------
    */

        $ticketCounts = DB::table('competition_tickets')
            ->select('competition_id')
            ->selectRaw('COUNT(*) AS generated_tickets')
            ->selectRaw(
                "SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available_tickets"
            )
            ->selectRaw(
                "SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) AS assigned_tickets"
            )
            ->selectRaw(
                "SUM(CASE WHEN status = 'unavailable' THEN 1 ELSE 0 END) AS unavailable_tickets"
            )
            ->groupBy('competition_id');

        /*
    |--------------------------------------------------------------------------
    | Shared competition query
    |--------------------------------------------------------------------------
    */

        $getCompetitions = function () use (
            $visibleStatuses,
            $ticketCounts
        ) {
            return DB::table('my_competitions')
                ->leftJoinSub(
                    clone $ticketCounts,
                    'ticket_counts',
                    function ($join) {
                        $join->on(
                            'my_competitions.id',
                            '=',
                            'ticket_counts.competition_id'
                        );
                    }
                )
                ->whereIn('my_competitions.status', $visibleStatuses)
                ->select(
                    'my_competitions.id',
                    'my_competitions.title',
                    'my_competitions.slug',
                    'my_competitions.main_image',
                    'my_competitions.starts_at',
                    'my_competitions.ends_at',
                    'my_competitions.draw_at',
                    'my_competitions.entry_price',
                    'my_competitions.currency',
                    'my_competitions.total_entries',
                    'my_competitions.is_featured',
                    'my_competitions.published_at',
                    'ticket_counts.generated_tickets',
                    'ticket_counts.available_tickets',
                    'ticket_counts.assigned_tickets',
                    'ticket_counts.unavailable_tickets'
                );
        };

        /*
    |--------------------------------------------------------------------------
    | Format competition data for existing Vue components
    |--------------------------------------------------------------------------
    */

        $mapCompetition = function ($competition, $dateBadge = null) {
            $capacity = (int) $competition->total_entries;

            $generated = (int) ($competition->generated_tickets ?? 0);
            $available = (int) ($competition->available_tickets ?? 0);
            $assigned = (int) ($competition->assigned_tickets ?? 0);
            $unavailable = (int) ($competition->unavailable_tickets ?? 0);

            $image = $competition->main_image;

            if (
                $image &&
                !str_starts_with($image, 'http://') &&
                !str_starts_with($image, 'https://')
            ) {
                $image = str_starts_with($image, '/')
                    ? asset(ltrim($image, '/'))
                    : asset('storage/' . $image);
            }

            $currency = $competition->currency ?: 'USD';

            return [
                'id' => encrypt((string) $competition->id),
                'title' => $competition->title,
                'slug' => $competition->slug,
                'date_badge' => $dateBadge,
                'image' => $image,

                // Percentage of competition capacity already assigned.
                'percentage' => $capacity > 0
                    ? min(100, (int) round(($assigned / $capacity) * 100))
                    : 0,

                'price' => 'Entry ' . number_format(
                    (float) $competition->entry_price,
                    2
                ) . ' ' . $currency,

                'entry_price' => (float) $competition->entry_price,
                'currency' => $currency,
                'total_entries' => $capacity,
                'generated_tickets' => $generated,
                'available_tickets' => $available,
                'assigned_tickets' => $assigned,
                'unavailable_tickets' => $unavailable,

                // Temporary until the competition details route is created.
                'link' => route('home'),
            ];
        };


        /*
    |--------------------------------------------------------------------------
    | 1. Featured competitions
    |--------------------------------------------------------------------------
    */




        /*
    |--------------------------------------------------------------------------
    | 2. Competitions ending today
    |--------------------------------------------------------------------------
    */

        $endingToday = $getCompetitions()
            ->whereNotNull('my_competitions.ends_at')
            ->where('my_competitions.ends_at', '>=', $todayStart)
            ->where('my_competitions.ends_at', '<', $tomorrowStart)
            ->orderBy('my_competitions.ends_at')
            ->limit(8)
            ->get()
            ->map(function ($competition) use ($mapCompetition) {
                return $mapCompetition(
                    $competition,
                    \Carbon\Carbon::parse($competition->ends_at)->format('M j')
                );
            })
            ->values();

        /*
    |--------------------------------------------------------------------------
    | 3. Competitions starting tomorrow
    |--------------------------------------------------------------------------
    */

        $tomorrow = $getCompetitions()
            ->whereNotNull('my_competitions.ends_at')
            ->where('my_competitions.ends_at', '>=', $tomorrowStart)
            ->where('my_competitions.ends_at', '<', $dayAfterTomorrow)
            ->orderBy('my_competitions.ends_at')
            ->limit(8)
            ->get()
            ->map(function ($competition) use ($mapCompetition) {
                return $mapCompetition(
                    $competition,
                    'TMR ' . \Carbon\Carbon::parse(
                        $competition->ends_at
                    )->format('H:i')
                );
            })
            ->values();


        /*
    |--------------------------------------------------------------------------
    | 4. Future competitions
    |--------------------------------------------------------------------------
    */

        $futureSelection = $getCompetitions()
            ->where('my_competitions.status', 'active')
            ->whereNotNull('my_competitions.ends_at')
            ->where('my_competitions.ends_at', '>=', $dayAfterTomorrow)
            ->orderBy('my_competitions.ends_at')
            ->limit(12)
            ->get()
            ->map(function ($competition) use ($mapCompetition) {
                return $mapCompetition(
                    $competition,
                    \Carbon\Carbon::parse($competition->ends_at)->format('M j')
                );
            })
            ->values();

        $featuredCompetitions = $getCompetitions()
            ->where('my_competitions.status', 'active')
            ->where('my_competitions.is_featured', 1)
            ->orderByDesc('my_competitions.published_at')
            ->limit(8)
            ->get()
            ->map(fn($competition) => $mapCompetition(
                $competition,
                $competition->ends_at
                    ? \Carbon\Carbon::parse($competition->ends_at)->format('M j')
                    : null
            ))
            ->values();

        /*
    |--------------------------------------------------------------------------
    | Return homepage data to Inertia
    |--------------------------------------------------------------------------
    */

        $slides = DB::table('my_competitions as c')
            ->join('competition_prizes as p', function ($join) {
                $join->on('p.competition_id', '=', 'c.id')
                    ->where('p.prize_role', '=', 'main')
                    ->where('p.status', '=', 'active');
            })
            ->whereIn('c.status', $visibleStatuses)
            ->where('c.starts_at', '<=', now())
            ->where('c.ends_at', '>', now())
            ->select(
                'c.id',
                'c.title',
                'c.slug',
                'c.main_image',
                'c.total_entries',
                'p.name as prize_name',
                'p.prize_value',
                'p.currency',
                'p.image_path as prize_image'
            )
            ->orderByDesc('p.prize_value')
            ->limit(5)
            ->get()
            ->map(function ($competition) {
                $image = $competition->prize_image ?: $competition->main_image;

                return [
                    'id' => encrypt((string) $competition->id),
                    'title' => $competition->title,
                    'slug' => $competition->slug,
                    'price' => '$' . number_format((float) $competition->prize_value, 0)
                        . ' ' . $competition->currency,
                    'percentage' => 0,
                    'image' => $image
                        ? (str_starts_with($image, '/')
                            ? asset(ltrim($image, '/'))
                            : asset('storage/' . $image))
                        : null,
                ];
            });

        return Inertia::render('Front/Home', [
            'title' => $title,
            'slides' => $slides,
            'featuredCompetitions' => $featuredCompetitions,
            'endingToday' => $endingToday,
            'tomorrow' => $tomorrow,
            'futureSelection' => $futureSelection,
            'updates' => $updates,
            'stats' => $stats,
            'faqs' => $faqs,
        ]);
    }
}
