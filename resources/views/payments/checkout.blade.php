@extends('layouts.app')

@section('content')
<div class="relative min-h-[calc(100vh-4rem)] bg-shinobi-dark text-slate-100 overflow-hidden pb-24 md:pb-16 selection:bg-shinobi-red selection:text-white">

    <!-- Atmospheric Background Aura & Glows -->
    <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden">
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[700px] sm:w-[950px] h-[450px] bg-gradient-to-b from-shinobi-red/25 via-shinobi-red/10 to-transparent blur-3xl opacity-75"></div>
        <div class="absolute top-48 -right-36 w-[450px] h-[450px] bg-shinobi-gold/10 blur-[130px] rounded-full"></div>
        <div class="absolute bottom-12 -left-36 w-[500px] h-[500px] bg-shinobi-chakra/10 blur-[140px] rounded-full"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#2D2933_1px,transparent_1px)] [background-size:24px_24px] opacity-25"></div>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10">

        <!-- HERO HEADER: Dark Shōnen Pacte Shinobi Banner -->
        <div class="relative rounded-2xl sm:rounded-3xl border border-shinobi-red/40 bg-gradient-to-b from-shinobi-card/95 via-shinobi-card/85 to-[#0F0D12]/95 backdrop-blur-xl p-6 sm:p-10 mb-8 sm:mb-12 shadow-[0_20px_50px_rgba(229,9,20,0.12)] overflow-hidden">
            <!-- Background Artwork silhouette / Blood Moon accent -->
            <div class="absolute right-0 top-0 bottom-0 w-full sm:w-1/2 pointer-events-none opacity-20 sm:opacity-30 mix-blend-screen bg-no-repeat bg-right-top bg-contain" style="background-image: url('{{ asset('images/hero-bg.jpg') }}');"></div>
            <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-shinobi-red/30 blur-2xl pointer-events-none"></div>

            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                <div class="lg:col-span-8 text-center sm:text-left">
                    <!-- Eyebrow Badge -->
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-shinobi-red/15 border border-shinobi-red/40 text-shinobi-red text-[11px] sm:text-xs font-mono font-bold tracking-widest uppercase mb-3 sm:mb-4">
                        <span class="inline-block w-2 h-2 rounded-full bg-shinobi-red animate-ping"></span>
                        PACTE DU SHINOBI • ACCÈS DÉFINITIF
                    </div>

                    <!-- Main Title -->
                    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-serif font-black tracking-tight text-white uppercase leading-tight mb-3">
                        DÉBLOQUE LE <span class="text-transparent bg-clip-text bg-gradient-to-r from-shinobi-red via-rose-500 to-amber-400">PASS SHINOBI ILLIMITÉ</span>
                    </h1>

                    <p class="text-slate-300 text-sm sm:text-base font-sans max-w-2xl leading-relaxed">
                        Un paiement unique à vie. Zéro abonnement récurrent. Révèle instantanément le portrait officiel de ton ninja et participe sans restriction aux examens Chūnin.
                    </p>
                </div>

                <!-- Right Quote Callout / Kanji Aura -->
                <div class="lg:col-span-4 hidden lg:flex flex-col items-end justify-center text-right border-l border-white/10 pl-6">
                    <div class="text-xs font-mono text-shinobi-gold/90 uppercase tracking-widest mb-1 flex items-center gap-1.5">
                        <i class="bi bi-quote text-base"></i> SCELLER LE DESTIN
                    </div>
                    <blockquote class="text-xs text-slate-300 italic font-serif leading-relaxed max-w-[240px]">
                        « Ce n'est pas un simple accès... C'est le plein potentiel de ton shinobi qui s'éveille. »
                    </blockquote>
                    <div class="mt-2 text-3xl font-serif text-shinobi-red/40 select-none tracking-widest">
                        無限 • 覚醒
                    </div>
                </div>
            </div>
        </div>

        @if(session('info'))
            <div class="mb-6 p-4 rounded-xl bg-shinobi-chakra/10 border border-shinobi-chakra/40 text-shinobi-chakra text-xs sm:text-sm font-mono flex items-center gap-3">
                <i class="bi bi-info-circle-fill text-lg shrink-0"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-shinobi-red/10 border border-shinobi-red/40 text-rose-300 text-xs sm:text-sm font-mono flex items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill text-lg shrink-0 text-shinobi-red"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- MAIN 2-COLUMN GRID (Mobile first: Payment action order-1, VIP Card perks order-2) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

            <!-- VIP Shinobi Pass Card (2nd on mobile, Left column on desktop) -->
            <div class="order-2 lg:order-1 lg:col-span-5 relative group">
                <!-- Glowing Pass Container -->
                <div class="relative rounded-2xl sm:rounded-3xl bg-gradient-to-b from-[#1C1722] via-shinobi-card to-[#120F16] border-2 border-shinobi-gold/40 p-6 sm:p-7 shadow-[0_15px_40px_rgba(244,202,48,0.12)] overflow-hidden">
                    
                    <!-- Decorative Golden Corner Ribbons -->
                    <div class="absolute -top-10 -right-10 w-24 h-24 bg-gradient-to-br from-shinobi-gold to-amber-600 rotate-45 opacity-20"></div>
                    <div class="absolute top-0 right-0 p-3 pointer-events-none">
                        <i class="bi bi-shield-fill-check text-shinobi-gold/60 text-xl"></i>
                    </div>

                    <!-- Top Badge -->
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-shinobi-gold/15 border border-shinobi-gold/40 text-shinobi-gold text-[10px] sm:text-xs font-mono font-bold tracking-wider uppercase">
                            <i class="bi bi-infinity text-sm"></i> ACCÈS PERMANENT À VIE
                        </span>
                        <span class="font-mono text-[10px] text-slate-400 uppercase tracking-widest">ÉDITION JOUEUR</span>
                    </div>

                    <!-- Pass Title -->
                    <h2 class="text-xl sm:text-2xl font-serif font-black text-white uppercase tracking-tight flex items-center gap-2 mb-2">
                        <span>Pass Shinobi Illimité</span>
                    </h2>

                    <!-- Split Art Showcase: Sealed vs Awakened Avatar -->
                    <div class="relative my-4 rounded-xl overflow-hidden border border-shinobi-border bg-shinobi-dark aspect-[16/9] shadow-inner">
                        <img src="{{ asset('images/locked-avatar.png') }}" alt="Shinobi Pass Avatar" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-shinobi-dark via-shinobi-dark/30 to-transparent"></div>
                        
                        <!-- Status Overlays -->
                        <div class="absolute bottom-2 left-2.5 right-2.5 flex items-center justify-between text-[10px] font-mono">
                            <span class="px-2 py-0.5 rounded bg-black/80 text-slate-300 border border-white/10 flex items-center gap-1">
                                <i class="bi bi-lock-fill text-rose-400"></i> Statut : Actuellement Verrouillé
                            </span>
                            <span class="px-2 py-0.5 rounded bg-shinobi-gold/20 text-shinobi-gold border border-shinobi-gold/40 font-bold">
                                覚醒 ÉVEIL IMMÉDIAT
                            </span>
                        </div>
                    </div>

                    <!-- Price Block -->
                    <div class="p-4 rounded-xl bg-shinobi-dark/80 border border-shinobi-gold/30 mb-5">
                        <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider block">Tarif unique d'activation</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl sm:text-4xl font-mono font-black text-transparent bg-clip-text bg-gradient-to-r from-shinobi-gold via-amber-300 to-yellow-500">
                                700 FCFA
                            </span>
                            <span class="text-xs font-mono text-slate-400">/ ~1,07 €</span>
                        </div>
                    </div>

                    <!-- Feature Checklist -->
                    <div class="space-y-3 mb-6">
                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-shinobi-gold/20 border border-shinobi-gold/50 flex items-center justify-center text-shinobi-gold shrink-0 text-xs mt-0.5">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <span class="text-xs sm:text-sm text-slate-200">
                                <strong class="text-white font-semibold">Portrait IA Officiel</strong> immédiatement généré et débloqué sur ton profil et tes fiches de combat.
                            </span>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-shinobi-gold/20 border border-shinobi-gold/50 flex items-center justify-center text-shinobi-gold shrink-0 text-xs mt-0.5">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <span class="text-xs sm:text-sm text-slate-200">
                                <strong class="text-white font-semibold">Inscriptions illimitées à TOUS les tournois</strong> et examens Chūnin (levée totale des quotas par appareil).
                            </span>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-shinobi-gold/20 border border-shinobi-gold/50 flex items-center justify-center text-shinobi-gold shrink-0 text-xs mt-0.5">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <span class="text-xs sm:text-sm text-slate-200">
                                <strong class="text-shinobi-gold font-bold">3 Jetons de Destin offerts</strong> crédités immédiatement pour altérer clan, affinité ou jutsu rare.
                            </span>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-shinobi-gold/20 border border-shinobi-gold/50 flex items-center justify-center text-shinobi-gold shrink-0 text-xs mt-0.5">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <span class="text-xs sm:text-sm text-slate-200">
                                <strong class="text-white font-semibold">Badge Honorifique & Cadre Doré</strong> visibles dans l'arène, au Panthéon et dans le classement.
                            </span>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-shinobi-gold/20 border border-shinobi-gold/50 flex items-center justify-center text-shinobi-gold shrink-0 text-xs mt-0.5">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <span class="text-xs sm:text-sm text-slate-200">
                                <strong class="text-white font-semibold">Activation définitive et instantanée</strong> attachée pour toujours à ton compte joueur.
                            </span>
                        </div>
                    </div>

                    <!-- Bottom Kanji Seal -->
                    <div class="pt-4 border-t border-white/10 flex items-center justify-between text-[11px] font-mono text-slate-400">
                        <span class="flex items-center gap-1.5 text-shinobi-gold">
                            <i class="bi bi-patch-check-fill"></i> Sceau Otaku Arena
                        </span>
                        <span>UN PAIEMENT • PRIVILÈGES PERMANENTS</span>
                    </div>
                </div>
            </div>

            <!-- Payment Flow (1st on mobile for instant action, Right column on desktop) -->
            <div class="order-1 lg:order-2 lg:col-span-7 space-y-6">

                <!-- Main Activation Box -->
                <div class="rounded-2xl sm:rounded-3xl bg-shinobi-card/95 border-2 border-shinobi-red/50 p-6 sm:p-8 shadow-[0_15px_45px_rgba(229,9,20,0.18)] relative overflow-hidden">
                    <div class="absolute -right-20 -bottom-20 w-48 h-48 rounded-full bg-shinobi-red/15 blur-3xl pointer-events-none"></div>

                    <!-- Header -->
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-shinobi-red/20 border border-shinobi-red text-rose-300 text-[11px] font-mono font-bold uppercase tracking-wider">
                            <i class="bi bi-lightning-charge-fill text-shinobi-red"></i> ACTIVATION INSTANTANÉE
                        </span>
                        <span class="font-mono text-xs text-slate-400">TRANSACTION SÉCURISÉE</span>
                    </div>

                    <h3 class="text-xl sm:text-2xl font-serif font-black text-white uppercase mb-2">
                        Valider mon Pass Shinobi
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-300 mb-6 leading-relaxed">
                        Accède au guichet sécurisé pour régler les 700 FCFA. Dès confirmation de ton paiement, ton personnage est éveillé et tes bonus sont crédités sans délai.
                    </p>

                    <!-- Trust Checklist -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 p-3 rounded-xl bg-shinobi-dark/60 border border-shinobi-border mb-6 text-[11px] font-mono text-slate-300">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-shield-lock-fill text-emerald-400 text-sm"></i>
                            <span>100% Chiffré & Sécurisé</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-lightning-fill text-shinobi-gold text-sm"></i>
                            <span>Éveil en temps réel</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-check-circle-fill text-sky-400 text-sm"></i>
                            <span>Zéro frais cachés</span>
                        </div>
                    </div>

                    <!-- ALERTE PHOTO MANQUANTE SI NON TÉLÉVERSÉE -->
                    @if($user->fighter && empty($user->fighter->source_photo_path))
                        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 mb-6 space-y-3">
                            <div class="flex items-center gap-2 text-amber-300 font-mono text-xs font-bold uppercase">
                                <i class="bi bi-camera-fill text-base"></i>
                                <span>Photo de ton Shinobi requise pour le portrait HD</span>
                            </div>
                            <p class="font-sans text-xs text-slate-300 leading-relaxed">
                                Tu n'avais pas fourni de photo lors de ton inscription. Téléverse ta photo dès maintenant pour que l'administration forge ton portrait officiel après validation.
                            </p>
                            <form method="POST" action="{{ route('payment.upload-photo') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 pt-1">
                                @csrf
                                <input type="file" name="source_photo_file" accept="image/*" required class="w-full text-xs font-mono text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-mono file:font-bold file:bg-white/10 file:text-white hover:file:bg-white/20">
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-mono text-xs font-black uppercase tracking-wider shrink-0 transition">
                                    Enregistrer la photo
                                </button>
                            </form>
                        </div>
                    @elseif($user->fighter && !empty($user->fighter->source_photo_path))
                        <div class="p-3.5 rounded-2xl bg-emerald-950/30 border border-emerald-500/30 mb-6 flex items-center justify-between gap-3 text-xs font-mono text-emerald-300">
                            <div class="flex items-center gap-2.5">
                                <i class="bi bi-check-circle-fill text-emerald-400 text-base"></i>
                                <span>Photo enregistrée pour la gravure HD</span>
                            </div>
                            <button type="button" @click="$refs.changePhotoForm.classList.toggle('hidden')" class="text-[11px] text-slate-400 hover:text-white underline">
                                Changer
                            </button>
                        </div>
                        <form x-ref="changePhotoForm" method="POST" action="{{ route('payment.upload-photo') }}" enctype="multipart/form-data" class="hidden p-4 rounded-2xl bg-shinobi-dark border border-shinobi-border mb-6 space-y-2">
                            @csrf
                            <span class="block font-mono text-xs text-slate-300">Nouvelle photo pour la forge du portrait :</span>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                <input type="file" name="source_photo_file" accept="image/*" required class="w-full text-xs font-mono text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-mono file:font-bold file:bg-white/10 file:text-white">
                                <button type="submit" class="px-4 py-2 rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white font-mono text-xs font-bold uppercase shrink-0 transition">
                                    Mettre à jour
                                </button>
                            </div>
                        </form>
                    @endif

                    <!-- MAIN CTA BUTTON: Redirect to Payment Window -->
                    <a href="{{ $chariowUrl }}" target="_blank" rel="noopener noreferrer" class="group relative w-full flex items-center justify-center gap-3 py-4 sm:py-5 px-6 rounded-xl font-mono font-black text-sm sm:text-base text-white uppercase tracking-wider bg-gradient-to-r from-shinobi-red via-red-600 to-rose-700 hover:from-shinobi-red-hover hover:to-rose-800 shadow-[0_10px_30px_rgba(229,9,20,0.45)] hover:shadow-[0_15px_40px_rgba(229,9,20,0.65)] hover:-translate-y-0.5 transition-all duration-200 text-decoration-none">
                        <i class="bi bi-fire text-lg group-hover:scale-110 transition-transform"></i>
                        <span>RÉGLER 700 FCFA & ÉVEILLER MON SHINOBI</span>
                        <i class="bi bi-arrow-right text-lg group-hover:translate-x-1 transition-transform"></i>
                    </a>

                    <p class="text-center text-[11px] font-mono text-slate-400 mt-3 flex items-center justify-center gap-1.5">
                        <i class="bi bi-box-arrow-up-right text-shinobi-gold"></i>
                        <span>Ouvre la fenêtre de règlement sécurisée dans un nouvel onglet.</span>
                    </p>
                </div>

                <!-- ACCORDION: Manual verification request fallback -->
                <div x-data="{ open: {{ (isset($latestRequest) && $latestRequest->status === 'pending') ? 'true' : 'false' }} }" class="rounded-2xl bg-shinobi-card/80 border border-shinobi-border overflow-hidden">
                    <button @click="open = !open" type="button" class="w-full p-4 sm:p-5 flex items-center justify-between text-left hover:bg-white/5 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-shinobi-border/60 flex items-center justify-center text-slate-300">
                                <i class="bi bi-clock-history text-sm"></i>
                            </div>
                            <div>
                                <span class="text-xs sm:text-sm font-mono font-bold text-slate-200 block">
                                    Déjà effectué le règlement ?
                                </span>
                                <span class="text-[11px] font-mono text-slate-400">
                                    Transmets les détails de ta transaction aux arbitres
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(isset($latestRequest) && $latestRequest->status === 'pending')
                                <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 font-mono text-[10px] font-bold">
                                    EN COURS D'EXAMEN
                                </span>
                            @endif
                            <i class="bi bi-chevron-down text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                        </div>
                    </button>

                    <div x-show="open" x-collapse x-cloak class="p-5 sm:p-6 border-t border-shinobi-border bg-shinobi-dark/50">
                        @if(isset($latestRequest) && $latestRequest->status === 'pending')
                            <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 font-mono text-xs space-y-2">
                                <div class="flex items-center gap-2 font-bold text-sm text-amber-200">
                                    <i class="bi bi-hourglass-split"></i>
                                    <span>Demande de validation en cours de traitement</span>
                                </div>
                                <p class="text-slate-300 text-[11px] leading-relaxed">
                                    Ta demande soumise le {{ $latestRequest->created_at->format('d/m/Y à H:i') }} (Mode : {{ $latestRequest->method_label }}, Coordonnées : {{ $latestRequest->phone_or_card }}) est actuellement examinée par l'administration.
                                </p>
                                <p class="text-[10px] text-slate-400">
                                    Dès que l'arbitre confirme ton paiement, ton pass sera éveillé et une fenêtre t'avertira automatiquement.
                                </p>
                            </div>
                        @else
                            <form method="POST" action="{{ route('payment.process') }}" id="checkout-form" enctype="multipart/form-data" class="space-y-4">
                                @csrf

                                <div>
                                    <label class="block text-xs font-mono font-semibold text-slate-300 uppercase tracking-wider mb-2">
                                        Moyen utilisé pour le débit
                                    </label>
                                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="payment_method" value="wave" checked class="peer sr-only">
                                            <div class="py-2.5 px-2 rounded-xl border border-shinobi-border bg-shinobi-card text-center peer-checked:border-shinobi-red peer-checked:bg-shinobi-red/10 peer-checked:text-white transition-all text-xs font-mono">
                                                Wave
                                            </div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="payment_method" value="orange_money" class="peer sr-only">
                                            <div class="py-2.5 px-2 rounded-xl border border-shinobi-border bg-shinobi-card text-center peer-checked:border-shinobi-red peer-checked:bg-shinobi-red/10 peer-checked:text-white transition-all text-xs font-mono">
                                                Orange
                                            </div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="payment_method" value="mtn_money" class="peer sr-only">
                                            <div class="py-2.5 px-2 rounded-xl border border-shinobi-border bg-shinobi-card text-center peer-checked:border-shinobi-red peer-checked:bg-shinobi-red/10 peer-checked:text-white transition-all text-xs font-mono">
                                                MTN
                                            </div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="payment_method" value="moov_money" class="peer sr-only">
                                            <div class="py-2.5 px-2 rounded-xl border border-shinobi-border bg-shinobi-card text-center peer-checked:border-shinobi-red peer-checked:bg-shinobi-red/10 peer-checked:text-white transition-all text-xs font-mono">
                                                Moov
                                            </div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="payment_method" value="card" class="peer sr-only">
                                            <div class="py-2.5 px-2 rounded-xl border border-shinobi-border bg-shinobi-card text-center peer-checked:border-shinobi-red peer-checked:bg-shinobi-red/10 peer-checked:text-white transition-all text-xs font-mono">
                                                Carte
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Numéro téléphone ou carte -->
                                <div>
                                    <label for="phone_or_card" class="block text-xs font-mono font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                        Numéro de téléphone ou de carte débité *
                                    </label>
                                    <input type="text" name="phone_or_card" id="phone_or_card" required class="w-full px-4 py-2.5 rounded-xl bg-shinobi-dark border border-shinobi-border focus:border-shinobi-red focus:ring-1 focus:ring-shinobi-red text-slate-100 text-sm font-mono placeholder-slate-500" placeholder="+225 07 12 34 56 78 ou 4242...">
                                    <span class="text-[10px] font-mono text-slate-400 mt-1 block">Numéro exact ayant débité les 700 FCFA.</span>
                                </div>

                                <!-- ID transaction -->
                                <div>
                                    <label for="transaction_reference" class="block text-xs font-mono font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                        ID ou Référence de transaction <span class="text-slate-500 font-normal lowercase">(si disponible)</span>
                                    </label>
                                    <input type="text" name="transaction_reference" id="transaction_reference" class="w-full px-4 py-2.5 rounded-xl bg-shinobi-dark border border-shinobi-border focus:border-shinobi-red focus:ring-1 focus:ring-shinobi-red text-slate-100 text-sm font-mono placeholder-slate-500" placeholder="Ex: TXN_123456, Référence SMS opérateur...">
                                    <span class="text-[10px] font-mono text-slate-400 mt-1 block">Accélère la vérification par l'équipe d'arbitrage.</span>
                                </div>

                                <!-- Photo optionnelle si non encore transmise -->
                                @if($user->fighter && empty($user->fighter->source_photo_path))
                                    <div>
                                        <label for="source_photo_file_process" class="block text-xs font-mono font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                            Photo de ton combattant <span class="text-shinobi-gold font-normal lowercase">(pour forge du portrait HD)</span>
                                        </label>
                                        <input type="file" name="source_photo_file" id="source_photo_file_process" accept="image/*" class="w-full text-xs font-mono text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-mono file:font-bold file:bg-white/10 file:text-white hover:file:bg-white/20">
                                    </div>
                                @endif

                                <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-mono font-bold text-xs uppercase tracking-wider bg-white/10 hover:bg-white/20 text-white border border-white/20 transition-all flex items-center justify-center gap-2">
                                    <i class="bi bi-send-fill text-xs"></i>
                                    <span>FAIRE UNE DEMANDE DE VALIDATION</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Footer Trust Badges -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                    <div class="p-3 rounded-xl bg-shinobi-card/60 border border-shinobi-border flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-shinobi-red/10 border border-shinobi-red/30 flex items-center justify-center text-shinobi-red shrink-0">
                            <i class="bi bi-shield-shaded text-sm"></i>
                        </div>
                        <div>
                            <span class="text-xs font-mono font-bold text-slate-200 block">100% Sécurisé</span>
                            <span class="text-[10px] text-slate-400">Guichet officiel</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-shinobi-card/60 border border-shinobi-border flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-shinobi-gold/10 border border-shinobi-gold/30 flex items-center justify-center text-shinobi-gold shrink-0">
                            <i class="bi bi-phone text-sm"></i>
                        </div>
                        <div>
                            <span class="text-xs font-mono font-bold text-slate-200 block">Accès Immédiat</span>
                            <span class="text-[10px] text-slate-400">Éveil automatique</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-shinobi-card/60 border border-shinobi-border flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-shinobi-chakra/10 border border-shinobi-chakra/30 flex items-center justify-center text-shinobi-chakra shrink-0">
                            <i class="bi bi-headset text-sm"></i>
                        </div>
                        <div>
                            <span class="text-xs font-mono font-bold text-slate-200 block">Support Arène</span>
                            <span class="text-[10px] text-slate-400">Équipe réactive</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
