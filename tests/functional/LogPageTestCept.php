<?php
$I = new FunctionalTester($scenario);
$I->wantTo('Ensure the log page is displayed');

$I->loginAsAdmin();

$I->amOnPluginsPage();

$I->seePluginActivated('email-log');
