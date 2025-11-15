@php
    use Filament\Support\Enums\MaxWidth;
    use Illuminate\Support\Facades\Storage;
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Info Card -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-6 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex items-center gap-4">
                @if(!empty($data['logo_path']) && is_string($data['logo_path']))
                    <img src="{{ Storage::url($data['logo_path']) }}" alt="Logo" class="w-24 h-24 object-cover rounded-lg">
                @else
                    <div class="text-5xl">🏫</div>
                @endif
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $data['name'] ?? 'Nama Sekolah' }}</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Kelola informasi dasar dan identitas sekolah Anda</p>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form wire:submit="save" class="space-y-6">
                {{ $this->form }}

                <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit"
                        style="background-color: #2563eb !important; color: white !important; padding: 12px 32px !important; font-weight: bold !important; font-size: 16px !important; border-radius: 8px !important; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; transition: all 0.3s !important; cursor: pointer !important; border: none !important;"
                        onmouseover="this.style.backgroundColor='#1d4ed8 !important'; this.style.transform='scale(1.05)';"
                        onmouseout="this.style.backgroundColor='#2563eb !important'; this.style.transform='scale(1)';">
                        💾 Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">📧</span>
                    <h3 class="font-semibold text-purple-900 dark:text-purple-100">Email</h3>
                </div>
                <p class="text-sm text-purple-800 dark:text-purple-200 break-all">
                    {{ $data['email'] ?? '—' }}
                </p>
            </div>

            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">📞</span>
                    <h3 class="font-semibold text-green-900 dark:text-green-100">Telepon</h3>
                </div>
                <p class="text-sm text-green-800 dark:text-green-200">
                    {{ $data['phone'] ?? '—' }}
                </p>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">📍</span>
                    <h3 class="font-semibold text-blue-900 dark:text-blue-100">Alamat</h3>
                </div>
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    {{ $data['address'] ?? '—' }}
                </p>
            </div>

            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">🏛️</span>
                    <h3 class="font-semibold text-orange-900 dark:text-orange-100">NPSN</h3>
                </div>
                <p class="text-sm text-orange-800 dark:text-orange-200">
                    {{ $data['npsn'] ?? '—' }}
                </p>
            </div>
        </div>

        <!-- Important Note -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex gap-3">
                <div class="text-2xl">ℹ️</div>
                <div>
                    <p class="font-semibold text-yellow-900 dark:text-yellow-100 mb-2">Catatan Penting:</p>
                    <ul class="text-sm text-yellow-800 dark:text-yellow-200 space-y-1">
                        <li>✓ Data profil ini hanya ada 1 untuk seluruh sekolah</li>
                        <li>✓ Informasi ini akan ditampilkan di slip gaji dan laporan</li>
                        <li>✓ Pastikan data sudah benar sebelum dicetak</li>
                        <li>✓ Perubahan akan langsung berlaku untuk dokumen baru</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
