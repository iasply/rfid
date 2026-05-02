<?php

namespace Tests\Feature;

use App\Models\Workstation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkstationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function workstation_can_be_created()
    {
        $workstation = Workstation::create([
            'hash' => 'WS-123',
            'desc' => 'Main Lab Workstation',
        ]);

        $this->assertDatabaseHas('workstations', [
            'hash' => 'WS-123',
            'desc' => 'Main Lab Workstation',
        ]);
    }

}
