<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\{Card, Question, Choice, ReviewLog, Subject};
use App\Services\SrsScheduler;

class SrsController extends Controller
{
    public function due(Request $request)
    {
        $request->validate([
            'subject' => ['nullable','string'], // e.g., FAR
            'limit'   => ['nullable','integer','min:1','max:50'],
        ]);
        $user = $request->user();
        $limit = $request->integer('limit', 10);
        $subjectCode = $request->string('subject')->toString();

        // 1) Fetch due cards
        $dueQuery = Card::query()
            ->where('user_id', $user->id)
            ->where('suspended', false)
            ->where('due_at', '<=', Carbon::now())
            ->orderBy('due_at');

        if ($subjectCode) {
            $dueQuery->whereHas('question.subject', fn($q) => $q->where('code', $subjectCode));
        }

        $dueCards = $dueQuery->with(['question:id,subject_id,stem,explanation', 'question.subject:id,code', 'question.choices:id,question_id,label,text,order'])
            ->limit($limit)
            ->get();

        // 2) If not enough, backfill with "new" questions (no card yet)
        if ($dueCards->count() < $limit) {
            $needed = $limit - $dueCards->count();

            $newQ = Question::query()
                ->when($subjectCode, fn($q) => $q->whereHas('subject', fn($s) => $s->where('code',$subjectCode)))
                ->whereDoesntHave('cards', fn($q) => $q->where('user_id', $user->id))
                ->inRandomOrder()
                ->limit($needed)
                ->get(['id','subject_id','stem','explanation']);

            foreach ($newQ as $q) {
                $card = Card::create([
                    'user_id'      => $user->id,
                    'question_id'  => $q->id,
                    'due_at'       => Carbon::now(), // due immediately
                    'interval'     => 0,
                    'repetitions'  => 0,
                    'lapses'       => 0,
                    'ease'         => 250, // 2.50
                    'suspended'    => false,
                ]);
                $card->load(['question:id,subject_id,stem,explanation', 'question.subject:id,code', 'question.choices:id,question_id,label,text,order']);
                $dueCards->push($card);
            }
        }

        // API response: do NOT expose is_correct
        return $dueCards->map(function(Card $card){
            return [
                'card_id'   => $card->id,
                'due_at'    => $card->due_at,
                'subject'   => $card->question->subject->code ?? null,
                'question'  => [
                    'id'    => $card->question->id,
                    'stem'  => $card->question->stem,
                    // explanation returned after review, not here
                    'choices' => $card->question->choices->sortBy('order')->values()->map(fn(Choice $c)=>[
                        'id' => $c->id,
                        'label' => $c->label,
                        'text' => $c->text,
                    ]),
                ],
            ];
        })->values();
    }

