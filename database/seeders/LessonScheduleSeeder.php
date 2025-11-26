<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LessonSchedule;
use App\Models\LessonHour;
use App\Models\Subject;
use App\Models\Employee;
use App\Models\Classroom;
use App\Enums\DayEnum;
use Illuminate\Support\Str;

class LessonScheduleSeeder extends Seeder
{
    private $lessonBlocks = [1, 2, 3]; // Block jam: 1, 2, atau 3 jam berurutan
    
    public function run(): void
    {
        $classrooms = Classroom::all();
        $subjects = Subject::all();
        $employees = Employee::all();

        if ($classrooms->isEmpty() || $subjects->isEmpty() || $employees->isEmpty()) {
            $this->command->error('Required data missing!');
            return;
        }

        $this->command->info("Found {$classrooms->count()} classrooms, {$subjects->count()} subjects, {$employees->count()} employees");

        $days = [
            DayEnum::MONDAY->value,
            DayEnum::TUESDAY->value,
            DayEnum::WEDNESDAY->value,
            DayEnum::THURSDAY->value,
            DayEnum::FRIDAY->value,
        ];

        $usedTeachers = [];
        $usedClassrooms = [];
        $teacherLoad = [];
        $createdCount = 0;

        // Inisialisasi beban mengajar
        foreach ($employees as $employee) {
            $teacherLoad[$employee->id] = 0;
        }

        foreach ($classrooms as $classroom) {
            $this->command->info("Processing classroom: {$classroom->name}");
            
            foreach ($days as $day) {
                $lessonHours = LessonHour::where('is_lesson', true)
                    ->where('day', $day)
                    ->orderBy('start')
                    ->get();

                if ($lessonHours->isEmpty()) {
                    continue;
                }

                $hourIndex = 0;
                $totalHours = $lessonHours->count();

                while ($hourIndex < $totalHours) {
                    // Tentukan berapa jam berurutan untuk block ini (1, 2, atau 3 jam)
                    $blockSize = $this->getBlockSize($hourIndex, $totalHours);
                    
                    // Ambil jam-jam untuk block ini
                    $blockHours = $lessonHours->slice($hourIndex, $blockSize);
                    $firstHour = $blockHours->first();
                    
                    // Cek apakah classroom sudah dipakai di timeslot ini
                    $timeSlotKey = $day . '_' . $firstHour->id;
                    if (isset($usedClassrooms[$classroom->id]) && 
                        in_array($timeSlotKey, $usedClassrooms[$classroom->id])) {
                        $hourIndex += $blockSize;
                        continue;
                    }

                    // Pilih guru dan mata pelajaran untuk block ini
                    $selectedEmployee = $this->getAvailableEmployee($employees, $day, $blockHours, $usedTeachers, $teacherLoad);
                    $selectedSubject = $subjects->random();

                    if ($selectedEmployee) {
                        // Buat jadwal untuk setiap jam dalam block
                        foreach ($blockHours as $hour) {
                            $timeSlotKey = $day . '_' . $hour->id;

                            // Update tracking
                            if (!isset($usedTeachers[$selectedEmployee->id])) {
                                $usedTeachers[$selectedEmployee->id] = [];
                            }
                            $usedTeachers[$selectedEmployee->id][] = $timeSlotKey;

                            if (!isset($usedClassrooms[$classroom->id])) {
                                $usedClassrooms[$classroom->id] = [];
                            }
                            $usedClassrooms[$classroom->id][] = $timeSlotKey;

                            $teacherLoad[$selectedEmployee->id]++;

                            // Create schedule
                            try {
                                LessonSchedule::updateOrCreate(
                                    [
                                        'classroom_id' => $classroom->id,
                                        'day' => $day,
                                        'lesson_hour_id' => $hour->id,
                                    ],
                                    [
                                        'id' => (string) Str::uuid(),
                                        'subject_id' => $selectedSubject->id,
                                        'employee_id' => $selectedEmployee->id,
                                    ]
                                );
                                $createdCount++;
                            } catch (\Exception $e) {
                                $this->command->error("Error: " . $e->getMessage());
                            }
                        }
                    }

                    $hourIndex += $blockSize;
                }
            }
        }

        $this->reportTeacherDistribution($teacherLoad, $employees, $createdCount);
    }

