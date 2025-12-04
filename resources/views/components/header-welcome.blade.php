<div class="relative">
    <!-- Logo Kiri - Position Absolute -->
    {{-- <div class="absolute left-0 top-1/2 -translate-y-1/2 w-32 sm:w-36 md:w-44 lg:w-52 min-w-32 sm:min-w-36 md:min-w-44 lg:min-w-52 z-10">
        <!-- Container logo: ubah w-XX dan h-XX untuk mengatur ukuran -->
        <div class="w-full h-24 sm:h-28 md:h-32 lg:h-40 rounded-lg sm:rounded-xl md:rounded-2xl overflow-hidden border-2 border-white/20 shadow-lg flex items-center justify-center bg-linear-to-br from-purple-500/20 to-blue-500/20">
            @if (file_exists(public_path('storage/image-page/PKPM SMK DF.png')))
                <!-- Pilih salah satu object-fit:
                     object-contain = muat penuh (tidak terpotong, aspect ratio tetap)
                     object-cover = memenuhi container (terpotong jika perlu)
                     object-fill = stretch (distorsi jika aspect ratio beda)
                -->
                <img src="{{ asset('storage/image-page/PKPM SMK DF.png') }}" alt="Logo SMK" class="w-full h-full object-fill" />
            @else
                <svg class="w-8 sm:w-10 md:w-12 lg:w-14 h-8 sm:h-10 md:h-12 lg:h-14 text-purple-300" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" />
                </svg>
            @endif
        </div>
    </div> --}}

    <!-- Text Content - Benar-benar di tengah layar, tidak terpengaruh logo -->
    <div class="w-full text-center py-4 sm:py-5 md:py-6">
        <div class="flex flex-col items-center justify-center gap-1 sm:gap-2 md:gap-3 mb-0.5 sm:mb-1 md:mb-2">
            <h1 class="text-lg sm:text-2xl md:text-3xl lg:text-3xl font-black bg-gradient-to-r from-purple-300 to-blue-300 bg-clip-text text-transparent">
                SISTEM ABSENSI DIGITAL SMK DARUL FIKRI
            </h1>
            <h1 class="text-lg sm:text-2xl md:text-3xl lg:text-3xl font-black bg-gradient-to-r from-purple-300 to-blue-300 bg-clip-text text-transparent">
                SILAHKAN ABSEN DISINI
            </h1>
        </div>
        <p class="text-[10px] sm:text-xs md:text-sm lg:text-base text-slate-300 font-medium">Scan QR code guru untuk mencatat kehadiran guru mengajar</p>
    </div>
</div>
