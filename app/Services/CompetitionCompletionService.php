<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class CompetitionCompletionService
{
    public function complete(int $competitionId): array
    {
        $drawWinners = [];

        $result = DB::transaction(function () use (
            $competitionId,
            &$drawWinners
        ) {
            $competition = DB::table('my_competitions')
                ->where('id', $competitionId)
                ->lockForUpdate()
                ->first();

            if (!$competition) {
                return [
                    'success' => false,
                    'message' => 'Competition not found.',
                ];
            }

            if ($competition->status === 'completed') {
                return [
                    'success' => false,
                    'message' => 'This competition has already been completed.',
                ];
            }

            if ($competition->status !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Only active competitions can be completed.',
                ];
            }

            // Get active Scheduled Draw prizes.
            $prizes = DB::table('competition_prizes')
                ->where('competition_id', $competitionId)
                ->where('winning_method', 'draw')
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            // No Scheduled Draw prizes is allowed.
            // Instant Win-only competitions can still be completed.
            if ($prizes->isNotEmpty()) {
                foreach ($prizes as $prize) {
                    if ((int) $prize->quantity < 1) {
                        return [
                            'success' => false,
                            'message' => "Invalid quantity for prize: {$prize->name}.",
                        ];
                    }
                }

                $requiredWinners = (int) $prizes->sum('quantity');

                // Tickets that already won any prize are excluded.
                $eligibleTickets = DB::table('competition_tickets as t')
                    ->where('t.competition_id', $competitionId)
                    ->where('t.status', 'assigned')
                    ->whereNotNull('t.user_id')
                    ->whereNotNull('t.order_id')
                    ->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('prize_winning_tickets as pwt')
                            ->whereColumn('pwt.ticket_id', 't.id');
                    })
                    ->count();

                if ($eligibleTickets < $requiredWinners) {
                    return [
                        'success' => false,
                        'message' => "Not enough eligible tickets. Required: {$requiredWinners}; available: {$eligibleTickets}.",
                    ];
                }

                // Randomly select unique eligible tickets.
                $selectedTickets = DB::table('competition_tickets as t')
                    ->where('t.competition_id', $competitionId)
                    ->where('t.status', 'assigned')
                    ->whereNotNull('t.user_id')
                    ->whereNotNull('t.order_id')
                    ->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('prize_winning_tickets as pwt')
                            ->whereColumn('pwt.ticket_id', 't.id');
                    })
                    ->inRandomOrder()
                    ->limit($requiredWinners)
                    ->lockForUpdate()
                    ->get([
                        't.id',
                        't.user_id',
                        't.order_id',
                        't.ticket_number',
                    ]);

                if ($selectedTickets->count() !== $requiredWinners) {
                    return [
                        'success' => false,
                        'message' => 'Unable to select enough eligible tickets. Please try again.',
                    ];
                }

                $allocationRows = [];
                $ticketOffset = 0;
                $now = now();

                // Assign selected tickets to prizes in configured order.
                foreach ($prizes as $prize) {
                    $quantity = (int) $prize->quantity;

                    $prizeTickets = $selectedTickets->slice(
                        $ticketOffset,
                        $quantity
                    );

                    $ticketOffset += $quantity;

                    foreach ($prizeTickets as $ticket) {
                        $allocationRows[] = [
                            'competition_id' => $competitionId,
                            'prize_id' => $prize->id,
                            'ticket_id' => $ticket->id,
                            'winning_method' => 'draw',
                            'status' => 'won',
                            'winner_user_id' => $ticket->user_id,
                            'order_id' => $ticket->order_id,
                            'won_at' => $now,
                            'claimed_at' => null,
                            'fulfilled_at' => null,
                            'notes' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $drawWinners[] = [
                            'user_id' => $ticket->user_id,
                            'ticket_id' => $ticket->id,
                            'prize_name' => $prize->name,
                            'ticket_number' => $ticket->ticket_number,
                        ];
                    }
                }

                if (!empty($allocationRows)) {
                    DB::table('prize_winning_tickets')
                        ->insert($allocationRows);

                    $winningRecords = DB::table('prize_winning_tickets')
                        ->where('competition_id', $competitionId)
                        ->where('winning_method', 'draw')
                        ->whereIn(
                            'ticket_id',
                            $selectedTickets->pluck('id')->all()
                        )
                        ->get(['id', 'ticket_id'])
                        ->keyBy('ticket_id');

                    foreach ($drawWinners as &$winner) {
                        $winningRecord = $winningRecords->get(
                            $winner['ticket_id']
                        );

                        $winner['winning_id'] = $winningRecord
                            ? $winningRecord->id
                            : null;
                    }

                    unset($winner);
                }
            }

            // Complete the competition, including Instant Win-only competitions.
            $now = now();

            DB::table('my_competitions')
                ->where('id', $competitionId)
                ->update([
                    'status' => 'completed',
                    'completed_at' => $now,
                    'updated_at' => $now,
                ]);

            $totalWinners = count($drawWinners);
            $hasScheduledDrawPrizes = $prizes->isNotEmpty();

            return [
                'success' => true,
                'message' => $hasScheduledDrawPrizes
                    ? "{$totalWinners} scheduled draw winners selected successfully. Competition completed."
                    : 'Competition completed successfully. No Scheduled Draw prizes were configured.',
                'competition_title' => $competition->title,
                'total_winners' => $totalWinners,
                'has_scheduled_draw_prizes' => $hasScheduledDrawPrizes,
            ];
        }, 3);

        // Notifications are sent only after the transaction commits.
        if ($result['success']) {
            try {
                $notificationService = app(NotificationService::class);

                foreach ($drawWinners as $winner) {
                    $user = User::find($winner['user_id']);

                    if (!$user) {
                        continue;
                    }

                    $winningTicketUrl = $winner['winning_id']
                        ? route('user.prizes.show', [
                            'winningTicket' => Crypt::encryptString(
                                (string) $winner['winning_id']
                            ),
                        ])
                        : route('user.dashboard');

                    $notificationService->send(
                        $user,
                        'Congratulations! You Won!',
                        "You have won {$winner['prize_name']} in the Scheduled Draw! Your winning ticket number is {$winner['ticket_number']}. Click to view your prize.",
                        $winningTicketUrl,
                        'scheduled_draw'
                    );
                }

                $notificationTitle = $result['has_scheduled_draw_prizes']
                    ? 'Scheduled Draw Completed'
                    : 'Competition Completed';

                $notificationMessage = $result['has_scheduled_draw_prizes']
                    ? "{$result['total_winners']} winners have been selected for {$result['competition_title']}."
                    : "{$result['competition_title']} has been completed. No Scheduled Draw prizes were configured.";

                $notificationService->notifyAdmins(
                    $notificationTitle,
                    $notificationMessage,
                    route('admin.tickets.show', [
                        'competitionId' => Crypt::encryptString(
                            (string) $competitionId
                        ),
                    ]),
                    $result['has_scheduled_draw_prizes']
                        ? 'scheduled_draw'
                        : 'competition_completed'
                );
            } catch (\Throwable $exception) {
                // Notification failure must not undo competition completion.
                report($exception);
            }
        }

        return $result;
    }
}
