<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\Program;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // School (tenant)
        $school = School::create([
            'name'          => 'Apex Trade Institute',
            'slug'          => 'apex',
            'primary_color' => '#d97706',
        ]);

        // Admin user for the Filament panel
        User::create([
            'name'      => 'Admin User',
            'email'     => 'admin@apextrade.test',
            'password'  => Hash::make('password'),
            'school_id' => $school->id,
        ]);

        // Programs
        $programsData = [
            ['name' => 'Electrical Fundamentals', 'description' => 'NEC code, wiring, panel installation, and safety.', 'duration_weeks' => 16, 'tuition_cost' => 6500],
            ['name' => 'HVAC Technician',          'description' => 'Refrigeration cycle, ductwork, EPA 608 certification prep.', 'duration_weeks' => 24, 'tuition_cost' => 8200],
            ['name' => 'Plumbing Essentials',      'description' => 'Pipe fitting, drainage systems, fixture installation.', 'duration_weeks' => 20, 'tuition_cost' => 7000],
            ['name' => 'Welding & Fabrication',    'description' => 'MIG, TIG, and stick welding with hands-on shop time.', 'duration_weeks' => 12, 'tuition_cost' => 5400],
            ['name' => 'Automotive Service Tech',  'description' => 'Engine diagnostics, brakes, suspension, and electrical systems.', 'duration_weeks' => 28, 'tuition_cost' => 9100],
        ];

        $programs = [];
        foreach ($programsData as $p) {
            $programs[] = Program::create(array_merge($p, [
                'school_id' => $school->id,
                'is_active' => true,
            ]));
        }

        // Enrolled students with payment plans (mix of paid / overdue / pending)
        $studentsData = [
            ['first_name' => 'James',   'last_name' => 'Rivera',   'email' => 'james.rivera@email.test'],
            ['first_name' => 'Maria',   'last_name' => 'Chen',     'email' => 'maria.chen@email.test'],
            ['first_name' => 'Darnell', 'last_name' => 'Brooks',   'email' => 'darnell.brooks@email.test'],
        ];

        foreach ($studentsData as $i => $s) {
            $student = Student::create(array_merge($s, [
                'school_id'    => $school->id,
                'phone'        => '555-' . rand(100, 999) . '-' . rand(1000, 9999),
                'portal_token' => Str::uuid(),
            ]));

            $program = $programs[$i % count($programs)];

            $enrollment = Enrollment::create([
                'school_id'  => $school->id,
                'student_id' => $student->id,
                'program_id' => $program->id,
                'enrolled_at' => now()->subMonths(3)->toDateString(),
                'status'     => 'active',
            ]);

            $installments = 6;
            $perInstallment = round($program->tuition_cost / $installments, 2);
            $startDate = now()->subMonths(3)->startOfMonth();

            $plan = PaymentPlan::create([
                'enrollment_id'          => $enrollment->id,
                'school_id'              => $school->id,
                'total_amount'           => $program->tuition_cost,
                'amount_per_installment' => $perInstallment,
                'installment_count'      => $installments,
                'frequency'              => 'monthly',
                'start_date'             => $startDate->toDateString(),
                'status'                 => 'active',
            ]);

            for ($j = 0; $j < $installments; $j++) {
                $dueDate = $startDate->copy()->addMonths($j);
                $isPast = $dueDate->isPast();
                $isPaid = $isPast && $j < 3; // first 3 months paid, rest overdue/pending

                Payment::create([
                    'enrollment_id'   => $enrollment->id,
                    'school_id'       => $school->id,
                    'payment_plan_id' => $plan->id,
                    'amount'          => $perInstallment,
                    'due_date'        => $dueDate->toDateString(),
                    'status'          => match (true) {
                        $isPaid  => 'paid',
                        $isPast  => 'overdue',
                        default  => 'pending',
                    },
                    'paid_at'        => $isPaid ? $dueDate->copy()->addDays(2) : null,
                    'payment_method' => $isPaid ? 'card' : null,
                    'transaction_id' => $isPaid ? 'MOCK-' . strtoupper(Str::random(12)) : null,
                ]);
            }
        }

        // Pending applicants awaiting review
        $pending = [
            ['first_name' => 'Sofia',  'last_name' => 'Nguyen',  'email' => 'sofia.nguyen@email.test',   'program' => 3],
            ['first_name' => 'Marcus', 'last_name' => 'Johnson', 'email' => 'marcus.johnson@email.test', 'program' => 4],
        ];

        foreach ($pending as $p) {
            Application::create([
                'school_id'         => $school->id,
                'program_id'        => $programs[$p['program']]->id,
                'first_name'        => $p['first_name'],
                'last_name'         => $p['last_name'],
                'email'             => $p['email'],
                'phone'             => '555-' . rand(100, 999) . '-' . rand(1000, 9999),
                'application_token' => Str::uuid(),
                'status'            => 'pending',
            ]);
        }
    }
}
