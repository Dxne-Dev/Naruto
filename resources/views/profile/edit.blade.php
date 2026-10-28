@extends('layouts.app')

@section('title', 'Paramètres du Compte — Otaku Arena')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 pb-12">
    
    <!-- En-tête de la Page -->
    <div class="space-y-1 border-b border-shinobi-border/60 pb-4">
        <span class="font-mono text-[10px] font-black uppercase tracking-wider text-shinobi-red">
            REGISTRE PERSONNEL DU SHINOBI
        </span>
        <h1 class="font-serif text-3xl sm:text-4xl font-black uppercase text-white tracking-wide">
            Paramètres du Compte
        </h1>
        <p class="font-mono text-xs text-slate-400">
            Gère tes informations d'accès et la sécurité de ton compte joueur.
        </p>
    </div>

    <!-- Carte Principale des Paramètres -->
    <div class="rounded-3xl border border-shinobi-border/80 bg-shinobi-card p-6 sm:p-8 shadow-2xl space-y-6 relative overflow-hidden">
        
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Email (Verrouillé) -->
            <div class="space-y-1.5 font-mono">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    ADRESSE EMAIL DU JOUEUR (VERROUILLÉE)
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-500 text-sm">
                        <i class="bi bi-envelope-fill"></i>
                    </span>
                    <input type="email" 
                           value="{{ $user->email }}" 
                           disabled 
                           readonly 
                           class="w-full rounded-xl border border-shinobi-border/60 bg-black/40 pl-10 pr-3.5 py-2.5 text-xs text-slate-400 cursor-not-allowed select-none">
                </div>
                <span class="text-[10px] text-slate-500 block">L'adresse email est liée au compte et ne peut être altérée.</span>
            </div>

            <!-- Nom Complet -->
            <div class="space-y-1.5 font-mono">
                <label for="name" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                    NOM COMPLET DU JOUEUR *
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $user->name) }}" 
                       required
                       class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-shinobi-red focus:outline-none focus:ring-1 focus:ring-shinobi-red transition">
                @error('name')
                    <span class="text-shinobi-red text-[10px] block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Pseudonyme -->
            <div class="space-y-1.5 font-mono">
                <label for="username" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                    PSEUDONYME DU PILOTE *
                </label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="{{ old('username', $user->username) }}" 
                       required
                       class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-shinobi-red focus:outline-none focus:ring-1 focus:ring-shinobi-red transition">
                @error('username')
                    <span class="text-shinobi-red text-[10px] block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Séparateur Stylisé -->
            <div class="border-t border-shinobi-border/60 pt-5 space-y-4">
                <div class="flex items-center gap-2">
                    <i class="bi bi-shield-lock-fill text-shinobi-gold"></i>
                    <h3 class="font-serif font-black uppercase text-base text-white tracking-wide">
                        Modifier le Mot de Passe
                    </h3>
                </div>

                <!-- Mot de passe actuel -->
                <div class="space-y-1.5 font-mono">
                    <label for="current_password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        MOT DE PASSE ACTUEL
                    </label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           placeholder="••••••••"
                           class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-shinobi-red focus:outline-none focus:ring-1 focus:ring-shinobi-red transition">
                    @error('current_password')
                        <span class="text-shinobi-red text-[10px] block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Nouveau mot de passe & Confirmation -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 font-mono">
                    <div class="space-y-1.5">
                        <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                            NOUVEAU MOT DE PASSE
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="••••••••"
                               class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-shinobi-red focus:outline-none focus:ring-1 focus:ring-shinobi-red transition">
                        @error('password')
                            <span class="text-shinobi-red text-[10px] block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                            CONFIRMATION
                        </label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               placeholder="••••••••"
                               class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-shinobi-red focus:outline-none focus:ring-1 focus:ring-shinobi-red transition">
                    </div>
                </div>
            </div>

            <!-- Bouton Enregistrer -->
            <div class="pt-4 border-t border-shinobi-border/60 flex justify-end">
                <button type="submit" 
                        class="rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white px-6 py-2.5 font-mono text-xs font-bold shadow-lg shadow-shinobi-red/30 transition flex items-center gap-2 cursor-pointer">
                    <span>Enregistrer les modifications</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </form>

    </div>

    <!-- Zone de Danger : Suppression du Compte -->
    <div class="rounded-3xl border border-red-900/60 bg-red-950/20 p-6 sm:p-8 shadow-xl space-y-4">
        <div class="flex items-center gap-2 text-shinobi-red font-mono text-xs font-bold uppercase tracking-wider">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Zone de Danger • Action Irréversible</span>
        </div>
        
        <h3 class="font-serif font-black uppercase text-xl text-white">
            Supprimer Définitivement mon Compte
        </h3>
        
        <p class="font-mono text-xs text-slate-400 leading-relaxed">
            Cette action effacera définitivement ton combattant, ton inventaire et toute ta progression dans l'arène.
            <br>
            <span class="text-amber-400 font-bold">Note :</span> La suppression du compte ne réinitialise ni le rate limit IP, ni le quota des 2 participations gratuites si la micro-transaction n'a pas été débloquée.
        </p>

        <form method="POST" action="{{ route('profile.destroy') }}" class="pt-3 font-mono text-xs space-y-3">
            @csrf
            @method('DELETE')

            <label for="delete_confirmation_password" class="block font-bold uppercase tracking-wider text-slate-300">
                Confirme ton mot de passe pour supprimer *
            </label>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <input type="password" 
                       id="delete_confirmation_password" 
                       name="delete_confirmation_password" 
                       placeholder="Entre ton mot de passe actuel..." 
                       required
                       class="flex-grow rounded-xl border border-shinobi-border bg-shinobi-dark px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-shinobi-red focus:outline-none focus:ring-1 focus:ring-shinobi-red transition">
                
                <button type="submit" 
                        onclick="return confirm('Es-tu absolument certain de vouloir supprimer définitivement ton compte ninja ? Cette action est irréversible.')"
                        class="rounded-xl border border-red-600/70 bg-red-900/40 hover:bg-red-800 text-red-200 px-5 py-2.5 font-bold transition cursor-pointer shrink-0">
                    Supprimer le compte
                </button>
            </div>
            @error('delete_confirmation_password')
                <span class="text-shinobi-red text-[10px] block">{{ $message }}</span>
            @enderror
        </form>
    </div>

</div>
@endsection
