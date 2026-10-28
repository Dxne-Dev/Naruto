@extends('layouts.app')

@section('content')
<div class="relative min-h-[calc(100vh-4rem)] flex items-center justify-center p-4 sm:p-6 overflow-hidden">
    
    <!-- Image naruto-hero en arrière-plan complet -->
    <img src="{{ asset('images/naruto-hero.png') }}" 
         alt="Naruto Examen Chunin" 
         class="absolute inset-0 w-full h-full object-cover object-center pointer-events-none select-none">

    <!-- Voile sombre immersif pour garantir un contraste et une lisibilité parfaits du formulaire -->
    <div class="absolute inset-0 bg-shinobi-dark/75 backdrop-blur-[2px] pointer-events-none"></div>

    <!-- Carte d'Authentification Otaku Arena -->
    <div class="relative z-10 w-full max-w-md my-auto">
        <div class="glass3d overflow-hidden rounded-3xl border border-shinobi-border/60 p-5 sm:p-6">
            
            <!-- En-tête formulaire -->
            <div class="relative text-center mb-5 space-y-1.5">
                <div class="inline-flex items-center gap-1.5 rounded-full border border-shinobi-red/30 bg-shinobi-red/10 px-3 py-1 font-mono text-[10px] font-bold tracking-wider text-shinobi-red uppercase">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-shinobi-red animate-pulse"></span>
                    AUTHENTIFICATION SHINOBI
                </div>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold uppercase tracking-tight text-white leading-tight">Connexion</h2>
                <p class="font-mono text-xs text-slate-400">
                    Retrouve ton combattant, consulte tes tournois et retourne au combat.
                </p>
            </div>

            <!-- Formulaire -->
            <form method="POST" action="{{ route('login.post') }}" class="relative space-y-4">
                @csrf

                <!-- Identifiant (Pseudo ou Email) -->
                <div class="space-y-1.5">
                    <label for="login" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-slate-300">
                        PSEUDONYME OU EMAIL
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-500 text-sm">
                            <i class="bi bi-person-fill"></i>
                        </span>
                        <input type="text" 
                               id="login" 
                               name="login" 
                               value="{{ old('login') }}" 
                               required 
                               autofocus 
                               placeholder="Ex: Sasuke99 ou ninja@konoha.com"
                               class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark/80 pl-10 pr-3.5 py-3 font-mono text-xs sm:text-sm text-white placeholder-slate-500 focus:border-shinobi-red focus:outline-none focus:ring-1 focus:ring-shinobi-red transition @error('login') border-shinobi-red text-red-200 @enderror">
                    </div>
                    @error('login')
                        <p class="font-mono text-xs text-shinobi-red mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mot de passe -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-slate-300">
                            MOT DE PASSE
                        </label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-500 text-sm">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               placeholder="••••••••"
                               class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark/80 pl-10 pr-10 py-3 font-mono text-xs sm:text-sm text-white placeholder-slate-500 focus:border-shinobi-red focus:outline-none focus:ring-1 focus:ring-shinobi-red transition @error('password') border-shinobi-red text-red-200 @enderror">
                        <button type="button" 
                                onclick="togglePasswordVisibility('password', this)" 
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-white transition cursor-pointer"
                                title="Afficher/masquer le mot de passe">
                            <i class="bi bi-eye text-base"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="font-mono text-xs text-shinobi-red mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Se souvenir de moi -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" 
                               id="remember" 
                               name="remember" 
                               value="1" 
                               class="h-4 w-4 rounded border-shinobi-border bg-shinobi-dark text-shinobi-red focus:ring-0 focus:ring-offset-0">
                        <span class="font-mono text-xs text-slate-400">Se souvenir de moi</span>
                    </label>
                </div>

                <!-- Bouton Soumettre -->
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-shinobi-red px-6 py-3.5 font-mono text-sm font-bold text-white shadow-lg shadow-shinobi-red/30 hover:bg-shinobi-red-hover hover:scale-[1.01] active:scale-[0.99] transition">
                        <span>ACCÉDER À L'ARÈNE</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

                <!-- Basculer vers Inscription -->
                <div class="border-t border-shinobi-border/80 pt-4 text-center">
                    <span class="font-mono text-xs text-slate-400">Nouveau shinobi ?</span>
                    <a href="{{ route('register') }}" class="font-mono text-xs font-bold text-shinobi-red hover:underline ml-1">
                        Créer un compte ▸
                    </a>
                </div>

            </form>

        </div>

    </div>
</div>

@push('scripts')
<script>
    function togglePasswordVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye-warning', 'bi-eye');
        }
    }
</script>
@endpush
@endsection
