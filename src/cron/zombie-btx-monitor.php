<?php
// chdir(realpath(__DIR__));
require_once realpath(__DIR__).'/../bootstrap/boot.php';
use controller\CronController;
use controller\SessionController;

ul('Zombie BTX monitor called');
SessionController::init($admin);
CronController::zombieBTXJob();
