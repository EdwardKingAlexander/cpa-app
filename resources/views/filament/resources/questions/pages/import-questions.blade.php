<x-filament-panels::page>
    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-3">
            <x-filament::button type="submit">
                Import Questions
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="{{ \App\Filament\Resources\QuestionResource::getUrl() }}"
                color="gray"
            >
                Cancel
            </x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-8">
        <x-slot name="heading">
            CSV Format
        </x-slot>

        <div class="space-y-2 text-sm leading-relaxed">
            <p>Columns: <code>stem</code>, <code>difficulty</code>, <code>explanation</code>, <code>choice_a</code>, <code>choice_b</code>, <code>choice_c</code>, <code>choice_d</code>, <code>correct</code>.</p>
            <p>The <code>correct</code> column accepts the letter for the correct choice (A-D). Additional choice columns (<code>choice_e</code>, <code>choice_f</code>) are optional.</p>
        </div>
    </x-filament::section>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            JSON Example
        </x-slot>

        <pre class="overflow-x-auto rounded-lg bg-gray-950/90 p-4 text-sm text-gray-100">
[
    {
        "stem": "Which qualitative characteristic enables comparisons?",
        "difficulty": "medium",
        "explanation": "Comparability lets users identify similarities and differences.",
        "choices": [
            {"label": "A", "text": "Relevance", "is_correct": false},
            {"label": "B", "text": "Faithful representation", "is_correct": false},
            {"label": "C", "text": "Comparability", "is_correct": true},
            {"label": "D", "text": "Verifiability", "is_correct": false}
        ]
    }
]</pre>
    </x-filament::section>
</x-filament-panels::page>

