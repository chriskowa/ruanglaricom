<?php

namespace Tests\Unit;

use App\Models\Participant;
use Carbon\Carbon;
use Tests\TestCase;

class AgeGroupTest extends TestCase
{
    /**
     * Test Umum category (< 40 years)
     */
    public function test_age_group_umum()
    {
        $participant = new Participant();
        
        // 39 years old
        $eventDate = Carbon::parse('2024-01-01');
        $participant->date_of_birth = $eventDate->copy()->subYears(39);
        
        $this->assertEquals('Umum', $participant->getAgeGroup($eventDate));
        
        // 39 years and 364 days old (almost 40)
        $participant->date_of_birth = $eventDate->copy()->subYears(40)->addDay();
        $this->assertEquals('Umum', $participant->getAgeGroup($eventDate));
    }

    /**
     * Test Master category (>= 40 and < 45 years)
     */
    public function test_age_group_master()
    {
        $participant = new Participant();
        $eventDate = Carbon::parse('2024-01-01');

        // Exactly 40 years old
        $participant->date_of_birth = $eventDate->copy()->subYears(40);
        $this->assertEquals('Master', $participant->getAgeGroup($eventDate));

        // 44 years old
        $participant->date_of_birth = $eventDate->copy()->subYears(44);
        $this->assertEquals('Master', $participant->getAgeGroup($eventDate));

        // Almost 45 (45 years - 1 day)
        $participant->date_of_birth = $eventDate->copy()->subYears(45)->addDay();
        $this->assertEquals('Master', $participant->getAgeGroup($eventDate));
    }

    /**
     * Test Master 45+ category (>= 45 and < 50 years)
     */
    public function test_age_group_master_45_plus()
    {
        $participant = new Participant();
        $eventDate = Carbon::parse('2024-01-01');

        // Exactly 45 years old
        $participant->date_of_birth = $eventDate->copy()->subYears(45);
        $this->assertEquals('Master 45+', $participant->getAgeGroup($eventDate));

        // 49 years old
        $participant->date_of_birth = $eventDate->copy()->subYears(49);
        $this->assertEquals('Master 45+', $participant->getAgeGroup($eventDate));
        
        // Almost 50 (50 years - 1 day)
        $participant->date_of_birth = $eventDate->copy()->subYears(50)->addDay();
        $this->assertEquals('Master 45+', $participant->getAgeGroup($eventDate));
    }

    /**
     * Test 50+ category (>= 50 years)
     */
    public function test_age_group_50_plus()
    {
        $participant = new Participant();
        $eventDate = Carbon::parse('2024-01-01');

        // Exactly 50 years old
        $participant->date_of_birth = $eventDate->copy()->subYears(50);
        $this->assertEquals('50+', $participant->getAgeGroup($eventDate));

        // 60 years old
        $participant->date_of_birth = $eventDate->copy()->subYears(60);
        $this->assertEquals('50+', $participant->getAgeGroup($eventDate));
    }

    /**
     * Test missing date of birth or event date
     */
    public function test_age_group_missing_data()
    {
        $participant = new Participant();
        $eventDate = Carbon::parse('2024-01-01');

        // Missing DOB
        $participant->date_of_birth = null;
        $this->assertEquals('-', $participant->getAgeGroup($eventDate));

        // Missing Event Date
        $participant->date_of_birth = Carbon::parse('1990-01-01');
        $this->assertEquals('-', $participant->getAgeGroup(null));
    }
}
