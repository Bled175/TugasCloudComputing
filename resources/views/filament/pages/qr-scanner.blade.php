<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Camera Section -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold mb-4">📷 Scan QR Code</h2>
                
                <div class="relative bg-gray-900 rounded-lg overflow-hidden" style="aspect-ratio: 4/3;">
                    <video id="qr-video" playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-48 h-48 border-2 border-green-400 rounded-lg relative"
                             style="box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);">
                            <div class="absolute -top-1 -left-1 w-6 h-6 border-t-2 border-l-2 border-green-400"></div>
                            <div class="absolute -top-1 -right-1 w-6 h-6 border-t-2 border-r-2 border-green-400"></div>
                            <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-2 border-l-2 border-green-400"></div>
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-2 border-r-2 border-green-400"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm text-gray-700">
                        <strong>💡 Tips:</strong> Arahkan kamera ke QR code siswa. Pastikan pencahayaan cukup untuk scan yang optimal.
                    </p>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6 sticky top-6">
                <h2 class="text-lg font-bold mb-4">✅ Hasil Scan</h2>

                @if($scannedStudent)
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded mb-4 animate-pulse">
                        <p class="font-semibold text-green-800">{{ $scannedStudent->nama }}</p>
                        <p class="text-sm text-green-700">{{ $scannedStudent->kelas }}</p>
                        <p class="text-xs text-green-600 mt-2">
                            ✓ Berhasil diabsensi pada {{ now()->format('H:i:s') }}
                        </p>
                    </div>
                @endif

                <h3 class="text-sm font-bold mt-6 mb-3">📋 Scan Terbaru (Hari Ini)</h3>
                
                @if(count($recentScans) > 0)
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($recentScans as $scan)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded border border-gray-200 text-sm hover:bg-gray-100 transition">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $scan['nama'] }}</p>
                                    <p class="text-xs text-gray-600">{{ $scan['kelas'] }}</p>
                                </div>
                                <p class="text-xs font-mono text-gray-500">{{ $scan['waktu'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 italic">Belum ada scan hari ini</p>
                @endif

                <!-- Statistics -->
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-700">
                        <strong>📊 Total Scan Hari Ini:</strong>
                        <span class="text-lg font-bold text-blue-600">{{ count($recentScans) }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden input for wire:model -->
    <input type="hidden" id="qr-input" wire:model.live="qr_token" />

    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('qr-video');
            const input = document.getElementById('qr-input');
            let stream = null;
            let isScanning = true;
            let lastScannedCode = null;
            let lastScanTime = 0;

            async function startCamera() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { 
                            facingMode: 'environment',
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        }
                    });
                    video.srcObject = stream;
                    video.setAttribute('playsinline', true);
                    video.play().then(() => {
                        scanQRCode();
                    });
                } catch (err) {
                    console.error('Camera error:', err);
                    alert('Gagal mengakses kamera: ' + err.message);
                }
            }

            function scanQRCode() {
                if (!isScanning || !video.videoWidth) {
                    requestAnimationFrame(scanQRCode);
                    return;
                }

                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert',
                });

                if (code) {
                    const now = Date.now();
                    // Only process if different code or 1+ second has passed
                    if (code.data !== lastScannedCode || (now - lastScanTime) > 1000) {
                        lastScannedCode = code.data;
                        lastScanTime = now;
                        
                        // Trigger Livewire update
                        input.value = code.data;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }

                requestAnimationFrame(scanQRCode);
            }

            // Start camera
            startCamera();

            // Handle page visibility
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    isScanning = false;
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                    }
                } else {
                    isScanning = true;
                    startCamera();
                }
            });

            // Cleanup
            window.addEventListener('beforeunload', function() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }
            });
        });

        // Listen for clear event
        document.addEventListener('clear-scanned-after-delay', function() {
            setTimeout(() => {
                @this.set('scannedStudent', null);
            }, 3000);
        });
    </script>
</x-filament-panels::page>

