<?php

namespace App\Console\Commands;

use App\Services\PenaltyAssessor;
use Illuminate\Console\Command;

class InspectForPenalty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspect-for-penalty {code} {--dry-run : List what would be imposed without writing anything} {--ignore-cutover : Assess loans released before the cutover date too}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imposes penalties on payment schedules left unsettled beyond their plan\'s grace period. Code: 1 - Bi-monthly, Code: 2 - Weekly and Arawan, Code: 3 - All';

    /**
     * The plan types each {code} covers, kept for backwards compatibility with
     * the original command.
     */
    private function planTypesFor(string $code): array {
        return match($code) {
            '1' => [PenaltyAssessor::BI_MONTHLY],
            '2' => [PenaltyAssessor::WEEKLY, PenaltyAssessor::ARAWAN],
            default => [PenaltyAssessor::BI_MONTHLY, PenaltyAssessor::WEEKLY, PenaltyAssessor::ARAWAN],
        };
    }

    public function handle(PenaltyAssessor $assessor)
    {
        $planTypes = $this->planTypesFor((string) $this->argument('code'));
        $ignoreCutover = (bool) $this->option('ignore-cutover');

        if(!$ignoreCutover && !$assessor->isAutomaticAssessmentEnabled()) {
            $this->warn('Automatic penalty assessment is disabled - PENALTY_ASSESSMENT_START_DATE is not set.');
            $this->line('Nothing was assessed. Set that date, or re-run with --ignore-cutover to assess regardless.');
            return self::SUCCESS;
        }

        if($this->option('dry-run')) {
            $candidates = $assessor->candidates(null, $ignoreCutover, $planTypes);

            $total = 0;
            foreach($candidates as $schedule) {
                $total += $assessor->penaltyAmountFor($schedule);
            }

            $this->info(sprintf(
                'Dry run: %d payment schedule(s) would be penalized, totalling %s. Nothing was written.',
                $candidates->count(),
                number_format($total, 2)
            ));

            return self::SUCCESS;
        }

        $result = $assessor->assess(null, null, $ignoreCutover, $planTypes);

        $this->info(sprintf(
            'Imposed %d penalt%s totalling %s.',
            $result['count'],
            $result['count'] == 1 ? 'y' : 'ies',
            number_format($result['total'], 2)
        ));

        return self::SUCCESS;
    }
}