    public function practice(Request $request)
    {
        $request->validate([
            'subject' => ['nullable','string'],
            'limit'   => ['nullable','integer','min:1','max:50'],
        ]);
        $user = $request->user();
        $limit = $request->integer('limit', 10);
        $subjectCode = $request->string('subject')->toString();

        // Fetch cards for practice mode (ignoring due_at)
        // Strategy: Mix recently reviewed cards + random cards for variety
        
        $baseQuery = Card::query()
            ->where('user_id', $user->id)
            ->where('suspended', false);

        if ($subjectCode) {
            $baseQuery->whereHas('question.subject', fn($q) => $q->where('code', $subjectCode));
        }

        // Get recently reviewed cards (up to half the limit)
        $recentLimit = (int) ceil($limit / 2);
        $recentCards = (clone $baseQuery)
            ->whereNotNull('last_reviewed_at')
            ->orderBy('last_reviewed_at', 'desc')
            ->with(['question:id,subject_id,stem,explanation', 'question.subject:id,code', 'question.choices:id,question_id,label,text,order'])
            ->limit($recentLimit)
            ->get();

        // Fill the rest with random cards
        $randomNeeded = $limit - $recentCards->count();
        $recentIds = $recentCards->pluck('id')->toArray();
        
        $randomCards = collect();
        if ($randomNeeded > 0) {
            $randomCards = (clone $baseQuery)
                ->when(!empty($recentIds), fn($q) => $q->whereNotIn('id', $recentIds))
                ->inRandomOrder()
                ->with(['question:id,subject_id,stem,explanation', 'question.subject:id,code', 'question.choices:id,question_id,label,text,order'])
                ->limit($randomNeeded)
                ->get();
        }

        // Combine and shuffle
        $practiceCards = $recentCards->merge($randomCards)->shuffle()->take($limit);

        // If still not enough, create cards for new questions
        if ($practiceCards->count() < $limit) {
            $needed = $limit - $practiceCards->count();
            $existingQuestionIds = Card::where('user_id', $user->id)
                ->pluck('question_id')
                ->toArray();

            $newQ = Question::query()
                ->when($subjectCode, fn($q) => $q->whereHas('subject', fn($s) => $s->where('code',$subjectCode)))
                ->whereNotIn('id', $existingQuestionIds)
                ->inRandomOrder()
                ->limit($needed)
                ->get(['id','subject_id','stem','explanation']);

            foreach ($newQ as $q) {
                $card = Card::create([
                    'user_id'      => $user->id,
                    'question_id'  => $q->id,
                    'due_at'       => Carbon::now(),
                    'interval'     => 0,
                    'repetitions'  => 0,
                    'lapses'       => 0,
                    'ease'         => 250,
                    'suspended'    => false,
                ]);
                $card->load(['question:id,subject_id,stem,explanation', 'question.subject:id,code', 'question.choices:id,question_id,label,text,order']);
                $practiceCards->push($card);
            }
        }

        // Return same format as due() endpoint
        return $practiceCards->map(function(Card $card){
            return [
                'card_id'   => $card->id,
                'due_at'    => $card->due_at,
                'subject'   => $card->question->subject->code ?? null,
                'question'  => [
                    'id'    => $card->question->id,
                    'stem'  => $card->question->stem,
                    'choices' => $card->question->choices->sortBy('order')->values()->map(fn(Choice $c)=>[
                        'id' => $c->id,
                        'label' => $c->label,
                        'text' => $c->text,
                    ]),
                ],
            ];
        })->values();
    }

    public function review(Request $request, SrsScheduler $scheduler)
    {
        $data = $request->validate([
            'card_id'    => ['required','integer','exists:cards,id'],
            'choice_id'  => ['nullable','integer','exists:choices,id'],
            'grade'      => ['required','in:again,hard,good,easy'],
            'duration_ms'=> ['nullable','integer','min:0','max:120000'], // optional
        ]);

        $user = $request->user();

        $card = Card::with(['question.choices'])->where('id',$data['card_id'])->where('user_id',$user->id)->firstOrFail();

        // Determine correctness if choice submitted
        $isCorrect = null;
        if (!empty($data['choice_id'])) {
            $picked = $card->question->choices->firstWhere('id', (int) $data['choice_id']);
            abort_unless($picked, 422, 'Choice not in this question.');
            $isCorrect = (bool) $picked->is_correct;
        }

        // Schedule next review
        $cardBefore = clone $card;
        $card = $scheduler->schedule($card, $data['grade']);

        // Log review
        ReviewLog::create([
            'card_id'          => $card->id,
            'user_id'          => $user->id,
            'question_id'      => $card->question_id,
            'grade'            => $data['grade'],
            'duration_ms'      => $data['duration_ms'] ?? 0,
            'interval_before'  => $cardBefore->interval,
            'interval_after'   => $card->interval,
            'ease_before'      => $cardBefore->ease,
            'ease_after'       => $card->ease,
        ]);

        // Return feedback (now it's okay to reveal correctness + explanation)
        return response()->json([
            'card_id'         => $card->id,
            'correct'         => $isCorrect,
            'next_due_at'     => $card->due_at,
            'next_interval_d' => $card->interval,
            'ease'            => $card->ease / 100.0,
            'explanation'     => $card->question->explanation,
        ]);
    }
}
