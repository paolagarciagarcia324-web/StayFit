<?php

if (!defined('STAYFIT_LOGOUT_CSS')) {
    define('STAYFIT_LOGOUT_CSS', true);
?>
<style>
    .sidebar a.logout-link {
        margin-top: 16px;
        background: #3EB489;
        font-weight: 700;
    }

    .sidebar a.logout-link:hover {
        background: #2f9b74;
    }
</style>
<?php
}

$logoutUrl = $logoutUrl ?? '../../controller/auth/logouthController.php';

?>
<a class="logout-link" href="<?= htmlspecialchars($logoutUrl, ENT_QUOTES, 'UTF-8') ?>">Cerrar sesión</a>
