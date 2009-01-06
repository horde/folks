<?php
/**
 * Report offensive content
 *
 * $Horde: folks/report.php,v 1.5 2008-08-03 18:32:29 mrubinsk Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Folks
 */

require_once dirname(__FILE__) . '/lib/base.php';
require_once 'Horde/Variables.php';

if (!Auth::isAuthenticated()) {
    Horde::authenticationFailureRedirect();
}

$user = Util::getFormData('user');
if (empty($user)) {
    $notification->push(_("User is not selected"), 'horde.warning');
    header('Location: ' . Folks::getUrlFor('list', 'list'));
    exit;
}

$title = _("Do you really want to report this user?");

$vars = Variables::getDefaultVariables();
$form = new Horde_Form($vars, $title);
$form->setButtons(array(_("Report"), _("Cancel")));

$enum = array('advertisement' => _("Advertisement content"),
              'terms' => _("Terms and conditions infringement"),
              'offensive' => _("Offensive content"),
              'copyright' => _("Copyright infringement"));

$form->addVariable($user, 'name', 'description', false);

$form->addHidden('', 'user', 'text', true, true);

$form->addVariable(_("Report type"), 'type', 'radio', true, false, null, array($enum));
$form->addVariable(_("Report reason"), 'reason', 'longtext', true);

$user_id = Util::getFormData('id');

if ($form->validate()) {
    if (Util::getFormData('submitbutton') == _("Report")) {
        require FOLKS_BASE . '/lib/Report.php';
        $report = Folks_Report::factory();

        $body =  _("User") . ': ' . $user . "\n"
            . _("Report type") . ': ' . $enum[$vars->get('type')] . "\n"
            . _("Report reason") . ': ' . $vars->get('reason') . "\n"
            . $return_url;

        $result = $report->report($body);
        if (is_a($result, 'PEAR_Error')) {
            $notification->push(_("User was not reported.") . ' ' .
                                $result->getMessage(), 'horde.error');
        } else {
            $notification->push(_("User was reported."), 'horde.success');
        }
    } else {
        $notification->push(_("User was not reported."), 'horde.warning');
    }
    header('Location: ' . $return_url);
    exit;
}

require FOLKS_TEMPLATES . '/common-header.inc';
require FOLKS_TEMPLATES . '/menu.inc';
$form->renderActive(null, null, null, 'post');
require $registry->get('templates', 'horde') . '/common-footer.inc';