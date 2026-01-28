<?php

namespace App\Livewire;

use Livewire\Component;

class QrCodeScanner extends Component
{
    public string $scannedCode = '';

    public function dispatchScan(string $code): void
    {
        $this->scannedCode = $code;
        $this->dispatch('qr-scanned', code: $code);
    }

    public function render()
    {
        return view('livewire.qr-code-scanner');
    }
}
