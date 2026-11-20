<?php

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'Administrator';

$adminInitial = strtoupper(
    substr(trim($adminName), 0, 1)
);

?>

<style>
/* Modern Profile Dropdown Styles */
.profile-dropdown-wrapper .navbar-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 6px 10px !important;
    border-radius: 99px;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    background: transparent;
    border: none;
    text-align: left;
}

.profile-dropdown-wrapper .navbar-profile:hover {
    background-color: rgba(0, 0, 0, 0.04) !important;
}

/* Floating Card Effect */
.modern-profile-menu {
    min-width: 190px;
    padding: 8px;
    margin-top: 14px !important;
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 14px;
    box-shadow: 0 16px 36px -8px rgba(15, 23, 42, 0.12),
                0 4px 12px -2px rgba(15, 23, 42, 0.04);
    animation: profilePopIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    transform-origin: top right;
}

@keyframes profilePopIn {
    0% {
        opacity: 0;
        transform: translateY(-8px) scale(0.96);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Logout Item */
.modern-profile-menu .logout-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    font-size: 0.92rem;
    font-weight: 600;
    color: #475569;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
}

/* Icon Container */
.modern-profile-menu .icon-bubble {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 0.85rem;
    transition: all 0.2s ease;
}

/* Hover Accent */
.modern-profile-menu .logout-link:hover {
    background-color: #fef2f2;
    color: #ef4444;
}

.modern-profile-menu .logout-link:hover .icon-bubble {
    background-color: #fee2e2;
    color: #ef4444;
    transform: translateX(2px);
}
</style>

<nav class="top-navbar">

    <div class="navbar-left">

        <button
            type="button"
            class="mobile-menu-btn"
            id="mobileMenuBtn"
            aria-label="Open menu"
        >
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="navbar-brand">
           <span class="darjibhai-logo">Darji<span>bhai</span></span>
        </div>

    </div>

    <!-- Profile Dropdown Container -->
    <div class="dropdown profile-dropdown-wrapper">

        <button
            type="button"
            class="navbar-profile btn p-0 border-0 bg-transparent text-start shadow-none"
            id="profileDropdown"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >
            <div class="navbar-profile-info">
                <strong>
                    <?= htmlspecialchars($adminName) ?>
                </strong>
                <span>
                    <?= htmlspecialchars($adminRole) ?>
                </span>
            </div>

            <div class="navbar-avatar">
                <?= htmlspecialchars($adminInitial) ?>
            </div>
        </button>

        <div class="dropdown-menu dropdown-menu-end modern-profile-menu" aria-labelledby="profileDropdown">
            <a class="dropdown-item logout-link" href="/darjibhai/admin/logout.php">
                <span class="icon-bubble">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </span>
                <span>Log Out</span>
            </a>
        </div>

    </div>

</nav>