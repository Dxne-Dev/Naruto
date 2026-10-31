<!DOCTYPE html>
<html lang="fr" class="h-full bg-shinobi-dark text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Otaku Arena — L\'Arène Shinobi des Tournois de l\'Examen Chūnin' }}</title>

    <!-- Favicon Officiel Otaku Arena -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/otaku-arena-favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/otaku-arena-favicon.png') }}">

    <!-- Google Fonts Inter, Newsreader & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,600;1,6..72,400&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Alpine.js Core for reactive UI components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Tailwind CSS v4 Build -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="min-h-full flex flex-col bg-shinobi-dark text-slate-200 font-sans antialiased selection:bg-shinobi-red selection:text-white">

    <!-- Top Navigation Bar (Fidèle à la maquette Otaku Arena) -->
    <!-- Visible sur tous les écrans (desktop & mobile) -->
    <header class="sticky top-0 z-50 bg-shinobi-dark/95 backdrop-blur border-b border-shinobi-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo Otaku Arena (Toujours visible avec son texte) -->
                <div class="flex items-center gap-3">
                    <a href="{{ auth()->check() ? route('fighters.my-character') : route('dashboard') }}" class="flex items-center gap-2.5 group">
                        <div class="relative w-9 h-9 sm:w-10 sm:h-10 rounded-xl overflow-hidden border border-shinobi-red/60 shadow-lg shadow-shinobi-red/25 group-hover:scale-105 transition-transform bg-shinobi-card flex items-center justify-center shrink-0">
                            <img src="{{ asset('images/otaku-arena-logo-transparent.png') }}" 
                                 alt="Otaku Arena Logo" 
                                 class="w-full h-full object-contain p-0.5">
                        </div>
                        <div>
                            <span class="font-mono font-black text-lg sm:text-xl tracking-wider text-white flex items-center gap-1">
                                OTAKU <span class="text-shinobi-red">ARENA</span>
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Center Nav Links Desktop : Maquette Panneau 1 & Espace Joueur -->
                <nav class="hidden md:flex items-center gap-1 font-mono text-xs uppercase tracking-wider">
                    @guest
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'text-white bg-white/5 font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            Accueil
                        </a>
                        <a href="{{ route('tournaments.open-list') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('tournaments.*') ? 'text-white bg-white/5 font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            Tournois
                        </a>
                        <a href="{{ route('leaderboard.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('leaderboard.*') ? 'text-white bg-white/5 font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            Classement
                        </a>
                    @else
                        <!-- Pour les connectés : Remplacement d'Accueil par "Mon Personnage" -->
                        <a href="{{ route('fighters.my-character') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('fighters.*') ? 'text-white bg-white/5 font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            Mon Personnage
                        </a>
                        <a href="{{ route('tournaments.open-list') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('tournaments.*') ? 'text-white bg-white/5 font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            Tournois
                        </a>
                        <a href="{{ route('strategies.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('strategies.*') ? 'text-white bg-white/5 font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            Stratégies
                        </a>
                        <a href="{{ route('battles.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('battles.*') ? 'text-white bg-white/5 font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            Combats
                        </a>
                        <a href="{{ route('friends.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('friends.*') ? 'text-white bg-white/5 font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            Amis
                        </a>
                        <a href="{{ route('leaderboard.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('leaderboard.*') ? 'text-white bg-white/5 font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            Classement
                        </a>
                    @endguest
                </nav>

                <!-- Right Action Buttons / User Actions -->
                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                        @php
                            $userFighter = auth()->user()->fighter;
                            $userHasPaid = (bool) (auth()->user()->has_paid_microtransaction);
                        @endphp

                        <!-- Wheel Tokens Pill (Visible sur mobile et desktop) -->
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-shinobi-card border border-shinobi-border text-xs font-mono">
                            <i class="bi bi-disc-fill text-shinobi-gold text-xs"></i>
                            <strong class="text-shinobi-gold font-bold">{{ auth()->user()->wheel_tokens ?? 0 }}</strong>
                            <span class="hidden sm:inline text-slate-400 text-[10px]">jetons</span>
                        </div>

                        <!-- User Profile Dropdown Menu (Alpine.js - Optimisé Mobile & Desktop) -->
                        <div class="relative" x-data="{ openProfileMenu: false }">
                            <button type="button" 
                                    @click="openProfileMenu = !openProfileMenu" 
                                    @click.outside="openProfileMenu = false"
                                    class="flex items-center gap-1.5 sm:gap-2 p-1 sm:pl-1.5 sm:pr-2.5 rounded-full bg-shinobi-card border border-shinobi-border hover:border-shinobi-red/50 transition cursor-pointer select-none">
                                <div class="w-7 h-7 rounded-full bg-black border border-shinobi-red overflow-hidden shrink-0 flex items-center justify-center">
                                    @if($userHasPaid && $userFighter)
                                        <img src="{{ $userFighter->portrait_url ?? ($userFighter->source_photo_url ?? 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=100&auto=format&fit=crop&q=80') }}" 
                                             alt="{{ $userFighter->name }}" 
                                             class="w-full h-full object-cover">
                                    @else
                                        <img src="{{ asset('images/locked-avatar.png') }}" 
                                             alt="Verrouillé" 
                                             class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <span class="hidden sm:inline font-mono text-xs font-semibold text-white">
                                    {{ $userFighter?->name ?? auth()->user()->username }}
                                </span>
                                <i class="bi bi-chevron-down text-[10px] text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openProfileMenu }"></i>
                            </button>

                            <!-- Dropdown Menu Positionné Proprement pour Mobile -->
                            <div x-show="openProfileMenu" 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 style="display: none;"
                                 class="fixed right-3 sm:absolute sm:right-0 mt-2 w-56 rounded-2xl bg-[#17151A]/95 backdrop-blur-xl border border-shinobi-border/90 p-2 shadow-2xl z-50 font-mono text-xs space-y-1">
                                
                                <div class="px-3 py-2 border-b border-shinobi-border/60">
                                    <span class="text-[10px] text-slate-400 block uppercase tracking-wider">Compte Joueur</span>
                                    <span class="font-bold text-white truncate block">{{ auth()->user()->username }}</span>
                                    <span class="text-[10px] text-slate-500 truncate block">{{ auth()->user()->email }}</span>
                                </div>

                                <!-- 1. Salons Privés Entre Amis -->
                                <a href="{{ route('tournaments.private.index') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-200 hover:text-white hover:bg-white/10 transition">
                                    <i class="bi bi-people-fill text-shinobi-gold"></i>
                                    <span>Salons Privés (Amis)</span>
                                </a>

                                <!-- 2. Paramètres du Compte -->
                                <a href="{{ route('profile.edit') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-200 hover:text-white hover:bg-white/10 transition">
                                    <i class="bi bi-gear-fill text-slate-400"></i>
                                    <span>Paramètres du compte</span>
                                </a>

                                <!-- 3. Déconnexion -->
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-red-400 hover:text-white hover:bg-shinobi-red/20 transition cursor-pointer text-left">
                                        <i class="bi bi-box-arrow-right"></i>
                                        <span>Déconnexion</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Maquette Panneau 1 : Boutons "Se connecter" et "S'inscrire" (affichés uniquement en desktop md+, masqués sur mobile) -->
                        <div class="hidden md:flex items-center gap-2">
                            <a href="{{ route('login') }}" class="font-mono text-xs text-slate-300 hover:text-white uppercase tracking-wider px-3 py-2 transition">
                                Se connecter
                            </a>
                            <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white text-xs font-mono font-bold uppercase tracking-wider transition shadow-lg shadow-shinobi-red/20">
                                S'inscrire
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow">
        <!-- Flash Notifications (Auto-dismiss après 5 secondes avec fondu) -->
        @if(session('success'))
            <div x-data="{ show: true }" 
                 x-init="setTimeout(() => show = false, 5000)" 
                 x-show="show" 
                 x-transition:leave="transition ease-in duration-500" 
                 x-transition:leave-start="opacity-100 transform translate-y-0" 
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3.5 rounded-xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 text-xs font-mono flex items-center justify-between shadow-xl shadow-emerald-950/40">
                    <div class="flex items-center gap-2.5">
                        <i class="bi bi-check-circle-fill text-emerald-400 text-sm"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-emerald-400 hover:text-white transition ml-3 cursor-pointer text-base leading-none">&times;</button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" 
                 x-init="setTimeout(() => show = false, 6000)" 
                 x-show="show" 
                 x-transition:leave="transition ease-in duration-500" 
                 x-transition:leave-start="opacity-100 transform translate-y-0" 
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3.5 rounded-xl bg-red-950/80 border border-red-500/50 text-red-200 text-xs font-mono flex items-center justify-between shadow-xl shadow-red-950/40">
                    <div class="flex items-center gap-2.5">
                        <i class="bi bi-exclamation-triangle-fill text-red-400 text-sm"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-red-400 hover:text-white transition ml-3 cursor-pointer text-base leading-none">&times;</button>
                </div>
            </div>
        @endif

        @if(request()->routeIs('dashboard', 'login', 'register', 'errors.*') || isset($isErrorPage) && $isErrorPage)
            @yield('content')
        @else
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                @yield('content')
            </div>
        @endif
    </main>

    <!-- ========================================================================= -->
    <!-- DOCK FLOTTANT MOBILE GAMING (Inspi Floating Pill Dock Dark Shōnen)       -->
    <!-- Visible UNIQUEMENT une fois connecté (après inscription/connexion)       -->
    <!-- ========================================================================= -->
    @auth
    <div class="md:hidden fixed bottom-4 inset-x-0 z-50 flex justify-center px-4 pointer-events-none">
        <nav class="pointer-events-auto w-full max-w-sm rounded-full bg-[#141217]/90 backdrop-blur-xl border border-shinobi-border/80 px-2 py-1.5 shadow-[0_10px_35px_rgba(0,0,0,0.8)] ring-1 ring-white/10">
            <div class="flex items-center justify-between">
                
                <!-- 1. Mon Ninja -->
                @php $isNinja = request()->routeIs('fighters.*'); @endphp
                <a href="{{ route('fighters.my-character') }}" 
                   class="relative flex flex-col items-center justify-center py-1.5 px-3 rounded-full transition-all duration-300 {{ $isNinja ? 'text-white bg-shinobi-red shadow-lg shadow-shinobi-red/40' : 'text-slate-400 hover:text-white' }}">
                    <i class="bi bi-person-badge text-base leading-none"></i>
                    <span class="font-mono text-[9px] font-bold mt-0.5">Ninja</span>
                    @if($isNinja)
                        <span class="absolute -bottom-1 w-1 h-1 rounded-full bg-white"></span>
                    @endif
                </a>

                <!-- 2. Tournois -->
                @php $isTourn = request()->routeIs('tournaments.*'); @endphp
                <a href="{{ route('tournaments.open-list') }}" 
                   class="relative flex flex-col items-center justify-center py-1.5 px-3 rounded-full transition-all duration-300 {{ $isTourn ? 'text-shinobi-dark bg-shinobi-gold shadow-lg shadow-shinobi-gold/40' : 'text-slate-400 hover:text-white' }}">
                    <i class="bi bi-trophy text-base leading-none"></i>
                    <span class="font-mono text-[9px] font-bold mt-0.5">Tournois</span>
                    @if($isTourn)
                        <span class="absolute -bottom-1 w-1 h-1 rounded-full bg-shinobi-dark"></span>
                    @endif
                </a>

                <!-- 3. Stratégies -->
                @php $isStrat = request()->routeIs('strategies.*'); @endphp
                <a href="{{ route('strategies.index') }}" 
                   class="relative flex flex-col items-center justify-center py-1.5 px-3 rounded-full transition-all duration-300 {{ $isStrat ? 'text-white bg-white/20 shadow-lg border border-white/30' : 'text-slate-400 hover:text-white' }}">
                    <i class="bi bi-sliders text-base leading-none"></i>
                    <span class="font-mono text-[9px] font-bold mt-0.5">Tactique</span>
                    @if($isStrat)
                        <span class="absolute -bottom-1 w-1 h-1 rounded-full bg-shinobi-gold"></span>
                    @endif
                </a>

                <!-- 4. Combats -->
                @php $isBattle = request()->routeIs('battles.*'); @endphp
                <a href="{{ route('battles.index') }}" 
                   class="relative flex flex-col items-center justify-center py-1.5 px-3 rounded-full transition-all duration-300 {{ $isBattle ? 'text-shinobi-dark bg-shinobi-chakra shadow-lg shadow-shinobi-chakra/40 font-bold' : 'text-slate-400 hover:text-white' }}">
                    <i class="bi bi-lightning-charge text-base leading-none"></i>
                    <span class="font-mono text-[9px] font-bold mt-0.5">Combats</span>
                    @if($isBattle)
                        <span class="absolute -bottom-1 w-1 h-1 rounded-full bg-shinobi-dark"></span>
                    @endif
                </a>

                <!-- 5. Classement -->
                @php $isLead = request()->routeIs('leaderboard.*'); @endphp
                <a href="{{ route('leaderboard.index') }}" 
                   class="relative flex flex-col items-center justify-center py-1.5 px-3 rounded-full transition-all duration-300 {{ $isLead ? 'text-white bg-shinobi-red shadow-lg shadow-shinobi-red/40' : 'text-slate-400 hover:text-white' }}">
                    <i class="bi bi-bar-chart-line text-base leading-none"></i>
                    <span class="font-mono text-[9px] font-bold mt-0.5">Rang</span>
                    @if($isLead)
                        <span class="absolute -bottom-1 w-1 h-1 rounded-full bg-white"></span>
                    @endif
                </a>

            </div>
        </nav>
    </div>
    @endauth

    <!-- Pass Request Decision Notification Modal (Alpine.js) -->
    @auth
        @php
            $unreadPassDecision = \App\Models\PassRequest::where('user_id', auth()->id())
                ->whereIn('status', ['approved', 'rejected'])
                ->where('user_notified', false)
                ->latest()
                ->first();
        @endphp

        @if($unreadPassDecision)
            <div x-data="{
                show: true,
                requestId: {{ $unreadPassDecision->id }},
                status: '{{ $unreadPassDecision->status }}',
                closeModal() {
                    this.show = false;
                    fetch('{{ route('payment.ack-notification', $unreadPassDecision) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });
                }
            }"
            x-show="show"
            x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md transition-all duration-300">
                <div @click.outside="closeModal()" class="relative w-full max-w-lg rounded-3xl p-6 sm:p-8 text-center shadow-2xl overflow-hidden {{ $unreadPassDecision->status === 'approved' ? 'bg-gradient-to-b from-[#201B14] via-shinobi-card to-[#120F16] border-2 border-shinobi-gold' : 'bg-gradient-to-b from-[#251014] via-shinobi-card to-[#120D10] border-2 border-shinobi-red' }}">
                    
                    @if($unreadPassDecision->status === 'approved')
                        <!-- Glow effect -->
                        <div class="pointer-events-none absolute -top-20 left-1/2 -translate-x-1/2 w-72 h-72 rounded-full bg-shinobi-gold/20 blur-3xl"></div>

                        <!-- Top Icon Crown/Trophy -->
                        <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-shinobi-gold/30 to-amber-500/10 border border-shinobi-gold/60 flex items-center justify-center text-shinobi-gold text-3xl shadow-lg shadow-shinobi-gold/30">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>

                        <span class="inline-block px-3 py-1 rounded-full bg-shinobi-gold/20 border border-shinobi-gold/40 text-shinobi-gold text-xs font-mono font-bold tracking-widest uppercase mb-2">
                            PACTE SHINOBI SCELLÉ & VALIDÉ
                        </span>

                        <h3 class="text-2xl sm:text-3xl font-serif font-black text-white uppercase tracking-tight mb-3">
                            Ton Pass Shinobi est Éveillé !
                        </h3>

                        <p class="text-slate-200 text-xs sm:text-sm font-sans leading-relaxed mb-6">
                            L'équipe d'arbitrage a vérifié ton règlement de <strong class="text-shinobi-gold font-bold">700 FCFA</strong> (Réf : {{ $unreadPassDecision->transaction_reference ?: $unreadPassDecision->phone_or_card }}). Ton accès illimité à vie est actif et la forge de ton portrait officiel HD est en cours !
                        </p>

                        <div class="p-3.5 rounded-xl bg-shinobi-dark/80 border border-shinobi-gold/30 mb-6 text-left font-mono text-xs text-slate-300 space-y-1.5">
                            <div class="flex items-center gap-2 text-shinobi-gold">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Inscriptions illimitées à vie aux tournois</span>
                            </div>
                            <div class="flex items-center gap-2 text-shinobi-gold">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Portrait officiel débloqué en haute résolution</span>
                            </div>
                            <div class="flex items-center gap-2 text-shinobi-gold">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>3 Jetons de Destin crédités pour la Boutique</span>
                            </div>
                        </div>

                        <button @click="closeModal()" type="button" class="w-full py-4 px-6 rounded-xl font-mono font-black text-sm uppercase tracking-wider text-slate-950 bg-gradient-to-r from-shinobi-gold via-amber-300 to-yellow-500 hover:brightness-110 shadow-lg shadow-shinobi-gold/30 transition-all">
                            DÉCOUVRIR MES PRIVILÈGES ▸
                        </button>
                    @else
                        <!-- Glow effect -->
                        <div class="pointer-events-none absolute -top-20 left-1/2 -translate-x-1/2 w-72 h-72 rounded-full bg-shinobi-red/20 blur-3xl"></div>

                        <!-- Top Icon Alert -->
                        <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-shinobi-red/30 to-rose-500/10 border border-shinobi-red/60 flex items-center justify-center text-shinobi-red text-3xl shadow-lg shadow-shinobi-red/30">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                        </div>

                        <span class="inline-block px-3 py-1 rounded-full bg-shinobi-red/20 border border-shinobi-red/40 text-rose-300 text-xs font-mono font-bold tracking-widest uppercase mb-2">
                            DÉCISION D'ARBITRAGE
                        </span>

                        <h3 class="text-2xl sm:text-3xl font-serif font-black text-white uppercase tracking-tight mb-3">
                            Demande Non Validée
                        </h3>

                        <p class="text-slate-300 text-xs sm:text-sm font-sans leading-relaxed mb-4">
                            L'administration n'a pas pu confirmer ta transaction de 700 FCFA avec les informations transmises pour le numéro <code class="text-white bg-white/10 px-1.5 py-0.5 rounded">{{ $unreadPassDecision->phone_or_card }}</code>.
                        </p>

                        <!-- Motif de rejet fourni par l'arbitre -->
                        @if($unreadPassDecision->admin_rejection_reason)
                            <div class="p-4 rounded-xl bg-shinobi-red/15 border border-shinobi-red/40 text-left mb-6">
                                <span class="block text-[10px] font-mono font-bold text-rose-300 uppercase tracking-widest mb-1">
                                    Motif indiqué par l'arbitre :
                                </span>
                                <p class="text-xs font-mono text-slate-100 leading-relaxed italic">
                                    « {{ $unreadPassDecision->admin_rejection_reason }} »
                                </p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <a href="{{ route('payment.checkout') }}" @click="closeModal()" class="py-3 px-4 rounded-xl font-mono font-bold text-xs uppercase tracking-wider text-white bg-shinobi-red hover:bg-shinobi-red-hover transition text-decoration-none flex items-center justify-center gap-1.5">
                                <i class="bi bi-arrow-repeat"></i>
                                <span>Refaire un Paiement</span>
                            </a>
                            <button @click="closeModal()" type="button" class="py-3 px-4 rounded-xl font-mono font-bold text-xs uppercase tracking-wider text-slate-300 bg-white/5 hover:bg-white/10 border border-white/10 transition">
                                Fermer
                            </button>
                        </div>
                    @endif

                </div>
            </div>
        @endif
    @endauth

    @stack('scripts')
</body>
</html>
