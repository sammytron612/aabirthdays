<?php

namespace App\Console\Commands;

use App\Models\Member;
use Illuminate\Console\Command;

class ShowMembersData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show members and their sobriety data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Members and Sobriety Data:');
        $this->line('');

        $members = Member::with('sobrietyDates')->take(10)->get();

        foreach ($members as $member) {
            $sobrietyDate = $member->sobrietyDates->first();
            $this->line(sprintf(
                '%s (%s) - Sober since: %s (%d days, %s)',
                $member->name,
                $member->email,
                $sobrietyDate->sobriety_date->format('M j, Y'),
                $sobrietyDate->daysSober(),
                $sobrietyDate->formattedTimeSober()
            ));
        }

        $this->line('');
        $this->info(sprintf('Total Members: %d', Member::count()));
        $this->info(sprintf('Total Sobriety Dates: %d', \App\Models\SobrietyDate::count()));
    }
}
