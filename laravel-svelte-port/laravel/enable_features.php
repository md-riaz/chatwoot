<?php
$account = App\Models\Account::find(1);
$account->enableFeature('custom_attributes');
$account->enableFeature('automations');
$account->enableFeature('audit_logs');
$account->enableFeature('sla');
$account->enableFeature('custom_roles');
$account->enableFeature('saml');
echo "Features enabled successfully.\n";
