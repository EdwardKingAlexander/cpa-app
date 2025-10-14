# Question Seeder Documentation

## Overview

The `QuestionSeeder` is a dedicated seeder for creating comprehensive CPA exam questions across all subjects. Each subject receives **20 high-quality questions** with varying difficulty levels.

## Location

`database/seeders/QuestionSeeder.php`

## Subjects Covered

- **FAR** - Financial Accounting and Reporting (20 questions)
- **AFAR** - Advanced Financial Accounting and Reporting (20 questions)
- **MAS** - Management Accounting and Services (20 questions)
- **AUDIT** - Auditing (20 questions)
- **RFBT** - Regulatory Framework for Business Transactions (20 questions)
- **TAX** - Taxation (20 questions)

**Total: 120 questions**

## Question Structure

Each question includes:
- **Stem**: The question text
- **Difficulty**: easy, medium, or hard
- **Explanation**: Detailed explanation of the correct answer
- **Options**: Four multiple-choice options (A, B, C, D)
- **Correct**: Index of the correct answer (0-3)

## Usage

### Run the Question Seeder Alone

```bash
php artisan db:seed --class=QuestionSeeder
```

This will:
- Create or update subjects if they don't exist
- Create or update all 120 questions
- Create all answer choices for each question
- Work safely with existing data (using `updateOrCreate`)

### Run with Database Seeder

If you want to run the complete database seed (users + questions):

```bash
php artisan migrate:fresh --seed
```

Or run seeders separately:

```bash
php artisan db:seed --class=DatabaseSeeder
php artisan db:seed --class=QuestionSeeder
```

### Integration with DatabaseSeeder

You can optionally add the QuestionSeeder to DatabaseSeeder's `run()` method:

```php
public function run(): void
{
    // ... existing user and subject seeding ...
    
    // Optionally call QuestionSeeder
    $this->call([
        QuestionSeeder::class,
    ]);
}
```

## Question Difficulty Distribution

Each subject has a balanced mix:
- **Easy**: ~30% (6 questions per subject)
- **Medium**: ~50% (10 questions per subject)
- **Hard**: ~20% (4 questions per subject)

## Data Integrity

The seeder uses `updateOrCreate` which means:
- ✅ Safe to run multiple times
- ✅ Won't create duplicates (matches on subject_id + stem)
- ✅ Will update existing questions if stem matches
- ✅ Choices are recreated each time to ensure consistency

## Example Questions

### FAR - Easy
```
Q: Which inventory costing method is prohibited under IFRS but allowed under U.S. GAAP?
A: LIFO
```

### AUDIT - Hard
```
Q: If management refuses to allow the auditor to communicate with predecessor auditors, what is the most appropriate response?
A: Decline the engagement
```

### TAX - Medium
```
Q: Which itemized deduction is subject to the 7.5% of AGI floor for individual taxpayers?
A: Unreimbursed medical expenses
```

## Benefits

1. **Comprehensive Coverage**: 20 questions per subject ensures students have plenty of practice material
2. **Realistic Exam Prep**: Questions mirror actual CPA exam format and difficulty
3. **Practice Mode Support**: Enough questions to support the new practice mode feature
4. **Testing Ready**: Provides sufficient data for testing the SRS algorithm

## Verification

After running the seeder, verify the data:

```bash
php artisan tinker
```

Then run:

```php
// Check total questions
Question::count(); // Should be 120

// Check questions per subject
Subject::withCount('questions')->get();

// Check a specific subject
Subject::where('code', 'TAX')->first()->questions()->count(); // Should be 20
```

## Testing the App

After seeding:

1. **Login** to the app
2. **Select TAX subject** (or any subject)
3. **Request 10 questions**
4. **Verify** questions load properly
5. **Answer and submit** to test SRS algorithm
6. **Use "Study more"** to test practice mode

## Maintenance

To add more questions:

1. Open `database/seeders/QuestionSeeder.php`
2. Add questions to the appropriate subject method (e.g., `getTaxQuestions()`)
3. Follow the existing format
4. Run the seeder: `php artisan db:seed --class=QuestionSeeder`

## Notes

- Questions are designed to test real CPA exam concepts
- Explanations help students understand why answers are correct
- Difficulty levels help the SRS algorithm schedule reviews appropriately
- All questions are multiple-choice with 4 options

## Production Considerations

For production deployment:
- Consider loading questions from a separate data source (CSV, API, etc.)
- Implement version control for questions
- Add metadata like tags, topics, and learning objectives
- Consider user-submitted questions and community review

---

**Created**: October 13, 2025
**Last Updated**: October 13, 2025
