<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\SobrietyDate;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MembersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = [
            ['name' => 'John Smith', 'email' => 'john.smith@example.com'],
            ['name' => 'Sarah Johnson', 'email' => 'sarah.johnson@example.com'],
            ['name' => 'Michael Davis', 'email' => 'michael.davis@example.com'],
            ['name' => 'Emily Wilson', 'email' => 'emily.wilson@example.com'],
            ['name' => 'David Brown', 'email' => 'david.brown@example.com'],
            ['name' => 'Jessica Taylor', 'email' => 'jessica.taylor@example.com'],
            ['name' => 'Christopher Anderson', 'email' => 'christopher.anderson@example.com'],
            ['name' => 'Amanda Martinez', 'email' => 'amanda.martinez@example.com'],
            ['name' => 'Matthew Thomas', 'email' => 'matthew.thomas@example.com'],
            ['name' => 'Lisa Garcia', 'email' => 'lisa.garcia@example.com'],
            ['name' => 'Daniel Rodriguez', 'email' => 'daniel.rodriguez@example.com'],
            ['name' => 'Jennifer Lopez', 'email' => 'jennifer.lopez@example.com'],
            ['name' => 'Kevin White', 'email' => 'kevin.white@example.com'],
            ['name' => 'Michelle Lee', 'email' => 'michelle.lee@example.com'],
            ['name' => 'Ryan Miller', 'email' => 'ryan.miller@example.com'],
            ['name' => 'Nicole Harris', 'email' => 'nicole.harris@example.com'],
            ['name' => 'Andrew Clark', 'email' => 'andrew.clark@example.com'],
            ['name' => 'Stephanie Lewis', 'email' => 'stephanie.lewis@example.com'],
            ['name' => 'Brandon Walker', 'email' => 'brandon.walker@example.com'],
            ['name' => 'Rachel Hall', 'email' => 'rachel.hall@example.com'],
            ['name' => 'Justin Allen', 'email' => 'justin.allen@example.com'],
            ['name' => 'Samantha Young', 'email' => 'samantha.young@example.com'],
            ['name' => 'Tyler King', 'email' => 'tyler.king@example.com'],
            ['name' => 'Ashley Wright', 'email' => 'ashley.wright@example.com'],
            ['name' => 'Joshua Scott', 'email' => 'joshua.scott@example.com'],
            ['name' => 'Megan Green', 'email' => 'megan.green@example.com'],
            ['name' => 'Nathan Adams', 'email' => 'nathan.adams@example.com'],
            ['name' => 'Kayla Baker', 'email' => 'kayla.baker@example.com'],
            ['name' => 'Jacob Nelson', 'email' => 'jacob.nelson@example.com'],
            ['name' => 'Hannah Carter', 'email' => 'hannah.carter@example.com'],
        ];

        foreach ($members as $index => $memberData) {
            $member = Member::create($memberData);

            // For the first 5 members, create sobriety dates within the last year
            if ($index < 5) {
                // Create dates between 30 days and 11 months ago for variety
                $startDate = Carbon::now()->subMonths(11);
                $endDate = Carbon::now()->subDays(30);
                $randomDate = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                );
            } else {
                // For remaining members, create dates between 1-20 years ago
                $startDate = Carbon::now()->subYears(20);
                $endDate = Carbon::now()->subYear(); // At least 1 year ago
                $randomDate = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                );
            }

            SobrietyDate::create([
                'member_id' => $member->id,
                'sobriety_date' => $randomDate->format('Y-m-d'),
            ]);
        }
    }
}
