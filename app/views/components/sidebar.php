<?php
// app/views/components/sidebar.php

// Ensure variables are set (inherits from header.php)
$base_url = $base_url ?? '/iCensus-ent/public';
$isAdmin = $isAdmin ?? false;
$isEncoder = $isEncoder ?? false;

// Determine Dashboard Link (same logic as header)
$homeLink = $isAdmin ? $base_url . '/sysadmin/dashboard' : ($isEncoder ? $base_url . '/encoder-dashboard' : $base_url . '/dashboard');
?>

<div id="sidebarOverlay" class="sidebar-overlay"></div>

<aside id="appSidebar" class="sidebar-menu">
    <div class="sidebar-header">
        <img src="<?= $base_url ?>/assets/img/iCensusLogo.png" alt="iCensus" class="sidebar-logo">
        <button id="closeSidebarBtn" class="close-btn">
            <span class="material-icons">close</span>
        </button>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="<?= $homeLink ?>">
                    <span class="material-icons">dashboard</span>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li>
                <a href="<?= $base_url ?>/residents">
                    <span class="material-icons">groups</span>
                    <span>Residents</span>
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>/analytics">
                    <span class="material-icons">analytics</span>
                    <span>Analytics</span>
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>/settings">
                    <span class="material-icons">settings</span>
                    <span>Settings</span>
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>/about">
                    <span class="material-icons">info</span>
                    <span>About</span>
                </a>
            </li>
            
            <li class="sidebar-logout">
                <a href="#" onclick="document.getElementById('logoutBtn').click(); return false;">
                    <span class="material-icons">logout</span>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>