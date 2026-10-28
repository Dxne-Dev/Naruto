@extends('layouts.app')

@section('content')
<div class="relative min-h-[calc(100vh-4rem)] flex items-center justify-center p-4 sm:p-6 overflow-hidden">
    
    <!-- Image naruto-hero en arrière-plan complet -->
    <img src="{{ asset('images/naruto-hero.png') }}" 
         alt="Naruto Examen Chunin" 
         class="absolute inset-0 w-full h-full object-cover object-center pointer-events-none select-none">

    <!-- Voile sombre immersif pour garantir un contraste et une lisibilité parfaits du formulaire -->
    <div class="absolute inset-0 bg-shinobi-dark/75 backdrop-blur-[2px] pointer-events-none"></div>

    <!-- Carte d'Enrôlement Otaku Arena -->
    <div class="relative z-10 w-full max-w-md my-auto">
        <div class="glass3d overflow-hidden rounded-3xl border border-shinobi-border/60 p-5 sm:p-6">

            <!-- En-tête formulaire -->
            <div class="relative text-center mb-5 space-y-1.5">
                <div class="inline-flex items-center gap-1.5 rounded-full border border-shinobi-red/30 bg-shinobi-red/10 px-3 py-1 font-mono text-[10px] font-bold tracking-wider text-shinobi-red uppercase">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-shinobi-red animate-pulse"></span>
                    REGISTRE DE L'ACADÉMIE NINJA
                </div>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold uppercase tracking-tight text-white leading-tight">Inscription</h2>
                <p class="font-mono text-xs text-slate-400">
                    Crée ton profil et donne naissance à ton shinobi pour entrer dans l’arène et affronter les meilleurs.
                </p>
            </div>

            <!-- Formulaire -->
            <form method="POST" action="{{ route('register.post') }}" class="relative space-y-4">
                @csrf

                <!-- Pseudonyme unique -->
                <div class="space-y-1.5">
                    <label for="username" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-slate-300">
                        PSEUDONYME UNIQUE *
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-500 text-sm">
                            <i class="bi bi-person-badge-fill"></i>
                        </span>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="{{ old('username') }}" 
                               required 
                               autofocus 
                               placeholder="Ex: ShadowNinja"
                               class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark/80 pl-10 pr-3.5 py-3 font-mono text-xs sm:text-sm text-white placeholder-slate-500 focus:border-shinobi-red focus:outline-none focus:ring-1 focus:ring-shinobi-red transition @error('username') border-shinobi-red text-red-200 @enderror">
                    </div>
                    <span class="block font-mono text-[10px] text-slate-500">Lettres, chiffres, tirets et underscores autorisés.</span>
                    @error('username')
                        <p class="font-mono text-xs text-shinobi-red mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Adresse Email -->
                <div class="space-y-1.5">
                    <label for="email" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-slate-300">
                        ADRESSE EMAIL *
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-500 text-sm">
                            <i class="bi bi-envelope-fill"></i>
                        </span>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               placeholder="shinobi@domaine.com"
                               class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark/80 pl-10 pr-3.5 py-3 font-mono text-xs sm:text-sm text-white placeholder-slate-500 focus:border-shinobi-red focus:outline-none focus:ring-1 focus:ring-shinobi-red transition @error('email') border-shinobi-red text-red-200 @enderror">
                    </div>
                    @error('email')
                        <p class="font-mono text-xs text-shinobi-red mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mot de passe -->
                <div class="space-y-1.5">
                    <label for="password" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-slate-300">
                        MOT DE PASSE *
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-500 text-sm">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               placeholder="6 car. min."
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

                <!-- Confirmation Mot de passe -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-slate-300">
                        CONFIRMATION DU MOT DE PASSE *
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-500 text-sm">
                            <i class="bi bi-shield-lock-fill"></i>
                        </span>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required 
                               placeholder="••••••••"
                               class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark/80 pl-10 pr-10 py-3 font-mono text-xs sm:text-sm text-white placeholder-slate-500 focus:border-shinobi-red focus:outline-none focus:ring-1 focus:ring-shinobi-red transition">
                        <button type="button" 
                                onclick="togglePasswordVisibility('password_confirmation', this)" 
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-white transition cursor-pointer"
                                title="Afficher/masquer la confirmation">
                            <i class="bi bi-eye text-base"></i>
                        </button>
                    </div>
                </div>

                <!-- Bouton Soumettre -->
                <div class="pt-3">
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-shinobi-red px-6 py-3.5 font-mono text-sm font-bold text-white shadow-lg shadow-shinobi-red/30 hover:bg-shinobi-red-hover hover:scale-[1.01] active:scale-[0.99] transition">
                        <span>CRÉER MON COMPTE</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

                <!-- Basculer vers Connexion -->
                <div class="border-t border-shinobi-border/80 pt-4 text-center">
                    <span class="font-mono text-xs text-slate-400">Déjà enregistré ?</span>
                    <a href="{{ route('login') }}" class="font-mono text-xs font-bold text-shinobi-red hover:underline ml-1">
                        Se connecter ▸
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
            icon.classList.add('bi-eye');
        }
    }
</script>
@endpush
@endsection
