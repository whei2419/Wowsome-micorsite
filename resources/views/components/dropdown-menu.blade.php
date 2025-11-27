<!-- Menu Button -->
            <div class="btn-container text-end pt-5">
                <button id="menuButton" aria-expanded="false" aria-controls="dropdownMenu" aria-label="Toggle Menu">
                    <!-- Hamburger Icon -->
                    <svg width="24" height="24" fill="#333" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                        <rect y="4" width="24" height="2" rx="1"></rect>
                        <rect y="11" width="24" height="2" rx="1"></rect>
                        <rect y="18" width="24" height="2" rx="1"></rect>
                    </svg>
                </button>
            </div>

            <!-- Dropdown Menu -->
            <div id="dropdownMenu" role="menu" aria-labelledby="menuButton" hidden>
                <!-- Menu Header with Close Button -->
                <div class="menu" style="display:flex; justify-content:space-between; align-items:center; padding-bottom:10px;">
                    <span style="font-weight:600; font-size:1.1rem;" class="text-disabled">Menu</span>
                    <button id="closeMenu" aria-label="Close Menu">×</button>
                </div>

                <!-- Menu Items -->
                <a href="{{ route('dashboard'); }}" class="menu mt-2 {{ request()->routeIs('dashboard') ? 'active-menu' : '' }}" role="menuitem" style="display:block; padding:12px; text-decoration:none; color:#5a3300; margin-bottom:8px; background:#e7c791cc; ">Rewards <span class="{{ request()->routeIs('dashboard') ? 'd-none' : '' }}"style="float:right;">→</span></a></a>

                <a href="{{ route('directory'); }}" class="menu mt-2 {{ request()->routeIs('directory') ? 'active-menu' : '' }}" role="menuitem" style="display:block; padding:12px; text-decoration:none; color:#5a3300; margin-bottom:8px; background:#e7c791cc; ">Directory <span class="{{ request()->routeIs('directory') ? 'd-none' : '' }}" style="float:right;">→</span></a>

                <a href="{{ route('linktree'); }}" class="menu mt-2 {{ request()->routeIs('linktree') ? 'active-menu' : '' }}" role="menuitem" style="display:block; padding:12px; text-decoration:none; color:#5a3300; margin-bottom:8px; background:#e7c791cc;">Linktree <span class="{{ request()->routeIs('linktree') ? 'd-none' : '' }}" style="float:right;">→</span></a>

                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" role="menuitem"class="menu" style="display:block; width:100%; padding:12px; border:none; text-decoration:none; color:#5a3300; background:#e7c791cc;  font-family: 'Montserrat', sans-serif; font-weight: 700; text-align:left; cursor:pointer;">Logout</button>
                </form>
            </div>