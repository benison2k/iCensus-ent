<?php
// app/views/components/sidebar.php

// Ensure variables are set
$base_url = $base_url ?? '/iCensus-ent/public';
$isAdmin = $isAdmin ?? false;
$isEncoder = $isEncoder ?? false;

// Get User Details
$currentUser = $_SESSION['user'] ?? ['full_name' => 'Guest User', 'role_name' => 'Visitor'];
$userInitial = strtoupper(substr($currentUser['full_name'], 0, 1));

// Determine Dashboard Link
$homeLink = $isAdmin ? $base_url . '/sysadmin/dashboard' : ($isEncoder ? $base_url . '/encoder-dashboard' : $base_url . '/dashboard');

// Get current URI for active state highlighting
$uri = $_SERVER['REQUEST_URI'] ?? '';
?>

<div id="sidebarOverlay" class="sidebar-overlay"></div>

<aside id="appSidebar" class="sidebar-menu">
    
    <div class="sidebar-profile-card">
        <div class="profile-avatar">
            <?= $userInitial ?>
        </div>
        
        <div class="profile-info">
            <div class="profile-name" title="<?= htmlspecialchars($currentUser['full_name']) ?>">
                <?= htmlspecialchars($currentUser['full_name']) ?>
            </div>
            <div class="profile-role">
                <?= htmlspecialchars($currentUser['role_name']) ?>
            </div>
        </div>

        <button id="closeSidebarBtn" class="close-btn" title="Close Menu">
            <span class="material-icons">chevron_left</span>
        </button>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="<?= $homeLink ?>" class="<?= strpos($uri, 'dashboard') !== false ? 'active' : '' ?>">
                    <span class="material-icons">dashboard</span>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li>
                <a href="<?= $base_url ?>/residents" class="<?= strpos($uri, 'residents') !== false ? 'active' : '' ?>">
                    <span class="material-icons">groups</span>
                    <span>Residents</span>
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>/analytics" class="<?= strpos($uri, 'analytics') !== false ? 'active' : '' ?>">
                    <span class="material-icons">analytics</span>
                    <span>Analytics</span>
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>/settings" class="<?= strpos($uri, 'settings') !== false ? 'active' : '' ?>">
                    <span class="material-icons">settings</span>
                    <span>Settings</span>
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>/about" class="<?= strpos($uri, 'about') !== false ? 'active' : '' ?>">
                    <span class="material-icons">info</span>
                    <span>About</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="#" onclick="document.getElementById('logoutBtn').click(); return false;" class="logout-link">
            <span class="material-icons">power_settings_new</span>
            <span>Sign Out</span>
        </a>
    </div>
</aside>