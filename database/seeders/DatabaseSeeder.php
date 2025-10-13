<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subject;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            User::updateOrCreate(
                ['email' => 'admin@cpa.test'],
                [
                    'name' => 'CPA Admin',
                    'password' => Hash::make('password'),
                ]
            );

            User::updateOrCreate(
                ['email' => 'student@cpa.test'],
                [
                    'name' => 'CPA Student',
                    'password' => Hash::make('password'),
                ]
            );

            foreach ($this->subjectSeedData() as $code => $subjectData) {
                $subject = Subject::updateOrCreate(
                    ['code' => $code],
                    ['name' => $subjectData['name']]
                );

                foreach ($subjectData['questions'] as $questionData) {
                    $question = Question::updateOrCreate(
                        [
                            'subject_id' => $subject->id,
                            'stem' => $questionData['stem'],
                        ],
                        [
                            'difficulty' => $questionData['difficulty'],
                            'explanation' => $questionData['explanation'],
                        ]
                    );

                    $question->choices()->delete();
                    $question->choices()->createMany(
                        collect($questionData['options'])
                            ->map(fn (string $choice, int $index) => [
                                'label' => chr(65 + $index),
                                'text' => $choice,
                                'is_correct' => $index === $questionData['correct'],
                                'order' => $index,
                            ])
                            ->all()
                    );
                }
            }
        });
    }

    protected function subjectSeedData(): array
    {
        return [
            'FAR' => [
                'name' => 'Financial Accounting and Reporting',
                'questions' => [
                    [
                        'stem' => 'Under ASC 606, revenue for a performance obligation satisfied over time is recognized when the entity can reasonably measure progress toward completion. Which measure best reflects this requirement?',
                        'difficulty' => 'medium',
                        'explanation' => 'ASC 606 permits input or output methods, but revenue must reflect the transfer of control to the customer.',
                        'options' => [
                            'When cash is collected in full',
                            'Evenly over the contract term regardless of performance',
                            'As control transfers using an output or input measure',
                            'Only after all performance obligations are complete',
                        ],
                        'correct' => 2,
                    ],
                    [
                        'stem' => 'Which lease classification results when a lessor transfers ownership at the end of the term and collectability is probable?',
                        'difficulty' => 'easy',
                        'explanation' => 'Ownership transfer and collectability indicate a sales-type lease for the lessor.',
                        'options' => [
                            'Direct financing lease',
                            'Sales-type lease',
                            'Operating lease',
                            'Short-term lease',
                        ],
                        'correct' => 1,
                    ],
                    [
                        'stem' => 'Which inventory costing method is prohibited under IFRS but allowed under U.S. GAAP?',
                        'difficulty' => 'easy',
                        'explanation' => 'IFRS prohibits the use of LIFO, while U.S. GAAP permits it.',
                        'options' => [
                            'FIFO',
                            'Weighted-average',
                            'Specific identification',
                            'LIFO',
                        ],
                        'correct' => 3,
                    ],
                    [
                        'stem' => 'When preparing consolidated financial statements, which entity is included?',
                        'difficulty' => 'medium',
                        'explanation' => 'The parent consolidates entities it controls, typically through majority voting interest or variable interest model.',
                        'options' => [
                            'A 20% equity method investee',
                            'A wholly owned subsidiary',
                            'A joint venture with shared control',
                            'An available-for-sale security',
                        ],
                        'correct' => 1,
                    ],
                    [
                        'stem' => 'Which section of the statement of cash flows includes cash paid for interest under U.S. GAAP?',
                        'difficulty' => 'medium',
                        'explanation' => 'U.S. GAAP classifies cash paid for interest as an operating activity.',
                        'options' => [
                            'Investing activities',
                            'Financing activities',
                            'Operating activities',
                            'Noncash supplemental disclosure',
                        ],
                        'correct' => 2,
                    ],
                ],
            ],
            'AFAR' => [
                'name' => 'Advanced Financial Accounting and Reporting',
                'questions' => [
                    [
                        'stem' => 'A foreign subsidiary with a functional currency different from the parent uses which rate for translating revenues under the current rate method?',
                        'difficulty' => 'medium',
                        'explanation' => 'Under the current rate method, income statement items are translated at the average exchange rate for the period.',
                        'options' => [
                            'Historical rate on the acquisition date',
                            'Current rate at period-end',
                            'Average rate for the period',
                            'Forward contract rate',
                        ],
                        'correct' => 2,
                    ],
                    [
                        'stem' => 'In a step acquisition, how is the previously held equity interest remeasured?',
                        'difficulty' => 'hard',
                        'explanation' => 'IFRS and U.S. GAAP require remeasurement to fair value with any gain or loss recognized in earnings.',
                        'options' => [
                            'At historical carrying amount',
                            'At current fair value with gain or loss in earnings',
                            'At book value with OCI adjustment',
                            'It is eliminated with no recognition',
                        ],
                        'correct' => 1,
                    ],
                    [
                        'stem' => 'Which entity must present segment disclosures under ASC 280?',
                        'difficulty' => 'easy',
                        'explanation' => 'Public business enterprises are required to disclose segment information under ASC 280.',
                        'options' => [
                            'Privately held companies only',
                            'Public business enterprises',
                            'All not-for-profit entities',
                            'Employee benefit plans',
                        ],
                        'correct' => 1,
                    ],
                    [
                        'stem' => 'When a parent loses control of a subsidiary, how is any retained noncontrolling investment measured?',
                        'difficulty' => 'medium',
                        'explanation' => 'The retained investment is measured at fair value and a gain or loss is recognized on disposal.',
                        'options' => [
                            'At carrying amount with no gain or loss',
                            'At fair value with gain or loss recognized',
                            'At historical cost adjusted for inflation',
                            'At expected future cash flows discounted',
                        ],
                        'correct' => 1,
                    ],
                    [
                        'stem' => 'For defined benefit plans, where is prior service cost initially recorded under U.S. GAAP?',
                        'difficulty' => 'medium',
                        'explanation' => 'Prior service cost is recorded in other comprehensive income and amortized to pension expense over service periods.',
                        'options' => [
                            'Current pension expense immediately',
                            'Other comprehensive income',
                            'Retained earnings directly',
                            'Revenue',
                        ],
                        'correct' => 1,
                    ],
                ],
            ],
            'MAS' => [
                'name' => 'Management Accounting and Services',
                'questions' => [
                    [
                        'stem' => 'Which cost is treated as a period cost in variable costing but inventoried under absorption costing?',
                        'difficulty' => 'medium',
                        'explanation' => 'Fixed manufacturing overhead is expensed immediately under variable costing but capitalized under absorption costing.',
                        'options' => [
                            'Direct materials',
                            'Direct labor',
                            'Variable manufacturing overhead',
                            'Fixed manufacturing overhead',
                        ],
                        'correct' => 3,
                    ],
                    [
                        'stem' => 'In a balanced scorecard, which perspective focuses on measures such as defect rates and cycle time?',
                        'difficulty' => 'easy',
                        'explanation' => 'Process efficiency metrics belong to the internal business process perspective.',
                        'options' => [
                            'Financial perspective',
                            'Customer perspective',
                            'Internal business process perspective',
                            'Learning and growth perspective',
                        ],
                        'correct' => 2,
                    ],
                    [
                        'stem' => 'Which capital budgeting technique considers the time value of money?',
                        'difficulty' => 'easy',
                        'explanation' => 'Net present value incorporates discounted cash flows and captures time value.',
                        'options' => [
                            'Payback period',
                            'Accounting rate of return',
                            'Net present value',
                            'Simple ROI',
                        ],
                        'correct' => 2,
                    ],
                    [
                        'stem' => 'When assessing relevant costs for a special order, which cost is irrelevant?',
                        'difficulty' => 'medium',
                        'explanation' => 'Sunk costs do not change with the decision and are therefore irrelevant.',
                        'options' => [
                            'Direct materials for the order',
                            'Additional shipping costs',
                            'Sunk development costs',
                            'Incremental labor costs',
                        ],
                        'correct' => 2,
                    ],
                    [
                        'stem' => 'A company uses activity-based costing. Which driver is most appropriate for machine setup costs?',
                        'difficulty' => 'medium',
                        'explanation' => 'Machine setup costs correlate best with the number of setups performed.',
                        'options' => [
                            'Machine hours',
                            'Units produced',
                            'Number of setups',
                            'Direct labor dollars',
                        ],
                        'correct' => 2,
                    ],
                ],
            ],
            'AUDIT' => [
                'name' => 'Auditing',
                'questions' => [
                    [
                        'stem' => 'Which component of audit risk is directly controlled by the auditor through the work performed?',
                        'difficulty' => 'medium',
                        'explanation' => 'Detection risk is controlled by the auditor via the nature, timing, and extent of procedures.',
                        'options' => [
                            'Inherent risk',
                            'Control risk',
                            'Detection risk',
                            'Engagement risk',
                        ],
                        'correct' => 2,
                    ],
                    [
                        'stem' => 'An auditor identifies significant deficiencies. To whom must these be communicated?',
                        'difficulty' => 'medium',
                        'explanation' => 'Significant deficiencies must be communicated to management and those charged with governance.',
                        'options' => [
                            'Only to the audit committee in writing',
                            'To management and those charged with governance',
                            'To regulators within 30 days',
                            'To all shareholders via the annual report',
                        ],
                        'correct' => 1,
                    ],
                    [
                        'stem' => 'Which procedure provides the most persuasive evidence about the existence of accounts receivable?',
                        'difficulty' => 'easy',
                        'explanation' => 'Confirmations sent directly to customers provide strong evidence of existence.',
                        'options' => [
                            'Analytical procedures on days sales outstanding',
                            'Recalculation of the aging schedule',
                            'External confirmations of balances',
                            'Observation of shipping documents',
                        ],
                        'correct' => 2,
                    ],
                    [
                        'stem' => 'If management refuses to allow the auditor to communicate with predecessor auditors, what is the most appropriate response?',
                        'difficulty' => 'hard',
                        'explanation' => 'Scope limitation at the acceptance stage typically leads the successor auditor to decline the engagement.',
                        'options' => [
                            'Issue a disclaimer of opinion',
                            'Increase planned detection risk',
                            'Decline the engagement',
                            'Proceed but note the limitation in the report',
                        ],
                        'correct' => 2,
                    ],
                    [
                        'stem' => 'Which opinion modification is required when the financial statements contain a material GAAP departure that is not pervasive?',
                        'difficulty' => 'medium',
                        'explanation' => 'A qualified opinion is appropriate for a material but not pervasive departure from GAAP.',
                        'options' => [
                            'Adverse opinion',
                            'Disclaimer of opinion',
                            'Qualified opinion',
                            'Unmodified opinion with emphasis paragraph',
                        ],
                        'correct' => 2,
                    ],
                ],
            ],
            'RFBT' => [
                'name' => 'Regulatory Framework for Business Transactions',
                'questions' => [
                    [
                        'stem' => 'Which business entity form offers limited liability to all owners and allows pass-through taxation by default?',
                        'difficulty' => 'easy',
                        'explanation' => 'A limited liability company (LLC) provides limited liability and is treated as a pass-through entity unless it elects otherwise.',
                        'options' => [
                            'General partnership',
                            'Sole proprietorship',
                            'Limited liability company',
                            'C corporation',
                        ],
                        'correct' => 2,
                    ],
                    [
                        'stem' => 'Under the UCC, which event creates a firm offer that cannot be revoked for a stated period?',
                        'difficulty' => 'medium',
                        'explanation' => 'A signed writing from a merchant assuring the offer will be held open creates a firm offer under UCC Article 2.',
                        'options' => [
                            'An oral promise to hold the offer open',
                            'A merchant’s signed written assurance to keep the offer open',
                            'Payment of consideration by the offeree',
                            'Acceptance by the offeree',
                        ],
                        'correct' => 1,
                    ],
                    [
                        'stem' => 'Which bankruptcy chapter allows the reorganization of a business under court supervision while it continues operations?',
                        'difficulty' => 'easy',
                        'explanation' => 'Chapter 11 permits business reorganization with debtor-in-possession operations.',
                        'options' => [
                            'Chapter 7',
                            'Chapter 9',
                            'Chapter 11',
                            'Chapter 13',
                        ],
                        'correct' => 2,
                    ],
                    [
                        'stem' => 'In contract law, consideration must be present to form a valid contract. Which item constitutes valid consideration?',
                        'difficulty' => 'medium',
                        'explanation' => 'A bargained-for exchange of legal value, such as a promise to perform, is valid consideration.',
                        'options' => [
                            'A moral obligation with no promise to act',
                            'A past performance already completed',
                            'A bargained-for promise to deliver goods',
                            'A gift promise without exchange',
                        ],
                        'correct' => 2,
                    ],
                    [
                        'stem' => 'Which document typically governs the rights and responsibilities among partners in a limited partnership?',
                        'difficulty' => 'medium',
                        'explanation' => 'A limited partnership agreement (or partnership agreement) defines partner rights, profit sharing, and management.',
                        'options' => [
                            'Corporate bylaws',
                            'Operating agreement',
                            'Limited partnership agreement',
                            'Articles of incorporation',
                        ],
                        'correct' => 2,
                    ],
                ],
            ],
            'TAX' => [
                'name' => 'Taxation',
                'questions' => [
                    [
                        'stem' => 'Which filing status generally provides the lowest tax rate for a qualifying widow or widower with a dependent child?',
                        'difficulty' => 'easy',
                        'explanation' => 'Qualifying widow(er) with dependent child retains the joint return rates for the two years following the spouse’s death.',
                        'options' => [
                            'Married filing separately',
                            'Single',
                            'Qualifying widow(er) with dependent child',
                            'Head of household',
                        ],
                        'correct' => 2,
                    ],
                    [
                        'stem' => 'Which itemized deduction is subject to the 7.5% of AGI floor for individual taxpayers?',
                        'difficulty' => 'medium',
                        'explanation' => 'Only unreimbursed medical expenses above 7.5% of AGI are deductible.',
                        'options' => [
                            'State income taxes paid',
                            'Qualified charitable contributions',
                            'Mortgage interest on a primary residence',
                            'Unreimbursed medical expenses',
                        ],
                        'correct' => 3,
                    ],
                    [
                        'stem' => 'Which corporate tax credit offsets a corporation’s regular tax liability but not its alternative minimum tax?',
                        'difficulty' => 'medium',
                        'explanation' => 'The general business credit reduces regular tax liability and has limited carryforward/carryback provisions.',
                        'options' => [
                            'Foreign tax credit',
                            'General business credit',
                            'Earned income credit',
                            'Child tax credit',
                        ],
                        'correct' => 1,
                    ],
                    [
                        'stem' => 'What is the tax treatment of Section 1231 assets that generate a net gain for the year?',
                        'difficulty' => 'hard',
                        'explanation' => 'Net Section 1231 gains are treated as long-term capital gains, subject to lookback rules for prior losses.',
                        'options' => [
                            'Ordinary income in all cases',
                            'Tax-exempt income',
                            'Long-term capital gain',
                            'Short-term capital gain',
                        ],
                        'correct' => 2,
                    ],
                    [
                        'stem' => 'Which partnership item must be separately stated on a Schedule K-1 to partners?',
                        'difficulty' => 'medium',
                        'explanation' => 'Items that retain their character for partners, such as charitable contributions, are separately stated.',
                        'options' => [
                            'Cost of goods sold',
                            'Tax depreciation',
                            'Charitable contributions',
                            'Guaranteed payments',
                        ],
                        'correct' => 2,
                    ],
                ],
            ],
        ];
    }
}

/**
 * README
 * --------------------------------------------------------------------------
 * Seed database:       php artisan migrate:fresh --seed
 * Run CLI smoke test:  php artisan srs:test
 * Postman verification:
 *   - Authenticate via Sanctum and GET  /api/v1/srs/due
 *   - Submit review with POST /api/v1/srs/review (card_id + grade)
 * --------------------------------------------------------------------------
 */
