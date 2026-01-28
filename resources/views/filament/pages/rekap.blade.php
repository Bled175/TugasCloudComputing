<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filters Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4">🔍 Filter</h3>
            
            <form wire:submit="submit" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{ $this->form }}
                </div>

                <div class="flex gap-2 justify-end pt-4">
                    <x-filament::button
                        type="button"
                        wire:click="resetFilters"
                        color="gray"
                        icon="heroicon-m-arrow-path"
                    >
                        Reset Filter
                    </x-filament::button>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow p-4 border-l-4 border-green-500">
                <p class="text-sm text-green-700 font-semibold">Total Hadir</p>
                <p class="text-3xl font-bold text-green-600 mt-2">
                    {{ $this->getQuery()->where('status', 'hadir')->count() }}
                </p>
            </div>

            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow p-4 border-l-4 border-yellow-500">
                <p class="text-sm text-yellow-700 font-semibold">Total Izin</p>
                <p class="text-3xl font-bold text-yellow-600 mt-2">
                    {{ $this->getQuery()->where('status', 'izin')->count() }}
                </p>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow p-4 border-l-4 border-blue-500">
                <p class="text-sm text-blue-700 font-semibold">Total Sakit</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">
                    {{ $this->getQuery()->where('status', 'sakit')->count() }}
                </p>
            </div>

            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow p-4 border-l-4 border-red-500">
                <p class="text-sm text-red-700 font-semibold">Total Alpha</p>
                <p class="text-3xl font-bold text-red-600 mt-2">
                    {{ $this->getQuery()->where('status', 'alpha')->count() }}
                </p>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-bold">📊 Data Absensi</h3>
                
                <x-filament::button
                    type="button"
                    wire:click="exportExcel"
                    icon="heroicon-m-arrow-down-tray"
                    color="success"
                >
                    Export Excel
                </x-filament::button>
            </div>

            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
