<?php

use Horde\Util\Util;

/**
 * Copyright Obala d.o.o. (www.obala.si)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author Duck <duck@obala.net>
 * @package Folks
 */

define('FOLKS_BASE', __DIR__);
require_once FOLKS_BASE . '/lib/base.php';

if (Util::getFormData('submitbutton') == _("Close")) {

    echo '<script type="text/javascript">RedBox.close();</script>';

} elseif (Util::getFormData('formname') == 'savesearch') {

    $result = $folks_driver->saveSearch(Util::getFormData('search_criteria'), Util::getFormData('search_name'));
    if ($result instanceof PEAR_Error) {
        $notification->push($result);
    } else {
        $notification->push(_("Search criteria saved successfuly"), 'horde.success');
        Horde::url('search.php')->redirect();
    }

} elseif ((Util::getGet('delete') == 1) && Util::getGet('query')) {

    $result = $folks_driver->deleteSavedSearch(Util::getGet('query'));
    if ($result instanceof PEAR_Error) {
        $notification->push($result);
    } else {
        $notification->push(_("Search criteria deleted."), 'horde.success');
        Horde::url('search.php')->redirect();
    }
}

// Render
$vars = Horde_Variables::getDefaultVariables();
$vars->set('search_criteria', $session->get('folks', 'last_search'));
$form = new Horde_Form($vars, '', 'savesearch');
$form->addVariable(_("Name"), 'search_name', 'text', true);
$form->addHidden('', 'search_criteria', 'text', true);
$form->setButtons([_("Save"), _("Close")]);
$notification->notify(['listeners' => 'status']);
$form->renderActive(null, null, Horde::selfUrl(), 'post');
