@php
    use Filament\Support\Enums\MaxWidth;
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Info Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex items-center gap-3">
                    <div class="text-3xl">📊</div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Aturan Gaji</p>
                        <p class="font-semibold text-gray-900 dark:text-white">Rp 7.500/jam</p>
                    </div>
                </div>
            </div>

            <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg border border-orange-200 dark:border-orange-800">
                <div class="flex items-center gap-3">
                    <div class="text-3xl">💰</div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Honor Tidak Hadir</p>
                        <p class="font-semibold text-gray-900 dark:text-white">Rp 3.500/jam</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                <div class="flex items-center gap-3">
                    <div class="text-3xl">✅</div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                        <p class="font-semibold text-gray-900 dark:text-white">Siap Generate</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="space-y-6">
            {{ $this->form }}

            <div class="flex gap-3 pt-4">
                <button type="button"
                    wire:click="$set('showConfirmation', true)"
                    style="background-color: #2563eb !important; color: white !important; padding: 12px 32px !important; font-weight: bold !important; font-size: 18px !important; border-radius: 8px !important; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; transition: all 0.3s !important; cursor: pointer !important; display: flex !important; align-items: center !important; gap: 8px !important; border: none !important;"
                    onmouseover="this.style.backgroundColor='#1d4ed8 !important'; this.style.transform='scale(1.05)';"
                    onmouseout="this.style.backgroundColor='#2563eb !important'; this.style.transform='scale(1)';">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    GENERATE GAJI SEMUA GURU
                </button>
            </div>
        </div>

        <!-- Confirmation Modal -->
        @if($showConfirmation)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 max-w-md w-full mx-4 transform transition">
                    <div class="text-center mb-6">
                        <div class="text-5xl mb-4">💰</div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Konfirmasi Generate Gaji</h3>
                    </div>

                    <p class="text-gray-600 dark:text-gray-400 mb-6 text-center text-lg">
                        Data gaji yang sudah ada akan diperbarui. Lanjutkan?
                    </p>

                    <div class="flex gap-3 justify-center">
                        <button type="button"
                            wire:click="$set('showConfirmation', false)"
                            style="background-color: transparent !important; color: #374151 !important; padding: 12px 24px !important; border: 2px solid #d1d5db !important; border-radius: 8px !important; font-weight: 600 !important; cursor: pointer !important;"
                            onmouseover="this.style.backgroundColor='#f3f4f6 !important';"
                            onmouseout="this.style.backgroundColor='transparent !important';">
                            ❌ Batal
                        </button>
                        <button type="button"
                            wire:click="generate"
                            style="background-color: #2563eb !important; color: white !important; padding: 12px 24px !important; border: none !important; border-radius: 8px !important; font-weight: bold !important; cursor: pointer !important; transition: all 0.3s !important;"
                            onmouseover="this.style.backgroundColor='#1d4ed8 !important'; this.style.transform='scale(1.05)';"
                            onmouseout="this.style.backgroundColor='#2563eb !important'; this.style.transform='scale(1)';">
                            ✅ Ya, Generate
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Instructions -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex gap-3">
                <div class="text-2xl">ℹ️</div>
                <div>
                    <p class="font-semibold text-yellow-900 dark:text-yellow-100 mb-2">Cara Menggunakan:</p>
                    <ul class="text-sm text-yellow-800 dark:text-yellow-200 space-y-1">
                        <li>✓ Pilih tahun dan bulan yang ingin di-generate</li>
                        <li>✓ Sistem akan menghitung jam hadir dan jam tidak hadir dari data absensi pelajaran</li>
                        <li>✓ Total gaji = (jam hadir × Rp 7.500) + (jam tidak hadir × Rp 3.500)</li>
                        <li>✓ Setelah di-generate, data bisa dilihat di menu "Gaji" untuk dicetak slip</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
