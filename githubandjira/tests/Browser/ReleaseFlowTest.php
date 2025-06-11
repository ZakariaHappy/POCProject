<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ReleaseFlowTest extends DuskTestCase
{
    public function testReleaseWorkflowEndToEnd()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                ->pause(500)
                ->screenshot('before-sidebar-release')
                ->waitFor('@sidebar-release', 10)
                ->assertSee('Release Workflow')
                ->screenshot('after-sidebar-release');
        });
    }
}
