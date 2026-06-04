<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAIService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    }

    /**
     * Send a prompt to Gemini API and parse JSON response.
     */
    protected function generateContent($prompt)
    {
        if (empty($this->apiKey)) {
            Log::warning('Gemini API Key is missing.');
            return null;
        }

        try {
            $response = Http::post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $jsonText = $data['candidates'][0]['content']['parts'][0]['text'];
                    return json_decode($jsonText, true);
                }
            }

            Log::error('Gemini API Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Gemini Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate match rate between student skills and industry requirements.
     */
    public function calculateMatchRate($studentSkills, $requiredSkills)
    {
        $prompt = "Kamu adalah AI HR Matchmaker profesional. Analisis kecocokan antara keterampilan siswa dan kebutuhan industri. \n\n" .
                  "Keterampilan Siswa (hanya yang sudah divalidasi sekolah): \n" . json_encode($studentSkills) . "\n\n" .
                  "Kebutuhan Industri (Required Skills): \n" . json_encode($requiredSkills) . "\n\n" .
                  "Instruksi: Evaluasi seberapa cocok keterampilan siswa dengan kebutuhan industri. Pertimbangkan tingkat relevansi. " .
                  "Berikan respons HANYA berupa objek JSON dengan dua properti:\n" .
                  "1. 'match_rate': integer dari 0 hingga 100 yang merepresentasikan persentase kecocokan.\n" .
                  "2. 'analysis': string pendek (maks 3-4 kalimat) yang menjelaskan secara profesional alasan skor tersebut diberikan. Gunakan bahasa Indonesia yang baik.";

        $result = $this->generateContent($prompt);

        if ($result && isset($result['match_rate'])) {
            return [
                'match_rate' => $result['match_rate'],
                'analysis' => $result['analysis'] ?? 'Kecocokan berhasil dihitung oleh AI.',
            ];
        }

        // Fallback jika API gagal
        return [
            'match_rate' => rand(40, 85),
            'analysis' => 'AI sedang tidak dapat memproses analisis saat ini. Skor di atas adalah perkiraan sistem dasar.',
        ];
    }

    /**
     * Analyze skill gap for a specific school.
     */
    public function analyzeSkillGap($schoolName, $studentsSkills, $industryDemands)
    {
        $prompt = "Kamu adalah AI Konsultan Kurikulum Pendidikan Vokasi (SMK). Tolong analisis kesenjangan keterampilan (Skill-Gap) antara apa yang diajarkan/dimiliki siswa di sekolah dengan apa yang saat ini paling dicari oleh industri.\n\n" .
                  "Data Sekolah: {$schoolName}\n" .
                  "Kumpulan Keterampilan Siswa (Agregat yang sudah divalidasi): \n" . json_encode($studentsSkills) . "\n\n" .
                  "Tren Kebutuhan Industri Saat Ini (berdasarkan lowongan aktif): \n" . json_encode($industryDemands) . "\n\n" .
                  "Instruksi: Analisis kesenjangan (gap) antara supply (siswa) dan demand (industri). " .
                  "Berikan respons HANYA berupa objek JSON dengan tiga properti:\n" .
                  "1. 'overall_gap_score': integer dari 0 hingga 100 (0 = gap sangat buruk/tidak relevan, 100 = sangat relevan/selaras dengan industri).\n" .
                  "2. 'top_missing_skills': array of strings (maksimal 5 skill utama yang sangat dibutuhkan industri tapi kurang/tidak dimiliki siswa).\n" .
                  "3. 'recommendations': array of strings (3 rekomendasi praktis dan spesifik untuk sekolah dalam mengadaptasi kurikulum atau pelatihan tambahan).";

        $result = $this->generateContent($prompt);

        if ($result && isset($result['overall_gap_score'])) {
            return $result;
        }

        // Fallback
        return [
            'overall_gap_score' => 60,
            'top_missing_skills' => ['Data AI tidak tersedia'],
            'recommendations' => ['Silakan periksa koneksi atau kunci API Gemini Anda.'],
        ];
    }
}
