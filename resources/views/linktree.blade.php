@extends('layouts.app')

<style>
    #dropdownMenu
    {
        left: 50%;
        transform: translateX(-50%);
        position: absolute;
        top: 60px;
        right: 0;
        background: #E7C791;
        border-radius: 12px;
        padding: 16px;
        width: 90%;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        color: #6b3e00;
        z-index: 9999;
    }

    #closeMenu {
        background:none; 
        border:none; 
        font-weight:bold; 
        font-size:1.4rem; 
        color:#6b3e00; 
        cursor:pointer;
    }

    #menuButton
    {
        background:#fff; 
        border:none; 
        border-radius:50%; 
        width:48px; 
        height:48px; 
        cursor:pointer; 
        display:flex; 
        align-items:center; 
        justify-content:center; 
        box-shadow:0 2px 5px rgba(0,0,0,0.2); 
        position:relative; z-index:1000;
    }

    .social-icons {
        display: flex;
        gap: 12px;
        justify-content: center;
        align-items: center;
        padding: 20px 0;
    }

    .social-icons .icon {
        width: 50px;
        height: 50px;
    }

    .social-icons svg {
        width: 100%;
        height: 100%;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .social-icons svg:hover {
        opacity: 0.6;
        transform: scale(1.05);
    }

    .logo-container {
        display:flex; 
        justify-content:center; 
    }

    .logo-round {
        width: 150px;
        padding: 30px;
        border-radius: 50%;
        background: #2B2B2B;
        height: 150px;
        display: flex;
        justify-content: center;
    }

    
</style>

@section('content')
    <div class="p-4 map-page main-content">
        <x-dropdown-menu />

        <div class="station-selection-container" style="margin-top:20vh;">
            <div class="card card-parent mb-2 animate-entry delay-2 p-5">
                <div class="logo-container">
                    <div class="logo-round">
                        <x-branding />
                    </div>
                </div>
                <div class="buttons">
                    <div class="col-12 text-center bg-white my-2 px-3 py-3 rounded-2">
                        <a href="#" class="text-dark text-bold">Links</a>
                    </div>
                    <div class="col-12 text-center  bg-white my-2 px-3 py-3 rounded-2">
                        <a href="#" class="text-dark text-bold">Directory</a>
                    </div>
                </div>
                <div class="social-icons">
                    <!-- Instagram -->
                    <div class="icon">
                        <svg viewBox="0 0 50 50" fill="none" stroke="#000">
                            <circle cx="25" cy="25" r="22" stroke-width="2"/>
                            <rect x="17" y="17" width="16" height="16" rx="4" stroke-width="2"/>
                            <circle cx="25" cy="25" r="5" stroke-width="2"/>
                            <circle cx="31" cy="19" r="2" fill="#000"/>
                        </svg>
                    </div>

                    <!-- X -->
                    <div class="icon">
                        <svg viewBox="0 0 50 50" fill="none" stroke="#000">
                            <circle cx="25" cy="25" r="22" stroke-width="2"/>
                            <path d="M18 18L32 32M32 18L18 32" stroke-width="3" />
                        </svg>
                    </div>

                    <!-- Facebook -->
                    <div class="icon">
                        <svg viewBox="0 0 50 50" fill="none" stroke="#000">
                            <circle cx="25" cy="25" r="22" stroke-width="2"/>
                            <path d="M27 16h-3c-2 0-3 1-3 3v4h-3v4h3v10h4V27h3l1-4h-4v-3c0-1 1-2 2-2h2v-4z" fill="#000"/>
                        </svg>
                    </div>

                    <!-- TikTok -->
                    <div class="icon">
                        <svg viewBox="0 0 50 50" fill="none" stroke="#000">
                            <circle cx="25" cy="25" r="22" stroke-width="2"/>
                            <path d="M30 16c1 3 3 5 6 5v4c-3 0-5-1-6-2v8c0 4-3 7-7 7s-7-3-7-7 3-7 7-7c1 0 2 0 3 1v4c-1-1-2-1-3-1-2 0-3 2-3 3s1 3 3 3 3-1 3-3V16h4z" fill="#000"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            const menuButton = document.getElementById('menuButton');
            const dropdownMenu = document.getElementById('dropdownMenu');
            const closeMenu = document.getElementById('closeMenu');

            function toggleMenu() {
                const isHidden = dropdownMenu.hasAttribute('hidden');
                if (isHidden) {
                    dropdownMenu.removeAttribute('hidden');
                    menuButton.setAttribute('aria-expanded', 'true');
                } else {
                    dropdownMenu.setAttribute('hidden', '');
                    menuButton.setAttribute('aria-expanded', 'false');
                }
            }

            menuButton.addEventListener('click', toggleMenu);
            closeMenu.addEventListener('click', () => {
                dropdownMenu.setAttribute('hidden', '');
                menuButton.setAttribute('aria-expanded', 'false');
            });

            // Close menu when clicking outside
            document.addEventListener('click', (event) => {
                if (!dropdownMenu.contains(event.target) && !menuButton.contains(event.target)) {
                    dropdownMenu.setAttribute('hidden', '');
                    menuButton.setAttribute('aria-expanded', 'false');
                }
            });
        </script>
    @endpush
@endsection
