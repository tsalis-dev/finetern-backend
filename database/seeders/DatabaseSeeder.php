<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\Portfolio;
use App\Models\Vacancy;
use App\Models\Application;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        // ==========================================
        // 1. Create Schools
        // ==========================================
        $schools = [
            ['name' => 'SMK Negeri 1 Bandung', 'email' => 'smkn1bdg@edu.com'],
            ['name' => 'SMK Telkom Malang', 'email' => 'smktelkom@edu.com'],
            ['name' => 'SMK Bina Informatika', 'email' => 'smkbi@edu.com'],
        ];

        $schoolIds = [];
        foreach ($schools as $s) {
            $user = User::create(['name' => $s['name'], 'email' => $s['email'], 'password' => $password, 'role' => 'school']);
            $user->profile()->create([
                'school' => $s['name'],
                'npsn' => rand(10000000, 99999999),
                'phone' => '022-' . rand(1111111, 9999999),
            ]);
            $schoolIds[] = $user->id;
        }

        // ==========================================
        // 2. Create Industries
        // ==========================================
        $industries = [
            ['name' => 'PT GoTo Gojek Tokopedia', 'email' => 'hr@goto.com'],
            ['name' => 'PT Ruangguru', 'email' => 'hr@ruangguru.com'],
            ['name' => 'Traveloka', 'email' => 'hr@traveloka.com'],
            ['name' => 'PT Dicoding Akademi', 'email' => 'hr@dicoding.com'],
            ['name' => 'Shopee Indonesia', 'email' => 'hr@shopee.co.id'],
        ];

        $industryIds = [];
        foreach ($industries as $i) {
            $user = User::create(['name' => $i['name'], 'email' => $i['email'], 'password' => $password, 'role' => 'industry']);
            $user->profile()->create([
                'company_name' => $i['name'],
                'nib' => rand(1000000000000, 9999999999999),
                'company_description' => 'Perusahaan teknologi terkemuka di Indonesia yang terus berinovasi.',
                'company_address' => 'Jakarta Selatan, Indonesia',
            ]);
            $industryIds[] = $user->id;
        }

        // ==========================================
        // 3. Create Vacancies
        // ==========================================
        $vacancyData = [
            ['title' => 'Frontend Web Developer Intern', 'req' => ['React', 'JavaScript', 'Tailwind', 'HTML', 'CSS']],
            ['title' => 'Backend Engineer Intern', 'req' => ['Laravel', 'PHP', 'MySQL', 'API']],
            ['title' => 'UI/UX Designer Intern', 'req' => ['Figma', 'Prototyping', 'User Research']],
            ['title' => 'Mobile App Developer Intern', 'req' => ['Flutter', 'Dart', 'Firebase']],
            ['title' => 'Data Analyst Intern', 'req' => ['Python', 'SQL', 'Data Visualization']],
            ['title' => 'IT Support Internship', 'req' => ['Troubleshooting', 'Networking', 'Windows']],
            ['title' => 'DevOps Engineer Intern', 'req' => ['Docker', 'Linux', 'AWS', 'Git']],
        ];

        $vacanciesList = [];
        foreach ($industryIds as $index => $indId) {
            // Each industry gets 2 random vacancies
            for ($j = 0; $j < 2; $j++) {
                $v = $vacancyData[array_rand($vacancyData)];
                $vacancy = Vacancy::create([
                    'user_id' => $indId,
                    'title' => $v['title'],
                    'description' => 'Dicari siswa magang/PKL yang bersemangat untuk belajar dan berkontribusi di lingkungan kerja profesional. ' . $v['title'],
                    'location' => 'Jakarta / Remote',
                    'work_mode' => rand(0, 1) ? 'WFH' : 'Hybrid',
                    'quota' => rand(1, 5),
                    'required_skills' => $v['req'],
                    'category' => 'Technology',
                    'status' => 'open',
                    'deadline' => now()->addDays(rand(10, 30)),
                ]);
                $vacanciesList[] = $vacancy;
            }
        }

        // ==========================================
        // 4. Create Students (30 Students)
        // ==========================================
        $firstNames = ['Budi', 'Siti', 'Andi', 'Dewi', 'Rudi', 'Rina', 'Ahmad', 'Fitri', 'Eko', 'Sri', 'Agus', 'Nina', 'Hadi', 'Maya', 'Doni'];
        $lastNames = ['Santoso', 'Wijaya', 'Kusuma', 'Pratama', 'Sari', 'Nugroho', 'Lestari', 'Hidayat', 'Putri', 'Saputra', 'Wahyuni'];
        $departments = ['Rekayasa Perangkat Lunak', 'Teknik Komputer dan Jaringan', 'Multimedia / DKV', 'Sistem Informasi'];
        $skillPool = ['React', 'Laravel', 'JavaScript', 'PHP', 'HTML', 'CSS', 'Figma', 'Python', 'SQL', 'Networking', 'Flutter'];

        $studentIds = [];
        for ($i = 1; $i <= 30; $i++) {
            $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
            $email = strtolower(str_replace(' ', '.', $name)) . $i . '@siswa.com';
            $schoolName = $schools[array_rand($schools)]['name'];
            $dept = $departments[array_rand($departments)];

            $user = User::create(['name' => $name, 'email' => $email, 'password' => $password, 'role' => 'student']);
            $user->profile()->create([
                'school' => $schoolName,
                'department' => $dept,
                'nisn' => '00' . rand(10000000, 99999999),
                'bio' => "Saya adalah siswa jurusan $dept dari $schoolName yang sangat antusias mengikuti program PKL untuk mengembangkan skill saya di dunia industri.",
                'gender' => rand(0, 1) ? 'Laki-laki' : 'Perempuan',
                'phone' => '08' . rand(1000000000, 9999999999),
            ]);
            $studentIds[] = $user->id;

            // Give them 3-5 random skills
            $numSkills = rand(3, 5);
            $mySkills = (array) array_rand(array_flip($skillPool), $numSkills);
            foreach ($mySkills as $sk) {
                Skill::create([
                    'user_id' => $user->id,
                    'name' => $sk,
                    'proficiency' => ['Basic', 'Intermediate', 'Advanced'][array_rand(['Basic', 'Intermediate', 'Advanced'])],
                    'is_validated' => rand(0, 1) ? true : false, // Some validated, some not
                ]);
            }

            // Give them 1 portfolio
            Portfolio::create([
                'user_id' => $user->id,
                'title' => 'Project Akhir ' . $sk,
                'description' => 'Ini adalah project yang saya kerjakan selama praktikum.',
                'url' => 'https://github.com/' . strtolower(str_replace(' ', '', $name)),
            ]);
        }

        // ==========================================
        // 5. Create Applications
        // ==========================================
        // Half of the students apply to 1 or 2 random vacancies
        $shuffledStudents = $studentIds;
        shuffle($shuffledStudents);
        $applicants = array_slice($shuffledStudents, 0, 20); // 20 students will apply

        foreach ($applicants as $studentId) {
            $numApps = rand(1, 2);
            for ($a = 0; $a < $numApps; $a++) {
                $vac = $vacanciesList[array_rand($vacanciesList)];
                
                // Avoid duplicate applications
                $exists = Application::where('user_id', $studentId)->where('vacancy_id', $vac->id)->exists();
                if (!$exists) {
                    $matchRate = rand(40, 95);
                    $aiMsg = $matchRate >= 80 
                        ? 'Siswa ini memiliki keterampilan yang sangat relevan dengan kebutuhan industri. Sangat direkomendasikan.' 
                        : 'Siswa ini memenuhi beberapa persyaratan dasar, namun masih membutuhkan pelatihan tambahan saat magang.';
                    
                    Application::create([
                        'vacancy_id' => $vac->id,
                        'user_id' => $studentId,
                        'match_rate' => $matchRate,
                        'ai_analysis' => $aiMsg,
                        'status' => ['Pending Review', 'Accepted', 'Rejected'][array_rand(['Pending Review', 'Accepted', 'Rejected'])],
                        'cover_letter' => 'Saya sangat tertarik dengan lowongan ini dan berharap dapat berkontribusi di perusahaan Bapak/Ibu.',
                    ]);
                }
            }
        }
    }
}
