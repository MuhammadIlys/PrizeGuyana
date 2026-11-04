<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrizeGuyanaDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();

            /*
             * These categories are created only when their slugs do not exist.
             * Existing categories and unrelated records are preserved.
             */
            $categories = [
                ['name' => 'Cars & Vehicles', 'slug' => 'cars-vehicles', 'description' => 'Cars, motorcycles and other vehicles.'],
                ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Phones, computers and consumer electronics.'],
                ['name' => 'Cash Prizes', 'slug' => 'cash-prizes', 'description' => 'Cash giveaways and money prizes.'],
                ['name' => 'Home & Lifestyle', 'slug' => 'home-lifestyle', 'description' => 'Home appliances and lifestyle prizes.'],
                ['name' => 'Luxury & Accessories', 'slug' => 'luxury-accessories', 'description' => 'Watches, accessories and premium goods.'],
                ['name' => 'Bundles & Experiences', 'slug' => 'bundles-experiences', 'description' => 'Prize bundles and special experiences.'],
            ];

            foreach ($categories as $category) {
                $categoryValues = [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                    'sort_order' => array_search($category, $categories, true) + 1,
                    'updated_at' => $now,
                ];

                $existingCategory = DB::table('competition_categories')
                    ->where('slug', $category['slug'])
                    ->first();

                if ($existingCategory) {
                    DB::table('competition_categories')
                        ->where('id', $existingCategory->id)
                        ->update($categoryValues);
                } else {
                    DB::table('competition_categories')->insert($categoryValues + [
                        'slug' => $category['slug'],
                        'created_at' => $now,
                    ]);
                }
            }

            $categoryIds = DB::table('competition_categories')
                ->whereIn('slug', array_column($categories, 'slug'))
                ->pluck('id', 'slug');

            /*
             * Reusable demo qualification questions. IDs are looked up below,
             * so this works even when the database already contains questions.
             */
            $questions = [
                [
                    'slug' => 'demo-guyana-capital',
                    'title' => 'Guyana General Knowledge',
                    'question' => 'What is the capital city of Guyana?',
                    'option_a' => 'Georgetown',
                    'option_b' => 'Linden',
                    'option_c' => 'New Amsterdam',
                    'option_d' => 'Bartica',
                    'correct_answer' => 'a',
                ],
                [
                    'slug' => 'demo-guyana-currency',
                    'title' => 'Guyana Currency',
                    'question' => 'What is the official currency of Guyana?',
                    'option_a' => 'Barbados dollar',
                    'option_b' => 'Guyanese dollar',
                    'option_c' => 'Trinidad and Tobago dollar',
                    'option_d' => 'Jamaican dollar',
                    'correct_answer' => 'b',
                ],
                [
                    'slug' => 'demo-car-safety',
                    'title' => 'Vehicle Knowledge',
                    'question' => 'Which item is essential for driver safety?',
                    'option_a' => 'Seat belt',
                    'option_b' => 'Sunroof',
                    'option_c' => 'Spoiler',
                    'option_d' => 'Decorative lights',
                    'correct_answer' => 'a',
                ],
            ];

            foreach ($questions as $question) {
                $questionValues = $question + [
                    'is_active' => true,
                    'updated_at' => $now,
                ];

                $existingQuestion = DB::table('qualification_questions')
                    ->where('slug', $question['slug'])
                    ->first();

                if ($existingQuestion) {
                    DB::table('qualification_questions')
                        ->where('id', $existingQuestion->id)
                        ->update($questionValues);
                } else {
                    DB::table('qualification_questions')->insert($questionValues + [
                        'created_at' => $now,
                    ]);
                }
            }

            $questionIds = DB::table('qualification_questions')
                ->whereIn('slug', array_column($questions, 'slug'))
                ->orderBy('id')
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();

            /*
             * Relative dates keep homepage sections testable whenever the
             * seeder is run:
             * - Ending Today: entries 1–6
             * - Tomorrow: entries 7–11
             * - Future Selection: entries 12–30
             */
            $items = [
                ['Electric SUV Giveaway', 'cars-vehicles', 25, 1000, 'active', true, 0, 'Tesla Model Y', 42000, 'physical'],
                ['Win a Premium Smartphone', 'electronics', 5, 800, 'active', true, 0, 'Premium Smartphone', 1200, 'physical'],
                ['Guyana Cash Prize Draw', 'cash-prizes', 10, 1500, 'published', true, 0, 'G$500,000 Cash Prize', 2500, 'cash'],
                ['Luxury Watch Giveaway', 'luxury-accessories', 15, 600, 'active', true, 0, 'Luxury Wristwatch', 3500, 'physical'],
                ['Home Makeover Bundle', 'home-lifestyle', 8, 500, 'published', false, 0, 'Home Appliance Bundle', 2200, 'physical'],
                ['Ultimate Tech Bundle', 'electronics', 12, 750, 'active', true, 0, 'Laptop and Accessories Bundle', 2800, 'physical'],

                ['Motorcycle Giveaway', 'cars-vehicles', 20, 700, 'active', true, 1, 'New Motorcycle', 4200, 'physical'],
                ['Laptop for Work & Study', 'electronics', 8, 900, 'published', false, 1, 'Performance Laptop', 1800, 'physical'],
                ['Weekend Shopping Cash', 'cash-prizes', 5, 1000, 'active', true, 1, 'G$250,000 Cash Prize', 1250, 'cash'],
                ['Smart Home Starter Pack', 'home-lifestyle', 10, 450, 'published', false, 1, 'Smart Home Bundle', 950, 'physical'],
                ['Designer Accessories Bundle', 'luxury-accessories', 7, 550, 'active', true, 1, 'Designer Accessories Bundle', 1400, 'physical'],

                ['Family SUV Prize Draw', 'cars-vehicles', 30, 2000, 'active', true, 3, 'Family SUV', 38000, 'physical'],
                ['Gaming Setup Giveaway', 'electronics', 18, 1200, 'active', true, 4, 'Gaming PC Setup', 3200, 'physical'],
                ['G$1 Million Cash Draw', 'cash-prizes', 20, 2500, 'published', true, 5, 'G$1,000,000 Cash Prize', 5000, 'cash'],
                ['Premium Refrigerator Giveaway', 'home-lifestyle', 12, 650, 'active', false, 6, 'Premium Refrigerator', 1700, 'physical'],
                ['Gold-Tone Luxury Watch', 'luxury-accessories', 9, 800, 'published', true, 7, 'Luxury Watch', 2600, 'physical'],
                ['Electric Scooter Giveaway', 'cars-vehicles', 6, 500, 'active', false, 8, 'Electric Scooter', 900, 'physical'],
                ['Creator Camera Kit', 'electronics', 14, 850, 'active', true, 9, 'Mirrorless Camera Kit', 2400, 'physical'],
                ['Kitchen Upgrade Bundle', 'home-lifestyle', 8, 600, 'published', false, 10, 'Kitchen Appliance Bundle', 1300, 'physical'],
                ['Holiday Spending Cash', 'cash-prizes', 10, 1800, 'active', true, 12, 'G$350,000 Cash Prize', 1750, 'cash'],
                ['Premium Tablet Giveaway', 'electronics', 7, 700, 'published', false, 14, 'Premium Tablet', 1100, 'physical'],
                ['Motorbike & Safety Gear', 'cars-vehicles', 16, 900, 'active', true, 16, 'Motorbike and Safety Gear', 3900, 'physical'],
                ['Luxury Gift Collection', 'luxury-accessories', 10, 650, 'active', false, 18, 'Luxury Gift Collection', 1900, 'physical'],
                ['Living Room Refresh', 'home-lifestyle', 12, 700, 'published', false, 20, 'Living Room Furniture Bundle', 3000, 'physical'],
                ['Creator Studio Bundle', 'electronics', 15, 1000, 'active', true, 22, 'Creator Studio Bundle', 3600, 'physical'],
                ['Big Cash Bonanza', 'cash-prizes', 25, 3000, 'published', true, 25, 'G$750,000 Cash Prize', 3750, 'cash'],
                ['Premium Laptop Giveaway', 'electronics', 12, 950, 'active', false, 28, 'Premium Laptop', 2100, 'physical'],
                ['Family Appliance Bundle', 'home-lifestyle', 8, 500, 'active', false, 30, 'Family Appliance Bundle', 1600, 'physical'],
                ['Luxury Car Dream Draw', 'cars-vehicles', 35, 2500, 'published', true, 35, 'Luxury Sedan', 55000, 'physical'],
                ['Mega Prize Collection', 'bundles-experiences', 20, 1500, 'active', true, 40, 'Mega Prize Collection', 5000, 'physical'],
            ];

            foreach ($items as $index => $item) {
                [$title, $categorySlug, $price, $totalEntries, $status, $featured, $endOffsetDays, $prizeName, $prizeValue, $prizeType] = $item;

                $reference = sprintf('PG-DEMO-%03d', $index + 1);
                $startsAt = $now->copy()->subDays(1)->startOfDay();

                if ($endOffsetDays === 0) {
                    $endsAt = $now->copy()->endOfDay();
                } elseif ($endOffsetDays === 1) {
                    $endsAt = $now->copy()->addDay()->endOfDay();
                } else {
                    $endsAt = $now->copy()->addDays($endOffsetDays)->endOfDay();
                }

                $drawAt = $endsAt->copy()->addDays(2)->setTime(18, 0);
                if ($status === 'draft') {
                    $publishedAt = null;
                } else {
                    $publishedAt = $now->copy()->subHours(2);
                }

                $competitionData = [
                    'title' => $title,
                    'slug' => Str::slug($title),
                    'internal_reference' => $reference,
                    'short_description' => 'Enter for a chance to win ' . $prizeName . '. Review the competition details and terms before entering.',
                    'description' => 'Take part in this PrizeGuyana demo competition for a chance to win ' . $prizeName . '. Please review the eligibility requirements, entry details, prize information, and official terms before participating.',
                    'meta_title' => $title . ' | PrizeGuyana',
                    'meta_description' => 'Enter the PrizeGuyana ' . $title . ' competition for a chance to win ' . $prizeName . '. See full details and terms.',
                    'category_id' => $categoryIds[$categorySlug] ?? null,
                    'is_featured' => $featured,
                    'main_image' => $index % 2 === 0 ? '/images/featured/comp-1.jpg' : '/images/featured/comp-2.jpg',
                    'video_path' => null,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'draw_at' => $drawAt,
                    'entry_price' => number_format($price, 2, '.', ''),
                    'currency' => 'USD',
                    'total_entries' => $totalEntries,
                    'max_entries_per_customer' => 10,
                    'entry_allocation_method' => 'random',
                    'qualification_enabled' => $index % 4 === 0,
                    'instant_win_enabled' => $index % 5 === 0,
                    'draw_method' => 'random_draw',
                    'minimum_age' => 18,
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'updated_at' => $now,
                ];

                DB::table('my_competitions')->updateOrInsert(
                    ['internal_reference' => $reference],
                    $competitionData + ['created_at' => $now]
                );

                $competitionId = DB::table('my_competitions')
                    ->where('internal_reference', $reference)
                    ->value('id');

                $questionSelection = $questionIds
                    ? array_slice($questionIds, $index % count($questionIds), 1)
                    : [];

                DB::table('my_competitions')
                    ->where('id', $competitionId)
                    ->update([
                        'qualification_question_ids' => $index % 4 === 0
                            ? json_encode($questionSelection)
                            : null,
                    ]);

                // Each competition gets its own correctly linked terms version.
                DB::table('competition_terms_versions')->updateOrInsert(
                    ['competition_id' => $competitionId, 'version' => '1.0'],
                    [
                        'title' => 'Official Competition Terms',
                        'content' => "PrizeGuyana Demo Competition Terms\n\n"
                            . "1. Eligibility: Participants must meet the minimum age and geographic eligibility requirements displayed for this competition.\n"
                            . "2. Entries: Entry price, entry limits, and total available entries are shown on the competition page. Entries are subject to successful payment and platform confirmation.\n"
                            . "3. Prize: The listed prize is the prize offered for this demo competition. Any cash amount or stated value is shown for testing purposes.\n"
                            . "4. Winner selection: The winner is selected using the method displayed on the competition page, subject to verification and applicable rules.\n"
                            . "5. Verification: A selected winner may be asked to confirm identity, eligibility, and contact details before a prize is awarded.\n"
                            . "6. Demo data: These terms are sample content for development and testing. Replace them with reviewed, jurisdiction-appropriate official terms before launching a live competition.",
                        'is_active' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                $termsId = DB::table('competition_terms_versions')
                    ->where('competition_id', $competitionId)
                    ->where('version', '1.0')
                    ->value('id');

                DB::table('my_competitions')
                    ->where('id', $competitionId)
                    ->update(['terms_id' => $termsId]);

                // Idempotently seed one main prize and one small prize per competition.
                $prizes = [
                    [
                        'name' => $prizeName,
                        'description' => 'Main prize for ' . $title . '. Sample prize data for testing.',
                        'prize_role' => 'main',
                        'prize_type' => $prizeType,
                        'image_path' => null,
                        'prize_value' => number_format($prizeValue, 2, '.', ''),
                        'currency' => 'USD',
                        'quantity' => 1,
                        'winning_method' => 'draw',
                        'status' => 'active',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Bonus Gift Card',
                        'description' => 'Secondary sample prize for testing prize lists.',
                        'prize_role' => 'small',
                        'prize_type' => 'cash',
                        'image_path' => null,
                        'prize_value' => '50.00',
                        'currency' => 'USD',
                        'quantity' => 3,
                        'winning_method' => 'instant_win',
                        'status' => 'active',
                        'sort_order' => 2,
                    ],
                ];

                foreach ($prizes as $prize) {
                    DB::table('competition_prizes')->updateOrInsert(
                        ['competition_id' => $competitionId, 'name' => $prize['name']],
                        $prize + ['updated_at' => $now, 'created_at' => $now]
                    );
                }

                // The supplied migration defines competition_country with unique
                // (competition_id, country_code); use Guyana as demo eligibility.
                DB::table('competition_country')->updateOrInsert(
                    ['competition_id' => $competitionId, 'country_code' => 'GY'],
                    ['updated_at' => $now, 'created_at' => $now]
                );
            }
        });
    }
}
