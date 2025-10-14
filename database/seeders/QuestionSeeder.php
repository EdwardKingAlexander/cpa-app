<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSeeder extends Seeder
{
    /**
     * Seed comprehensive CPA exam questions across all subjects.
     * Each subject receives 20+ high-quality questions.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ($this->getQuestionData() as $code => $subjectData) {
                $subject = Subject::firstOrCreate(
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

                    // Delete existing choices and recreate to ensure consistency
                    $question->choices()->delete();
                    $question->choices()->createMany(
                        collect($questionData['options'])
                            ->map(fn (string $choice, int $index) => [
                                'label' => chr(65 + $index), // A, B, C, D
                                'text' => $choice,
                                'is_correct' => $index === $questionData['correct'],
                                'order' => $index,
                            ])
                            ->all()
                    );
                }
            }
        });

        $this->command->info('✓ Successfully seeded questions for all subjects');
    }

    /**
     * Get comprehensive question data for all subjects.
     */
    protected function getQuestionData(): array
    {
        return [
            'FAR' => [
                'name' => 'Financial Accounting and Reporting',
                'questions' => $this->getFARQuestions(),
            ],
            'AFAR' => [
                'name' => 'Advanced Financial Accounting and Reporting',
                'questions' => $this->getAFARQuestions(),
            ],
            'MAS' => [
                'name' => 'Management Accounting and Services',
                'questions' => $this->getMASQuestions(),
            ],
            'AUDIT' => [
                'name' => 'Auditing',
                'questions' => $this->getAuditQuestions(),
            ],
            'RFBT' => [
                'name' => 'Regulatory Framework for Business Transactions',
                'questions' => $this->getRFBTQuestions(),
            ],
            'TAX' => [
                'name' => 'Taxation',
                'questions' => $this->getTaxQuestions(),
            ],
        ];
    }

    protected function getFARQuestions(): array
    {
        return [
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
            [
                'stem' => 'A company changes from LIFO to FIFO inventory costing. How should this change be reported?',
                'difficulty' => 'medium',
                'explanation' => 'This is a change in accounting principle requiring retrospective application to prior periods.',
                'options' => [
                    'Prospectively with no prior period adjustment',
                    'Retrospectively by adjusting prior period financial statements',
                    'As a discontinued operation',
                    'In other comprehensive income',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which of the following is considered a change in accounting estimate?',
                'difficulty' => 'easy',
                'explanation' => 'Changes in useful life are changes in accounting estimates and are applied prospectively.',
                'options' => [
                    'Change from FIFO to weighted-average inventory method',
                    'Change in depreciation method',
                    'Change in useful life of equipment',
                    'Correction of a prior period error',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'Under the equity method of accounting, dividends received from an investee should be recorded as:',
                'difficulty' => 'easy',
                'explanation' => 'Dividends reduce the investment account under the equity method; they do not create income.',
                'options' => [
                    'Dividend income',
                    'A reduction of the investment account',
                    'Other comprehensive income',
                    'A liability',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which type of subsequent event requires adjustment to the financial statements?',
                'difficulty' => 'medium',
                'explanation' => 'Events that provide evidence of conditions that existed at the balance sheet date require adjustment.',
                'options' => [
                    'Loss of a major customer after year-end',
                    'Settlement of litigation for an amount different than accrued, where the litigation existed at year-end',
                    'Natural disaster after year-end',
                    'Issuance of bonds after year-end',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'What is the primary difference between a finance lease and an operating lease for the lessee under ASC 842?',
                'difficulty' => 'medium',
                'explanation' => 'Finance leases show interest and amortization separately; operating leases show single lease expense.',
                'options' => [
                    'Only finance leases are recorded on the balance sheet',
                    'Finance leases result in straight-line expense; operating leases result in front-loaded expense',
                    'Finance leases show interest expense and amortization; operating leases show single lease expense',
                    'Operating leases do not require lease liability recognition',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'How should research and development costs be treated under U.S. GAAP?',
                'difficulty' => 'easy',
                'explanation' => 'R&D costs are expensed as incurred under U.S. GAAP, with limited exceptions.',
                'options' => [
                    'Capitalized and amortized over the useful life',
                    'Expensed as incurred',
                    'Capitalized until technological feasibility is established',
                    'Recorded as intangible assets',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'A company issues convertible bonds. Under U.S. GAAP, how are these bonds initially recorded?',
                'difficulty' => 'hard',
                'explanation' => 'U.S. GAAP does not separate the debt and equity components of convertible debt; it records entirely as a liability.',
                'options' => [
                    'Entirely as a liability at face value',
                    'Separated into debt and equity components',
                    'Entirely as equity',
                    'At fair value with changes through earnings',
                ],
                'correct' => 0,
            ],
            [
                'stem' => 'Which of the following would be classified as an investing activity in the statement of cash flows?',
                'difficulty' => 'easy',
                'explanation' => 'Purchase of equipment is an investing activity as it involves long-term assets.',
                'options' => [
                    'Payment of dividends',
                    'Purchase of equipment',
                    'Issuance of common stock',
                    'Payment of interest on debt',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'What is the correct treatment for deferred tax assets?',
                'difficulty' => 'medium',
                'explanation' => 'Deferred tax assets are recognized but must be evaluated for a valuation allowance based on likelihood of realization.',
                'options' => [
                    'Always recognized in full',
                    'Never recognized',
                    'Recognized but evaluated for need of a valuation allowance',
                    'Recorded as contra-liabilities',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'Under ASC 450, a loss contingency should be accrued when:',
                'difficulty' => 'medium',
                'explanation' => 'Loss contingencies are accrued when probable and reasonably estimable.',
                'options' => [
                    'It is reasonably possible',
                    'It is remote',
                    'It is probable and the amount can be reasonably estimated',
                    'Management decides it is material',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'How are gains and losses on available-for-sale debt securities reported?',
                'difficulty' => 'medium',
                'explanation' => 'Unrealized gains/losses on AFS securities are reported in OCI until realized.',
                'options' => [
                    'In net income',
                    'In other comprehensive income',
                    'As an adjustment to retained earnings',
                    'Not reported until sold',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which statement is true regarding earnings per share (EPS)?',
                'difficulty' => 'easy',
                'explanation' => 'Public companies must present both basic and diluted EPS on the income statement.',
                'options' => [
                    'Only basic EPS is required for public companies',
                    'Both basic and diluted EPS must be presented by public companies',
                    'EPS is optional for all companies',
                    'Only diluted EPS is required',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'What is the accounting treatment for stock-based compensation under ASC 718?',
                'difficulty' => 'medium',
                'explanation' => 'Stock-based compensation is measured at grant-date fair value and recognized as expense over the service period.',
                'options' => [
                    'Measured at intrinsic value on exercise date',
                    'Measured at fair value on grant date and expensed over the vesting period',
                    'Not recognized as expense if equity-settled',
                    'Expensed only when options are exercised',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which of the following errors would cause assets to be overstated?',
                'difficulty' => 'medium',
                'explanation' => 'Failing to record depreciation causes assets to be overstated.',
                'options' => [
                    'Failure to record depreciation expense',
                    'Recording accounts payable twice',
                    'Understating revenue',
                    'Overstating expenses',
                ],
                'correct' => 0,
            ],
            [
                'stem' => 'How should a bargain purchase in a business combination be treated under ASC 805?',
                'difficulty' => 'hard',
                'explanation' => 'A bargain purchase results in a gain recognized immediately in earnings.',
                'options' => [
                    'Recorded as goodwill',
                    'Recorded as a gain in earnings',
                    'Allocated to reduce non-current assets',
                    'Recorded as a deferred credit',
                ],
                'correct' => 1,
            ],
        ];
    }

    protected function getAFARQuestions(): array
    {
        return [
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
            [
                'stem' => 'Under the temporal method of foreign currency translation, which account is translated at the current exchange rate?',
                'difficulty' => 'medium',
                'explanation' => 'Monetary items like cash are translated at the current rate under the temporal method.',
                'options' => [
                    'Inventory carried at cost',
                    'Fixed assets',
                    'Cash',
                    'Common stock',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'In push-down accounting, when is the acquirer\'s basis "pushed down" to the subsidiary\'s financial statements?',
                'difficulty' => 'hard',
                'explanation' => 'Push-down accounting is typically applied when control is obtained, especially in SEC registrant subsidiaries.',
                'options' => [
                    'Never; subsidiaries maintain historical cost',
                    'When control is obtained and certain criteria are met',
                    'Only for 100% acquisitions',
                    'Only if the subsidiary elects push-down',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'What is the accounting treatment for intercompany profits in inventory in consolidated financial statements?',
                'difficulty' => 'medium',
                'explanation' => 'Intercompany profits in inventory are eliminated until realized through sale to third parties.',
                'options' => [
                    'Recognized immediately in consolidated income',
                    'Eliminated until realized by sale to external parties',
                    'Recorded as deferred revenue',
                    'Never eliminated',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'How are cumulative translation adjustments reported in the financial statements?',
                'difficulty' => 'easy',
                'explanation' => 'Translation adjustments are reported in accumulated other comprehensive income (AOCI).',
                'options' => [
                    'In net income',
                    'As a direct adjustment to retained earnings',
                    'In accumulated other comprehensive income',
                    'As a separate line item in liabilities',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'Which condition indicates that a variable interest entity (VIE) should be consolidated by the reporting entity?',
                'difficulty' => 'hard',
                'explanation' => 'The entity with the power to direct significant activities and obligation/right to significant benefits is the primary beneficiary.',
                'options' => [
                    'The reporting entity owns 50% or more voting interest',
                    'The reporting entity is the primary beneficiary',
                    'The VIE has positive equity',
                    'The reporting entity has no involvement',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'When computing goodwill in a business combination with a noncontrolling interest, which method measures NCI at fair value?',
                'difficulty' => 'medium',
                'explanation' => 'The full goodwill method measures NCI at fair value, attributing goodwill to both parent and NCI.',
                'options' => [
                    'Partial goodwill method',
                    'Full goodwill method',
                    'Pooling method',
                    'Equity method',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which of the following is NOT a quantitative threshold for reportable segments under ASC 280?',
                'difficulty' => 'medium',
                'explanation' => 'The 10% tests include revenue, profit/loss, and assets. Number of employees is not a threshold.',
                'options' => [
                    'Revenue is 10% or more of combined revenue',
                    'Absolute profit or loss is 10% or more',
                    'Assets are 10% or more of combined assets',
                    'Number of employees is 10% or more',
                ],
                'correct' => 3,
            ],
            [
                'stem' => 'How should a hedging instrument be accounted for if it qualifies as a fair value hedge?',
                'difficulty' => 'hard',
                'explanation' => 'Both the hedging instrument and hedged item are marked to fair value with changes in earnings, offsetting each other.',
                'options' => [
                    'Changes in fair value recorded in OCI',
                    'Changes in fair value recorded in earnings',
                    'No changes recorded until settlement',
                    'Changes in fair value adjust the hedged asset carrying amount only',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'In an upstream sale of inventory between parent and subsidiary, where does the intercompany profit elimination impact?',
                'difficulty' => 'medium',
                'explanation' => 'Upstream sales affect both parent and NCI proportionately in elimination.',
                'options' => [
                    'Only the parent\'s share of income',
                    'Only the subsidiary\'s income',
                    'Both the parent and NCI proportionately',
                    'Neither; upstream sales are not eliminated',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'Which pension component represents the increase in pension obligation due to employee service during the current period?',
                'difficulty' => 'easy',
                'explanation' => 'Service cost represents the present value of benefits earned by employees during the period.',
                'options' => [
                    'Interest cost',
                    'Service cost',
                    'Expected return on plan assets',
                    'Amortization of prior service cost',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'What is the primary difference between IFRS and U.S. GAAP in accounting for development costs?',
                'difficulty' => 'medium',
                'explanation' => 'IFRS allows capitalization of development costs when certain criteria are met; U.S. GAAP generally expenses them.',
                'options' => [
                    'IFRS expenses all development costs; U.S. GAAP capitalizes',
                    'IFRS capitalizes when criteria met; U.S. GAAP expenses',
                    'Both capitalize development costs',
                    'Both expense development costs',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'When a parent sells a portion of its ownership in a subsidiary but retains control, what is the accounting treatment?',
                'difficulty' => 'hard',
                'explanation' => 'When control is retained, the transaction is treated as an equity transaction with no gain or loss in earnings.',
                'options' => [
                    'A gain or loss is recognized in earnings',
                    'The transaction is treated as an equity transaction',
                    'The subsidiary is deconsolidated',
                    'No accounting entry is required',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which rate is used to remeasure a foreign subsidiary\'s depreciation expense when the functional currency is the parent\'s currency?',
                'difficulty' => 'hard',
                'explanation' => 'Under the remeasurement method, depreciation uses historical rates from when the asset was acquired.',
                'options' => [
                    'Current rate at balance sheet date',
                    'Average rate for the period',
                    'Historical rate when asset was acquired',
                    'Forward rate',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'How should a bargain purchase be recorded in consolidated financial statements?',
                'difficulty' => 'medium',
                'explanation' => 'A bargain purchase results in a gain recognized in the consolidated income statement.',
                'options' => [
                    'As negative goodwill on the balance sheet',
                    'As a gain in consolidated income',
                    'Allocated to reduce acquired assets',
                    'As additional paid-in capital',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Under ASC 842, how should a lessee classify a lease if the present value of lease payments equals 90% of the asset\'s fair value?',
                'difficulty' => 'medium',
                'explanation' => 'If PV of payments is substantially all (≥90%) of fair value, it\'s classified as a finance lease.',
                'options' => [
                    'Operating lease',
                    'Finance lease',
                    'Short-term lease',
                    'Executory contract',
                ],
                'correct' => 1,
            ],
        ];
    }

    protected function getMASQuestions(): array
    {
        return [
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
            [
                'stem' => 'Which variance represents the difference between actual and standard quantity of materials used?',
                'difficulty' => 'easy',
                'explanation' => 'Material quantity (or usage) variance measures efficiency in material consumption.',
                'options' => [
                    'Material price variance',
                    'Material quantity variance',
                    'Labor rate variance',
                    'Variable overhead efficiency variance',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'In a make-or-buy decision, which costs should be considered?',
                'difficulty' => 'medium',
                'explanation' => 'Only incremental costs and opportunity costs are relevant; sunk and allocated costs are not.',
                'options' => [
                    'All historical costs',
                    'Only fixed costs',
                    'Incremental costs and opportunity costs',
                    'Sunk costs',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'What is the primary purpose of a flexible budget?',
                'difficulty' => 'easy',
                'explanation' => 'A flexible budget adjusts for actual activity levels, enabling meaningful variance analysis.',
                'options' => [
                    'To eliminate all variances',
                    'To compare actual results at actual activity level to budgeted costs at that same level',
                    'To prepare financial statements',
                    'To allocate costs to products',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which costing method assigns only variable manufacturing costs to products?',
                'difficulty' => 'easy',
                'explanation' => 'Variable costing (direct costing) assigns only variable manufacturing costs to inventory.',
                'options' => [
                    'Absorption costing',
                    'Variable costing',
                    'Process costing',
                    'Job order costing',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Under what condition is the contribution margin equal to operating income?',
                'difficulty' => 'medium',
                'explanation' => 'When fixed costs are zero, contribution margin equals operating income.',
                'options' => [
                    'When variable costs equal fixed costs',
                    'When fixed costs are zero',
                    'At the break-even point',
                    'When sales equal variable costs',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which performance measure is calculated as net income divided by average total assets?',
                'difficulty' => 'easy',
                'explanation' => 'Return on assets (ROA) measures profitability relative to total assets.',
                'options' => [
                    'Return on equity',
                    'Return on assets',
                    'Profit margin',
                    'Asset turnover',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'What is the main disadvantage of using return on investment (ROI) as a performance measure?',
                'difficulty' => 'medium',
                'explanation' => 'ROI can discourage managers from investing in projects that lower divisional ROI but exceed the company\'s cost of capital.',
                'options' => [
                    'It ignores the time value of money',
                    'It may lead to suboptimal decisions when divisional ROI exceeds company cost of capital',
                    'It is too difficult to calculate',
                    'It cannot be compared across divisions',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which method of allocating joint costs assigns costs based on the relative sales value at the split-off point?',
                'difficulty' => 'medium',
                'explanation' => 'The sales value at split-off method allocates joint costs proportionally to relative sales values.',
                'options' => [
                    'Physical units method',
                    'Net realizable value method',
                    'Sales value at split-off method',
                    'Constant gross margin method',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'Which of the following would increase the break-even point?',
                'difficulty' => 'medium',
                'explanation' => 'An increase in fixed costs raises the break-even point, requiring more sales to cover costs.',
                'options' => [
                    'Decrease in fixed costs',
                    'Increase in sales price',
                    'Decrease in variable cost per unit',
                    'Increase in fixed costs',
                ],
                'correct' => 3,
            ],
            [
                'stem' => 'Under throughput costing, which costs are considered product costs?',
                'difficulty' => 'hard',
                'explanation' => 'Throughput costing treats only direct materials as product costs; all other costs are period costs.',
                'options' => [
                    'Direct materials, direct labor, and overhead',
                    'Only direct materials',
                    'Only variable manufacturing costs',
                    'All manufacturing costs',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'What does a favorable labor efficiency variance indicate?',
                'difficulty' => 'easy',
                'explanation' => 'A favorable efficiency variance means fewer hours were used than standard allowed.',
                'options' => [
                    'Actual hours exceeded standard hours',
                    'Actual hours were less than standard hours allowed',
                    'Actual wage rate was less than standard rate',
                    'Actual wage rate exceeded standard rate',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which budgeting approach starts from a zero base and requires justification for all expenditures?',
                'difficulty' => 'easy',
                'explanation' => 'Zero-based budgeting requires each expense to be justified from scratch each period.',
                'options' => [
                    'Incremental budgeting',
                    'Flexible budgeting',
                    'Zero-based budgeting',
                    'Activity-based budgeting',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'In target costing, what is the formula for target cost?',
                'difficulty' => 'medium',
                'explanation' => 'Target cost is calculated by subtracting desired profit from the expected market price.',
                'options' => [
                    'Target cost = Actual cost - Desired profit',
                    'Target cost = Market price - Desired profit',
                    'Target cost = Market price + Desired profit',
                    'Target cost = Actual cost + Desired profit',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which term describes the amount by which sales can decline before a loss is incurred?',
                'difficulty' => 'easy',
                'explanation' => 'Margin of safety measures the cushion between actual sales and break-even sales.',
                'options' => [
                    'Operating leverage',
                    'Contribution margin ratio',
                    'Margin of safety',
                    'Break-even point',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'When using process costing, how are equivalent units calculated under the weighted-average method?',
                'difficulty' => 'medium',
                'explanation' => 'Weighted-average method includes all units completed plus equivalent units in ending work in process.',
                'options' => [
                    'Units completed only',
                    'Units started during the period',
                    'Units completed plus equivalent units in ending WIP',
                    'Beginning WIP plus units started',
                ],
                'correct' => 2,
            ],
        ];
    }

    protected function getAuditQuestions(): array
    {
        return [
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
            [
                'stem' => 'Which of the following is an example of a substantive analytical procedure?',
                'difficulty' => 'easy',
                'explanation' => 'Comparing current year gross margin to prior year identifies unexpected fluctuations.',
                'options' => [
                    'Testing the operating effectiveness of controls',
                    'Comparing current year gross margin percentage to prior year',
                    'Observing the client\'s inventory count',
                    'Confirming accounts receivable',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'An auditor notes a scope limitation that is material but not pervasive. Which opinion is appropriate?',
                'difficulty' => 'medium',
                'explanation' => 'A material scope limitation results in a qualified opinion or disclaimer; if not pervasive, qualified is appropriate.',
                'options' => [
                    'Unmodified opinion',
                    'Qualified opinion',
                    'Adverse opinion',
                    'Disclaimer of opinion',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which type of audit report paragraph includes a discussion of key audit matters in a public company audit?',
                'difficulty' => 'medium',
                'explanation' => 'Critical audit matters (CAMs) are communicated in a separate section for public company audits under PCAOB standards.',
                'options' => [
                    'Basis for opinion',
                    'Opinion paragraph',
                    'Critical audit matters',
                    'Emphasis-of-matter',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'In assessing control risk, which activity provides evidence of operating effectiveness?',
                'difficulty' => 'easy',
                'explanation' => 'Tests of controls, such as reperformance, provide evidence that controls operated effectively.',
                'options' => [
                    'Inquiry of management only',
                    'Walk-through procedures',
                    'Reperformance of control activities',
                    'Review of control design documentation',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'Which assertion is primarily addressed by confirming accounts payable balances?',
                'difficulty' => 'medium',
                'explanation' => 'Confirmations primarily provide evidence about completeness for liabilities (ensuring all are recorded).',
                'options' => [
                    'Existence',
                    'Completeness',
                    'Valuation',
                    'Presentation',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'What is the auditor\'s responsibility regarding fraud in a financial statement audit?',
                'difficulty' => 'medium',
                'explanation' => 'The auditor must obtain reasonable assurance that material misstatements due to fraud are detected.',
                'options' => [
                    'Guarantee that no fraud exists',
                    'Obtain reasonable assurance that material fraud is detected',
                    'Detect all fraud, regardless of materiality',
                    'Report any suspicion of fraud to law enforcement',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which of the following is a test of controls?',
                'difficulty' => 'easy',
                'explanation' => 'Inspecting evidence of management review and approval tests the operating effectiveness of a control.',
                'options' => [
                    'Recalculating depreciation expense',
                    'Confirming receivables',
                    'Inspecting documents for evidence of management review and approval',
                    'Performing analytical procedures',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'An auditor discovers material noncompliance with laws and regulations. What is the auditor\'s responsibility?',
                'difficulty' => 'hard',
                'explanation' => 'The auditor must communicate to those charged with governance and evaluate the impact on the audit and opinion.',
                'options' => [
                    'Immediately report to regulators',
                    'Communicate to those charged with governance and evaluate impact on the audit',
                    'Resign from the engagement immediately',
                    'Ignore if it does not affect the financial statements',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which procedure is most effective in detecting lapping of cash receipts?',
                'difficulty' => 'medium',
                'explanation' => 'Confirming receivables can reveal timing differences that indicate lapping.',
                'options' => [
                    'Observing cash count',
                    'Confirming accounts receivable',
                    'Reviewing bank reconciliations',
                    'Testing internal controls over cash',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'When an auditor issues an adverse opinion, which paragraph is modified?',
                'difficulty' => 'medium',
                'explanation' => 'An adverse opinion requires a basis for adverse opinion paragraph explaining the GAAP departure and a modified opinion paragraph.',
                'options' => [
                    'Introductory paragraph only',
                    'Basis for opinion and opinion paragraphs',
                    'Only the opinion paragraph',
                    'Auditor responsibility paragraph',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which factor most likely indicates a high risk of material misstatement due to fraud?',
                'difficulty' => 'medium',
                'explanation' => 'Management incentives tied to aggressive financial targets increase fraud risk.',
                'options' => [
                    'High employee turnover',
                    'Management compensation tied to aggressive financial targets',
                    'Complex organizational structure',
                    'Recently implemented IT system',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'An auditor notes that recorded sales are valid but may be materially overstated. Which assertion is at risk?',
                'difficulty' => 'easy',
                'explanation' => 'Accuracy (valuation) relates to whether amounts are recorded at correct amounts.',
                'options' => [
                    'Existence',
                    'Completeness',
                    'Accuracy',
                    'Occurrence',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'Which substantive procedure provides evidence about the valuation of inventory?',
                'difficulty' => 'easy',
                'explanation' => 'Testing inventory for net realizable value provides evidence about valuation.',
                'options' => [
                    'Observing the physical inventory count',
                    'Testing net realizable value',
                    'Confirming inventory held by third parties',
                    'Tracing inventory purchases to receiving reports',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'What is the purpose of an engagement quality review (EQR)?',
                'difficulty' => 'medium',
                'explanation' => 'An EQR provides an objective evaluation of significant judgments and conclusions before the report is issued.',
                'options' => [
                    'To replace the audit team\'s work',
                    'To evaluate significant judgments and conclusions before report issuance',
                    'To ensure compliance with firm policies only',
                    'To test internal controls',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which of the following would cause an auditor to assess control risk at the maximum?',
                'difficulty' => 'medium',
                'explanation' => 'If controls are not designed effectively or tests of controls are not performed, control risk is assessed at maximum.',
                'options' => [
                    'Controls are well-designed and tests indicate effective operation',
                    'The auditor chooses not to test controls',
                    'Inherent risk is low',
                    'Substantive procedures are extensive',
                ],
                'correct' => 1,
            ],
        ];
    }

    protected function getRFBTQuestions(): array
    {
        return [
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
                    'A merchant\'s signed written assurance to keep the offer open',
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
            [
                'stem' => 'Under the UCC, when does risk of loss pass to the buyer in a shipment contract?',
                'difficulty' => 'medium',
                'explanation' => 'In a shipment contract, risk passes when the seller delivers goods to the carrier.',
                'options' => [
                    'When the buyer receives the goods',
                    'When the seller delivers goods to the carrier',
                    'When payment is made',
                    'When the contract is signed',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which agency relationship duty requires the agent to act solely for the benefit of the principal?',
                'difficulty' => 'easy',
                'explanation' => 'The duty of loyalty requires the agent to act in the principal\'s best interest.',
                'options' => [
                    'Duty of care',
                    'Duty of loyalty',
                    'Duty of obedience',
                    'Duty to account',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'What is the primary advantage of forming a C corporation?',
                'difficulty' => 'easy',
                'explanation' => 'C corporations provide limited liability to shareholders, protecting personal assets.',
                'options' => [
                    'Pass-through taxation',
                    'Unlimited liability',
                    'Limited liability for shareholders',
                    'Simplified formation process',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'Under securities law, which type of offering is exempt from SEC registration?',
                'difficulty' => 'medium',
                'explanation' => 'Private placements under Regulation D are exempt from SEC registration requirements.',
                'options' => [
                    'Public offerings over $5 million',
                    'Initial public offerings (IPOs)',
                    'Private placements under Regulation D',
                    'All secondary market transactions',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'Which tort occurs when one party makes a false statement that injures another party\'s reputation?',
                'difficulty' => 'easy',
                'explanation' => 'Defamation involves making false statements that harm another\'s reputation.',
                'options' => [
                    'Fraud',
                    'Negligence',
                    'Defamation',
                    'Breach of contract',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'What is the "mailbox rule" in contract formation?',
                'difficulty' => 'medium',
                'explanation' => 'Under the mailbox rule, acceptance is effective when dispatched, not when received.',
                'options' => [
                    'Acceptance is effective when received',
                    'Acceptance is effective when dispatched',
                    'Offers can be revoked after acceptance',
                    'Acceptance must be in writing',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which element is NOT required to establish fraud?',
                'difficulty' => 'medium',
                'explanation' => 'Fraud requires: false representation, scienter, intent to induce reliance, justifiable reliance, and damages. Privity is not required.',
                'options' => [
                    'False representation of material fact',
                    'Scienter (knowledge of falsity)',
                    'Privity of contract',
                    'Justifiable reliance',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'In what type of contract does the statute of frauds require a writing?',
                'difficulty' => 'medium',
                'explanation' => 'The statute of frauds requires contracts for the sale of land to be in writing.',
                'options' => [
                    'Sale of goods under $500',
                    'Service contracts under one year',
                    'Contracts for the sale of land',
                    'Oral promises to pay debts',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'What is the consequence of a material breach of contract?',
                'difficulty' => 'easy',
                'explanation' => 'A material breach excuses the non-breaching party from further performance.',
                'options' => [
                    'The contract is automatically void',
                    'The non-breaching party is excused from further performance',
                    'Specific performance is always available',
                    'No remedies are available',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which of the following is a secured transaction under UCC Article 9?',
                'difficulty' => 'medium',
                'explanation' => 'A security interest in inventory is a secured transaction governed by UCC Article 9.',
                'options' => [
                    'A mortgage on real property',
                    'An unsecured loan',
                    'A security interest in inventory',
                    'A guaranty agreement',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'What is the effect of a novation in contract law?',
                'difficulty' => 'hard',
                'explanation' => 'A novation substitutes a new party and releases the original party from obligations.',
                'options' => [
                    'The original contract is modified',
                    'A new party is substituted and the original party is released',
                    'The contract is voided',
                    'Performance is temporarily suspended',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which federal law protects employees from discrimination based on race, color, religion, sex, or national origin?',
                'difficulty' => 'easy',
                'explanation' => 'Title VII of the Civil Rights Act of 1964 prohibits employment discrimination.',
                'options' => [
                    'Americans with Disabilities Act',
                    'Fair Labor Standards Act',
                    'Title VII of the Civil Rights Act of 1964',
                    'Age Discrimination in Employment Act',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'Under what doctrine can a principal be held liable for an agent\'s torts committed within the scope of employment?',
                'difficulty' => 'medium',
                'explanation' => 'Respondeat superior holds principals vicariously liable for agents\' torts within scope of employment.',
                'options' => [
                    'Apparent authority',
                    'Respondeat superior',
                    'Indemnification',
                    'Ratification',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'What is the purpose of a non-compete agreement?',
                'difficulty' => 'easy',
                'explanation' => 'Non-compete agreements prevent employees from competing with their employer for a specified period and area.',
                'options' => [
                    'To prevent disclosure of trade secrets only',
                    'To restrict an employee from competing after employment ends',
                    'To eliminate all post-employment obligations',
                    'To guarantee lifetime employment',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which type of property ownership gives each owner an undivided interest with right of survivorship?',
                'difficulty' => 'medium',
                'explanation' => 'Joint tenancy includes right of survivorship, meaning a deceased owner\'s share passes to surviving owners.',
                'options' => [
                    'Tenancy in common',
                    'Joint tenancy',
                    'Tenancy by the entirety',
                    'Community property',
                ],
                'correct' => 1,
            ],
        ];
    }

    protected function getTaxQuestions(): array
    {
        return [
            [
                'stem' => 'Which filing status generally provides the lowest tax rate for a qualifying widow or widower with a dependent child?',
                'difficulty' => 'easy',
                'explanation' => 'Qualifying widow(er) with dependent child retains the joint return rates for the two years following the spouse\'s death.',
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
                'stem' => 'Which corporate tax credit offsets a corporation\'s regular tax liability but not its alternative minimum tax?',
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
            [
                'stem' => 'What is the holding period requirement for long-term capital gain treatment?',
                'difficulty' => 'easy',
                'explanation' => 'Assets must be held for more than one year to qualify for long-term capital gain treatment.',
                'options' => [
                    'More than 6 months',
                    'More than 1 year',
                    'More than 18 months',
                    'More than 2 years',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which of the following is NOT deductible as a business expense?',
                'difficulty' => 'medium',
                'explanation' => 'Political contributions are not deductible under IRC Section 162.',
                'options' => [
                    'Reasonable compensation to employees',
                    'Business travel expenses',
                    'Political contributions',
                    'Advertising costs',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'What is the maximum Section 179 expense deduction a taxpayer can elect in 2024 (assuming no phase-out)?',
                'difficulty' => 'medium',
                'explanation' => 'The Section 179 deduction limit is adjusted annually for inflation and is $1,220,000 for 2024.',
                'options' => [
                    '$500,000',
                    '$1,000,000',
                    '$1,220,000',
                    'Unlimited',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'Which of the following entities is NOT subject to the net investment income tax (NIIT)?',
                'difficulty' => 'hard',
                'explanation' => 'C corporations are not subject to NIIT; it applies to individuals, estates, and trusts.',
                'options' => [
                    'Individuals',
                    'Trusts',
                    'C corporations',
                    'Estates',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'How is qualified business income (QBI) from a pass-through entity generally taxed?',
                'difficulty' => 'medium',
                'explanation' => 'QBI deduction allows up to 20% deduction from qualified business income, reducing effective tax rate.',
                'options' => [
                    'At capital gains rates',
                    'Subject to up to 20% deduction under Section 199A',
                    'Fully exempt from tax',
                    'Taxed at corporate rates',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which method of accounting must be used by C corporations with average annual gross receipts exceeding $27 million?',
                'difficulty' => 'medium',
                'explanation' => 'Large C corporations must use the accrual method of accounting.',
                'options' => [
                    'Cash method',
                    'Accrual method',
                    'Hybrid method',
                    'Installment method',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'What is the tax treatment of municipal bond interest?',
                'difficulty' => 'easy',
                'explanation' => 'Interest from municipal bonds is generally exempt from federal income tax.',
                'options' => [
                    'Fully taxable as ordinary income',
                    'Taxed as capital gain',
                    'Exempt from federal income tax',
                    'Subject to alternative minimum tax only',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'Which of the following is true regarding like-kind exchanges under Section 1031?',
                'difficulty' => 'hard',
                'explanation' => 'Under the TCJA, like-kind exchanges are limited to real property; personal property no longer qualifies.',
                'options' => [
                    'They apply to all types of property',
                    'They are limited to real property',
                    'They result in immediate recognition of gain',
                    'They are no longer allowed',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'What is the kiddie tax?',
                'difficulty' => 'medium',
                'explanation' => 'The kiddie tax taxes certain unearned income of children at the parent\'s tax rate.',
                'options' => [
                    'A tax on children\'s earned income',
                    'A tax on unearned income of children at the parent\'s rate',
                    'A tax credit for families with children',
                    'A penalty for early withdrawal from retirement accounts',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which retirement plan allows for both employer and employee contributions and permits loans?',
                'difficulty' => 'easy',
                'explanation' => 'A 401(k) plan permits both contributions and loans to participants.',
                'options' => [
                    'Traditional IRA',
                    'Roth IRA',
                    '401(k) plan',
                    'SEP IRA',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'What is the maximum annual contribution limit for a traditional IRA in 2024 (excluding catch-up contributions)?',
                'difficulty' => 'medium',
                'explanation' => 'The 2024 IRA contribution limit is $7,000 (or $8,000 if age 50 or older).',
                'options' => [
                    '$5,500',
                    '$6,000',
                    '$6,500',
                    '$7,000',
                ],
                'correct' => 3,
            ],
            [
                'stem' => 'Which of the following is subject to self-employment tax?',
                'difficulty' => 'easy',
                'explanation' => 'Net earnings from self-employment are subject to self-employment tax.',
                'options' => [
                    'Wages from an employer',
                    'Dividend income',
                    'Net earnings from self-employment',
                    'Interest income',
                ],
                'correct' => 2,
            ],
            [
                'stem' => 'What is the corporate tax rate under the Tax Cuts and Jobs Act?',
                'difficulty' => 'easy',
                'explanation' => 'The TCJA set a flat corporate tax rate of 21%.',
                'options' => [
                    '15%',
                    '21%',
                    '28%',
                    '35%',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'Which exclusion allows taxpayers to exclude gain from the sale of a principal residence?',
                'difficulty' => 'medium',
                'explanation' => 'Section 121 allows exclusion of up to $250,000 ($500,000 for married filing jointly) of gain on sale of principal residence.',
                'options' => [
                    'Section 1031',
                    'Section 121',
                    'Section 1244',
                    'Section 179',
                ],
                'correct' => 1,
            ],
            [
                'stem' => 'How are distributions from a Roth IRA taxed if all requirements are met?',
                'difficulty' => 'easy',
                'explanation' => 'Qualified distributions from Roth IRAs are tax-free if the account has been open 5 years and other criteria are met.',
                'options' => [
                    'Fully taxable as ordinary income',
                    'Taxed as capital gain',
                    'Tax-free',
                    'Subject to 10% penalty only',
                ],
                'correct' => 2,
            ],
        ];
    }
}
