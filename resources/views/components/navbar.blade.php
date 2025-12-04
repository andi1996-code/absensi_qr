<div
    class="bg-white/[0.07] backdrop-blur-2xl rounded-lg sm:rounded-xl md:rounded-2xl shadow-2xl p-3 sm:p-4 md:p-5 lg:p-6 border border-white/10 mb-1.5 sm:mb-2 md:mb-2.5 lg:mb-3 flex items-center justify-between gap-3 sm:gap-4 md:gap-5">
    <!-- Left: Logo/Image -->
    <div class="shrink-0 flex items-center gap-2 sm:gap-2.5 md:gap-3 lg:gap-4">
        <!-- Logo Sekolah -->
        <div
            class="w-14 sm:w-16 md:w-20 lg:w-24 h-14 sm:h-16 md:h-20 lg:h-24 rounded-lg sm:rounded-xl md:rounded-2xl overflow-hidden border-2 border-white/20 shadow-lg flex items-center justify-center bg-linear-to-br from-purple-500/20 to-blue-500/20">
            @if (file_exists(public_path('storage/image-page/logo-sekolah.jpeg')))
                <img src="{{ asset('storage/image-page/logo-sekolah.jpeg') }}" alt="Logo SMK"
                    class="w-full h-full object-cover" />
            @else
                <svg class="w-7 sm:w-8 md:w-10 lg:w-12 h-7 sm:h-8 md:h-10 lg:h-12 text-purple-300" fill="currentColor"
                    viewBox="0 0 24 24">
                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" />
                </svg>
            @endif
        </div>

        <!-- Logo Lampung -->
        <div
            class="w-14 sm:w-16 md:w-20 lg:w-24 h-14 sm:h-16 md:h-20 lg:h-24 rounded-lg sm:rounded-xl md:rounded-2xl overflow-hidden border-2 border-white/20 shadow-lg flex items-center justify-center bg-linear-to-br from-red-500/20 to-orange-500/20">
            @if (file_exists(public_path('storage/image-page/logo-lampung.jpeg')))
                <img src="{{ asset('storage/image-page/logo-lampung.jpeg') }}" alt="Logo Lampung"
                    class="w-full h-full object-scale-down" />
            @else
                <svg class="w-7 sm:w-8 md:w-10 lg:w-12 h-7 sm:h-8 md:h-10 lg:h-12 text-red-300" fill="currentColor"
                    viewBox="0 0 24 24">
                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" />
                </svg>
            @endif
        </div>
    </div>

    <!-- Center: School Name & Info -->
    <div class="flex-1 text-right">
        <h2
            class="text-xs sm:text-sm md:text-base lg:text-xl font-black bg-gradient-to-r from-purple-300 to-blue-300 bg-clip-text text-transparent line-clamp-1">
            PUSAT KEUNGGULAN</h2>
        <h2
            class="text-xs sm:text-sm md:text-base lg:text-4xl font-black bg-gradient-to-r from-purple-300 to-blue-300 bg-clip-text text-transparent line-clamp-1">
            SMK DARUL FIKRI</h2>

    </div>
</div>
