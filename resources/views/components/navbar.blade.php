<div
    class="rounded-lg sm:rounded-xl md:rounded-2xl shadow-2xl p-2 sm:p-2.5 md:p-3 lg:p-4 border border-white/10 mb-1.5 sm:mb-2 md:mb-2.5 lg:mb-3 flex items-center justify-between gap-2 sm:gap-3 md:gap-4">
    <!-- Left: Logo/Image -->
    <div class="flex-1 flex items-center justify-center gap-1.5 sm:gap-2 md:gap-2.5 lg:gap-3">
        <!-- Logo Sekolah -->
        <div
            class="w-12 sm:w-14 md:w-16 lg:w-20 h-12 sm:h-14 md:h-16 lg:h-20 rounded-lg sm:rounded-xl md:rounded-2xl overflow-hidden border-2 border-white/20 shadow-lg flex items-center justify-center bg-linear-to-br from-purple-500/20 to-blue-500/20">
            @if (file_exists(public_path('storage/image-page/logo-sekolah.jpeg')))
                <img src="{{ asset('storage/image-page/logo-sekolah.jpeg') }}" alt="Logo SMK"
                    class="w-full h-full object-cover" />
            @else
                <svg class="w-6 sm:w-7 md:w-8 lg:w-10 h-6 sm:h-7 md:h-8 lg:h-10 text-purple-300" fill="currentColor"
                    viewBox="0 0 24 24">
                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" />
                </svg>
            @endif
        </div>

        <!-- Logo Lampung -->
        <div
            class="w-12 sm:w-14 md:w-16 lg:w-20 h-12 sm:h-14 md:h-16 lg:h-20 rounded-lg sm:rounded-xl md:rounded-2xl overflow-hidden border-2 border-white/20 shadow-lg flex items-center justify-center bg-linear-to-br from-red-500/20 to-orange-500/20">
            @if (file_exists(public_path('storage/image-page/logo-lampung.jpeg')))
                <img src="{{ asset('storage/image-page/logo-lampung.jpeg') }}" alt="Logo Lampung"
                    class="w-full h-full object-scale-down" />
            @else
                <svg class="w-6 sm:w-7 md:w-8 lg:w-10 h-6 sm:h-7 md:h-8 lg:h-10 text-red-300" fill="currentColor"
                    viewBox="0 0 24 24">
                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" />
                </svg>
            @endif
        </div>

        <div
            class="w-16 sm:w-20 md:w-24 lg:w-28 h-12 rounded-lg sm:rounded-xl md:rounded-2xl overflow-hidden border-2 border-white/20 shadow-lg flex items-center justify-center bg-linear-to-br from-red-500/20 to-orange-500/20">
            @if (file_exists(public_path('storage/image-page/PKPM SMK DF.png')))
                <img src="{{ asset('storage/image-page/PKPM SMK DF.png') }}" alt="Logo Lampung"
                    class="w-full h-full object-scale-down" />
            @else
                <svg class="w-6 sm:w-7 md:w-8 lg:w-10 h-6 sm:h-7 md:h-8 lg:h-10 text-red-300" fill="currentColor"
                    viewBox="0 0 24 24">
                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" />
                </svg>
            @endif
        </div>
    </div>

    <!-- Center: School Name & Info -->
    {{-- <div class="flex-1 text-right">
        <h2
            class="text-[10px] sm:text-xs md:text-sm lg:text-lg font-black bg-gradient-to-r from-purple-300 to-blue-300 bg-clip-text text-transparent line-clamp-1">
            PUSAT KEUNGGULAN</h2>
        <h2
            class="text-xs sm:text-sm md:text-base lg:text-2xl font-black bg-gradient-to-r from-purple-300 to-blue-300 bg-clip-text text-transparent line-clamp-1">
            SMK DARUL FIKRI</h2>

    </div> --}}
</div>
