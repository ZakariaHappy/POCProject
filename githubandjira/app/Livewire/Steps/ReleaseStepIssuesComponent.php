<?php

namespace App\Livewire\Steps;

use Livewire\Component;

class ReleaseStepIssuesComponent extends Component
{
    public array $issues = [];

    public function goToNext()
    {
        $this->emitUp('goToStep', 2);
    }

    public function render()
    {
        return view('livewire.steps.release-step-issues-component');
    }
}
