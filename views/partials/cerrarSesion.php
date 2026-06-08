<?php

if (!defined('STAYFIT_LOGOUT_CSS')) {
    define('STAYFIT_LOGOUT_CSS', true);
}

$logoutUrl = $logoutUrl ?? '../../controllers/auth/logouthController.php';

?>
<a class="logout-link" href="<?= htmlspecialchars($logoutUrl, ENT_QUOTES, 'UTF-8') ?>">Cerrar sesión</a>
