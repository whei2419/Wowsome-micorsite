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
            directory page
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
