<div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
    <!-- Kolom Kiri: Gambar/Konten -->
    <div class="relative overflow-hidden bg-slate-800 ">

        <!-- Gambar Background Full -->
        <img src="{{ asset('storage/image-page/image-login.webp') }}" alt="Login Illustration"
            class="absolute inset-0 w-full h-full object-cover rounded-tr-4xl rounded-br-4xl">
    </div>


    <!-- Kolom Kanan: Form Login -->
    <div class="flex flex-col justify-center p-12 bg-slate-800">
        <div class="w-full max-w-md mx-auto rounded-lg p-0.5 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500">
            <div class="bg-slate-800 rounded-lg p-8 space-y-8">
                <img src="{{ asset('storage/image-page/logo-sekolah.jpeg') }}" alt="logo sekolah" class="mx-auto w-24 h-24 rounded-full border-4 border-white mb-4">
                <!-- Header -->
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-white mb-2">
                        Sistem Absensi Digital
                    </h2>
                    <h2 class="text-4xl font-bold text-white mb-2">
                        SMKS DARUL FIKRI
                    </h2>

                    <p class="text-sm text-slate-400">
                        Silakan masuk ke akun Anda
                    </p>
                </div>

                <!-- Error Message -->
                @if ($error)
                    <div class="p-4 bg-red-500/10 border border-red-500/50 rounded-lg">
                        <p class="text-sm text-red-400">{{ $error }}</p>
                    </div>
                @endif

                <!-- Form -->
                <form wire:submit.prevent="login" class="space-y-5">
                    @foreach ($this->form->getComponents() as $component)
                        {{ $component }}
                    @endforeach

                    <button type="submit"
                        class="w-full py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-150 shadow-lg shadow-blue-600/20">
                        {{ __('Masuk') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
