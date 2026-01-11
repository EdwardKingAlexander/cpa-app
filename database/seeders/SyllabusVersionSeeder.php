<?php

namespace Database\Seeders;

use App\Models\SyllabusVersion;
use Database\Seeders\Concerns\LoadsSyllabusData;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class SyllabusVersionSeeder extends Seeder
{
    use LoadsSyllabusData;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = $this->loadSyllabusData();
        $version = $data['version'];
        $versionCode = $version['code'];
        $versionId = Uuid::uuid5(Uuid::NAMESPACE_URL, 'syllabus-version|'.$versionCode)->toString();

        $record = SyllabusVersion::query()->where('code', $versionCode)->first();
        $attributes = [
            'effective_date' => $version['effective_date'],
        ];

        if ($version['source'] !== null) {
            $attributes['source'] = $version['source'];
        }

        if ($record) {
            $record->fill($attributes)->save();

            return;
        }

        SyllabusVersion::create($attributes + [
            'id' => $versionId,
            'code' => $versionCode,
        ]);
    }
}
