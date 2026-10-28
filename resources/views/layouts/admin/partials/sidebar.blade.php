<aside class="sidebar sidebar-default sidebar-white sidebar-base navs-rounded-all">
    <div class="sidebar-header d-flex align-items-center justify-content-start">
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand">
            <div class="logo-main">
                <div class="logo-normal">
                    <svg class="text-primary icon-30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="-0.757324" y="19.2427" width="28" height="4" rx="2" transform="rotate(-45 -0.757324 19.2427)" fill="currentColor"/>
                        <rect x="7.72803" y="27.728" width="28" height="4" rx="2" transform="rotate(-45 7.72803 27.728)" fill="currentColor"/>
                        <rect x="10.5366" y="16.3945" width="16" height="4" rx="2" transform="rotate(45 10.5366 16.3945)" fill="currentColor"/>
                        <rect x="10.5562" y="-0.556152" width="28" height="4" rx="2" transform="rotate(45 10.5562 -0.556152)" fill="currentColor"/>
                    </svg>
                </div>
            </div>
            <h4 class="logo-title ms-2">Otaku Admin</h4>
        </a>
        <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
            <i class="icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </i>
        </div>
    </div>

    <div class="sidebar-body pt-0 data-scrollbar">
        <div class="sidebar-list">
            <ul class="navbar-nav iq-main-menu" id="sidebar-menu">
                <li class="nav-item static-item">
                    <a class="nav-link static-item disabled" href="#" tabindex="-1">
                        <span class="default-icon">COMMANDEMENT</span>
                        <span class="mini-icon">-</span>
                    </a>
                </li>
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="icon">
                            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.4" d="M16.0756 2H19.4616C20.8639 2 22.0001 3.14585 22.0001 4.55996V7.97452C22.0001 9.38864 20.8639 10.5345 19.4616 10.5345H16.0756C14.6734 10.5345 13.5371 9.38864 13.5371 7.97452V4.55996C13.5371 3.14585 14.6734 2 16.0756 2Z" fill="currentColor"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2ZM4.53852 13.4655H7.92449C9.32676 13.4655 10.463 14.6114 10.463 16.0255V19.44C10.463 20.8532 9.32676 22 7.92449 22H4.53852C3.13626 22 2 20.8532 2 19.44V16.0255C2 14.6114 3.13626 13.4655 4.53852 13.4655ZM19.4615 13.4655H16.0755C14.6732 13.4655 13.537 14.6114 13.537 16.0255V19.44C13.537 20.8532 14.6732 22 16.0755 22H19.4615C20.8637 22 22 20.8532 22 19.44V16.0255C22 14.6114 20.8637 13.4655 19.4615 13.4655Z" fill="currentColor"></path>
                            </svg>
                        </i>
                        <span class="item-name">Tableau de Bord</span>
                    </a>
                </li>

                <!-- Tournois -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.tournaments.*') ? 'active' : '' }}" href="{{ route('admin.tournaments.index') }}">
                        <i class="icon">
                            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12C2 17.523 6.477 22 12 22C17.523 22 22 17.523 22 12C22 6.477 17.523 2 12 2ZM12 4V12L17.657 17.657C19.102 16.212 20 14.212 20 12C20 7.589 16.411 4 12 4Z" fill="currentColor" opacity="0.4"/>
                                <path d="M12 4C7.589 4 4 7.589 4 12C4 16.411 7.589 20 12 20C13.847 20 15.549 19.373 16.91 18.324L12 13.414V4Z" fill="currentColor"/>
                            </svg>
                        </i>
                        <span class="item-name">Gestion Tournois</span>
                    </a>
                </li>

                <!-- Créer Tournoi -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.tournaments.create') ? 'active' : '' }}" href="{{ route('admin.tournaments.create') }}">
                        <i class="icon">
                            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 4V20M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </i>
                        <span class="item-name">Nouveau Tournoi</span>
                    </a>
                </li>

                <!-- Joueurs & Shinobis -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                        <i class="icon">
                            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.4" d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="currentColor"></path>
                                <path d="M12 14.5C6.99 14.5 2.73 17.86 2 22.28C2.54 22.74 3.14 23.14 3.79 23.47C5.16 22.25 8.44 21.5 12 21.5C15.56 21.5 18.84 22.25 20.21 23.47C20.86 23.14 21.46 22.74 22 22.28C21.27 17.86 17.01 14.5 12 14.5Z" fill="currentColor"></path>
                            </svg>
                        </i>
                        <span class="item-name">Joueurs & Shinobis</span>
                    </a>
                </li>

                <!-- Validation Pass Shinobi -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.pass-requests.*') ? 'active' : '' }}" href="{{ route('admin.pass-requests.index') }}">
                        <i class="icon text-warning">
                            <i class="bi bi-shield-check fs-5"></i>
                        </i>
                        <span class="item-name">Demandes de Pass</span>
                        @php
                            $pendingPassCount = \App\Models\PassRequest::where('status', 'pending')->count();
                        @endphp
                        @if($pendingPassCount > 0)
                            <span class="badge bg-warning text-dark ms-auto font-mono" style="font-size: 10px;">{{ $pendingPassCount }}</span>
                        @endif
                    </a>
                </li>

                <!-- Portraits Shinobi (Magicien d'Oz) -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.portraits.*') ? 'active' : '' }}" href="{{ route('admin.portraits.index') }}">
                        <i class="icon text-danger">
                            <i class="bi bi-magic fs-5"></i>
                        </i>
                        <span class="item-name">Portraits & Cartes</span>
                    </a>
                </li>

                <!-- Profil Admin -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}" href="{{ route('admin.profile.edit') }}">
                        <i class="icon">
                            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 15C15.866 15 19 11.866 19 8C19 4.13401 15.866 1 12 1C8.13401 1 5 4.13401 5 8C5 11.866 8.13401 15 12 15Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2.90991 22.25C3.77991 18.64 7.57991 16 11.9999 16C16.4199 16 20.2199 18.64 21.0899 22.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </i>
                        <span class="item-name">Profil Arbitre</span>
                    </a>
                </li>

                <li class="nav-item static-item mt-3">
                    <a class="nav-link static-item disabled" href="#" tabindex="-1">
                        <span class="default-icon">PASSERELLE</span>
                        <span class="mini-icon">-</span>
                    </a>
                </li>

                <!-- Retour Vers le jeu / l'arène -->
                <li class="nav-item">
                    <a class="nav-link text-danger" href="{{ route('dashboard') }}">
                        <i class="icon text-danger">
                            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 11L12 14L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M21 12V19C21 20.1 20.1 21 19 21H5C3.9 21 3 20.1 3 19V5C3 3.9 3.9 3 5 3H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </i>
                        <span class="item-name fw-bold">Retour Arène Shinobi</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="sidebar-footer"></div>
</aside>
