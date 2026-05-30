<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/includes/bootstrap.php';
keyportal_logout();
header('Location: /admin/login.php', true, 302);