    /**
     * Tentukan ukuran block (1, 2, atau 3 jam) berdasarkan posisi dan sisa jam
     */
    private function getBlockSize(int $currentIndex, int $totalHours): int
    {
        $remaining = $totalHours - $currentIndex;
        
        // Jika sisa 1 jam, harus ambil 1 jam
        if ($remaining === 1) {
            return 1;
        }
        
        // Jika sisa 2 jam, bisa ambil 1 atau 2 jam (50% chance masing-masing)
        if ($remaining === 2) {
            return fake()->boolean(50) ? 1 : 2;
        }
        
        // Untuk sisa 3+ jam, pilih random antara 1, 2, atau 3 jam
        // Dengan probabilitas: 1jam=30%, 2jam=50%, 3jam=20%
        $random = fake()->numberBetween(1, 100);
        if ($random <= 30) return 1;
        if ($random <= 80) return 2;
        return 3;
    }

    /**
     * Cari guru yang available untuk seluruh jam dalam block
     */
    private function getAvailableEmployee($employees, $day, $blockHours, &$usedTeachers, &$teacherLoad)
    {
        $availableEmployees = $employees->filter(function ($employee) use ($day, $blockHours, $usedTeachers, $teacherLoad) {
            // Cek untuk setiap jam dalam block, guru harus available
            foreach ($blockHours as $hour) {
                $timeSlotKey = $day . '_' . $hour->id;
                
                // Cek konflik timeslot
                $hasTimeConflict = isset($usedTeachers[$employee->id]) && 
                                  in_array($timeSlotKey, $usedTeachers[$employee->id]);
                
                // Cek overload (maksimal 25 jam/minggu)
                $isOverloaded = $teacherLoad[$employee->id] >= 25;
                
                if ($hasTimeConflict || $isOverloaded) {
                    return false;
                }
            }
            return true;
        });

        if ($availableEmployees->isEmpty()) {
            return null;
        }

        // Prioritaskan guru dengan beban paling sedikit
        return $availableEmployees->sortBy(function ($employee) use ($teacherLoad) {
            return $teacherLoad[$employee->id];
        })->first();
    }

    private function reportTeacherDistribution($teacherLoad, $employees, $createdCount): void
    {
        $this->command->info("\n=== Teacher Load Distribution ===");
        
        $loadDistribution = [0, 0, 0, 0, 0];
        $totalTeachingHours = 0;
        $teachersWithLoad = 0;
        
        foreach ($teacherLoad as $teacherId => $load) {
            $teacher = $employees->firstWhere('id', $teacherId);
            $teacherName = $teacher ? $teacher->user->name : 'Unknown';
            
            if ($load > 0) {
                $teachersWithLoad++;
                $totalTeachingHours += $load;
            }
            
            if ($load === 0) $loadDistribution[0]++;
            elseif ($load <= 5) $loadDistribution[1]++;
            elseif ($load <= 10) $loadDistribution[2]++;
            elseif ($load <= 15) $loadDistribution[3]++;
            else $loadDistribution[4]++;
            
            if ($load > 0) {
                $this->command->info("{$teacherName}: {$load} teaching hours");
            }
        }

        $averageLoad = $teachersWithLoad > 0 ? round($totalTeachingHours / $teachersWithLoad, 1) : 0;
        
        $this->command->info("\n=== Load Summary ===");
        $this->command->info("0 hours: {$loadDistribution[0]} teachers");
        $this->command->info("1-5 hours: {$loadDistribution[1]} teachers");
        $this->command->info("6-10 hours: {$loadDistribution[2]} teachers");
        $this->command->info("11-15 hours: {$loadDistribution[3]} teachers");
        $this->command->info("16+ hours: {$loadDistribution[4]} teachers");
        $this->command->info("Average load: {$averageLoad} hours per teacher");
        $this->command->info("Total schedules created: {$createdCount}");
        $this->command->info("Teachers with assignment: {$teachersWithLoad}/{$employees->count()}");
    }
}