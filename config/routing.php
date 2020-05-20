<?php

use \SallePW\SlimApp\Controller\HomeController;
use \SallePW\SlimApp\Controller\RegisterController;
use \SallePW\SlimApp\Controller\SignInController;
use \SallePW\SlimApp\Controller\VisitsController;
use \SallePW\SlimApp\Controller\AuthController;
use \SallePW\SlimApp\Controller\ProfileController;
use \SallePW\SlimApp\Controller\SecurityController;
use \SallePW\SlimApp\Controller\BankAccountController;
use \SallePW\SlimApp\Controller\DashboardController;
use \SallePW\SlimApp\Controller\SendMoneyController;
use \SallePW\SlimApp\Controller\RequestMoneyController;
use \SallePW\SlimApp\Controller\TransactionsController;
use \SallePW\SlimApp\Middleware\StartSessionMiddleware;


$app->add(StartSessionMiddleware::class);

$app->get('/', HomeController::class . ':showLanding')->setName('home');

$app->get('/sign-up', RegisterController::class . ':showSignUp')->setName('sign-up');
$app->post('/sign-up', RegisterController::class . ':registerMe')->setName('sign-up');
$app->get('/sign-in', SignInController::class . ':showSignIn')->setName('sign-in');
$app->post('/sign-in', SignInController::class . ':login')->setName('sign-in');

$app->get('/sign-out', HomeController::class . ':signout')->setName('sign-out');

$app->get('/profile', ProfileController::class . ":showProfile")->setName('profile');
$app->post('/profile', ProfileController::class . ":upload")->setName('profile');

$app->get('/profile/security', SecurityController::class . ":showSecurity")->setName('security');
$app->post('/profile/security', SecurityController::class . ":reset")->setName('security');

$app->get('/account/summary', DashboardController::class . ":showDashboard")->setName('dashboard');

$app->get('/account/bank-account', BankAccountController::class . ":showBankAccount")->setName('bank-account');
$app->post('/account/bank-account', BankAccountController::class . ":addAccount")->setName('bank-account');
$app->post('/account/bank-account/load', BankAccountController::class . ":loadMoney")->setName('load');

$app->get('/account/money/send', SendMoneyController::class . ":showSendMoney")->setName('send-money');
$app->post('/account/money/send', SendMoneyController::class . ":sendMoney")->setName('send-money');

$app->get('/account/money/requests', RequestMoneyController::class . ":showRequestMoney")->setName('request-money');
$app->get('/account/money/requests/pending', RequestMoneyController::class . ":showPending")->setName('request-money');
$app->post('/account/money/requests', RequestMoneyController::class . ":requestMoney")->setName('request-money');
$app->get('/account/money/requests/{id}/accept', RequestMoneyController::class . ":accept")->setName('request-money');

$app->get('/account/transactions', TransactionsController::class . ":showTransactions")->setName('transactions');

$app->get(
    '/visits',
    VisitsController::class . ":showVisits"
)->setName('visits');

$app->get('/activate',
AuthController::class . ":showAuth", function ($request, $response, $args) {
    // Show book identified by $args['id']
})->setName('auth');;
