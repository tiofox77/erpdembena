<!-- Sidebar -->
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <!-- ... existing code ... -->

                <!-- Maintenance Section -->
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseMaintenanceSettings" aria-expanded="false" aria-controls="collapseMaintenanceSettings">
                    <div class="sb-nav-link-icon"><i class="fas fa-cogs"></i></div>
                    Maintenance Settings
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseMaintenanceSettings" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="{{ route('maintenance.failure-modes') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            Failure Modes
                        </a>
                        <a class="nav-link" href="{{ route('maintenance.failure-mode-categories') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                            Failure Mode Categories
                        </a>
                        <a class="nav-link" href="{{ route('maintenance.failure-causes') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-question-circle"></i></div>
                            Failure Causes
                        </a>
                        <a class="nav-link" href="{{ route('maintenance.failure-cause-categories') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-sitemap"></i></div>
                            Failure Cause Categories
                        </a>
                    </nav>
                </div>

                <!-- ... existing code ... -->
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            {{ auth()->user()->name }}
        </div>
    </nav>
</div>
