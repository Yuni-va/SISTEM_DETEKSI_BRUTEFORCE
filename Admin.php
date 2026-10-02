<?php

require_once __DIR__."/../helpers/Security.php";

$loginPath = defined("AUTH_LOGIN_PATH") ? AUTH_LOGIN_PATH : "login.php";

redirect_if_not_admin($loginPath);
