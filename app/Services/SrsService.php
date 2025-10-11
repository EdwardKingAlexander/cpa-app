<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Card;

class SrsScheduler
{
    // SM-2 defaults:
    // - ease starts at 2.50 (we store 250)
    // - intervals in days; first 1, then 6 if "Good"
    // Adjust bounds for ease to avoid degeneracy.
    const EASE_MIN = 130; // 1.30
    const EASE_MAX = 350; // 3.50

    public function schedule(Card $card, string $grade): Card
    {
        $now = Carbon::now();

        $easeBefore = $card->ease;
        $intervalBefore = $card->interval;

        // Map grade → q (quality 0..3) and ease deltas (SM-2 inspired)
        // again=0, hard=1, good=2, easy=3
        $map = [
            'again' => ['q' => 0, 'easeDelta' => -20],
            'hard'  => ['q' => 1, 'easeDelta' => -15],
            'good'  => ['q' => 2, 'easeDelta' => 0],
            'easy'  => ['q' => 3, 'easeDelta' => +15],
        ];
        if (! isset($map[$grade])) {
            throw new \InvalidArgumentException('Invalid grade.');
        }
        $q = $map[$grade]['q'];

        // Update ease (in basis points)
        $card->ease = max(self::EASE_MIN, min(self::EASE_MAX, $card->ease + $map[$grade]['easeDelta']));

        if ($q < 2) {
            // Fail: reset repetitions, increment lapses, short interval (10 min)
            $card->repetitions = 0;
            $card->lapses += 1;
            $card->interval = 0;
            $card->due_at = $now->addMinutes(10);
        } else {
            // Pass: compute next interval
            if ($card->repetitions === 0) {
                // first successful review
                $card->interval = 1;
            } elseif ($card->repetitions === 1) {
                $card->interval = 6;
            } else {
                // next = prev * ease
                $card->interval = (int) round($card->interval * ($card->ease / 100.0));
            }

            // Slight boost if "easy"
            if ($grade === 'easy') {
                $card->interval = (int) round($card->interval * 1.3);
            }

            $card->repetitions += 1;
            $card->due_at = $now->copy()->addDays(max(1, $card->interval));
        }

        $card->save();

        // Return some context (caller will log)
        $card->setAttribute('interval_before', $intervalBefore);
        $card->setAttribute('ease_before', $easeBefore);

        return $card;
    }
}
