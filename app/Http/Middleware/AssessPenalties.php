<?php

namespace App\Http\Middleware;

use App\Models\SystemLog;
use App\Services\PenaltyAssessor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs the daily portfolio-wide penalty sweep off the application's own traffic.
 *
 * This deployment has no scheduler: it is a Windows/XAMPP box that is powered
 * off overnight, so a wall-clock trigger would never fire. Staff using the app
 * during office hours is the only reliable execution window, so their first
 * request of the day carries the sweep.
 *
 * Deliberately NOT queued - QUEUE_CONNECTION is database but no worker runs, so
 * a queued job would sit in the jobs table forever, which is the same failure as
 * the cron that never ran.
 */
class AssessPenalties
{
    private const MARKER = 'penalties.last_assessed_on';
    private const LOCK = 'penalties.sweep';

    public function handle(Request $request, Closure $next): Response
    {
        if($this->shouldSweep($request)) $this->sweep();

        return $next($request);
    }

    private function shouldSweep(Request $request): bool
    {
        // Only on ordinary page loads, and never for guests.
        if(!$request->isMethod('GET') || !$request->user()) return false;

        // Off entirely until the owner sets a cutover date.
        if(!app(PenaltyAssessor::class)->isAutomaticAssessmentEnabled()) return false;

        return Cache::get(self::MARKER) !== Carbon::now()->toDateString();
    }

    /**
     * A failure here must never take down the page the user asked for, so this
     * swallows exceptions after logging them. The marker is only written on
     * success, so a failed sweep is retried on the next request rather than
     * being silently skipped for the day.
     */
    private function sweep(): void
    {
        $lock = Cache::lock(self::LOCK, 120);

        // Another request is already sweeping - let it finish.
        if(!$lock->get()) return;

        try {
            $result = app(PenaltyAssessor::class)->sweep();

            Cache::put(self::MARKER, Carbon::now()->toDateString(), Carbon::now()->addDays(7));

            SystemLog::record(
                'penalty.assessed.auto',
                sprintf(
                    'Daily penalty assessment ran: %d penalt%s imposed, totalling %s.',
                    $result['count'],
                    $result['count'] == 1 ? 'y' : 'ies',
                    number_format($result['total'], 2)
                ),
                null,
                ['assessed' => $result]
            );
        } catch (\Throwable $ex) {
            Log::error('Automatic penalty assessment failed: ' . $ex->getMessage(), ['exception' => $ex]);
        } finally {
            $lock->release();
        }
    }
}
