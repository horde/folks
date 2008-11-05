<?php
/**
 * $Id: comments.php 974 2008-10-07 19:46:00Z duck $
 *
 * Copyright Obala d.o.o. (www.obala.si)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author Duck <duck@obala.net>
 * @package Folks
 */

define('FOLKS_BASE', dirname(__FILE__) . '/..');
require_once FOLKS_BASE . '/lib/base.php';
require_once 'tabs.php';

$title = _("Comments");
$profile = $folks_driver->getProfile();
if ($profile instanceof PEAR_Error) {
    $notification->push($profile);
    header('Location: ' . Folks::getUrlFor('list', 'list'));
    exit;
}

$comments = array(
    'never' => _("No one"),
    'all' => _("Any one"),
    'authenticated' => _("Authenticated users"),
    'moderate' => _("Moderate comments - I will approve every single comment")
);

if ($conf['comments']['allow'] == 'authenticated') {
    unset($comments['all']);
}

$form = new Horde_Form($vars, $title, 'comments');
$v = $form->addVariable(_("Who can post comments to your profile"), 'user_comments', 'radio', false, false, null, array($comments));
$v->setDefault('authenticated');
$form->setButtons(array(_("Save"), _("Delete all current comments")));

if (!$form->isSubmitted()) {
    $vars->set('user_comments', $profile['user_comments']);

} elseif ($form->validate()) {

    if (Util::getFormData('submitbutton') == _("Delete all current comments")) {

        $result = $registry->call('forums/deleteForum', array('folks', Auth::getAuth()));
        if ($result instanceof PEAR_Error) {
            $notification->push($result);
        } else {
            $result = $folks_driver->updateComments(Auth::getAuth(), true);
            if ($result instanceof PEAR_Error) {
                $notification->push($result);
            } else {
                $notification->push(_("Comments deleted successfuly"), 'horde.success');
            }
        }

    } else {

        // Update forum status
        if ($vars->get('user_comments') == 'moderate' && $profile['user_comments'] != 'moderate' ||
            $vars->get('user_comments') != 'moderate' && $profile['user_comments'] == 'moderate') {

            $info = array('author' => Auth::getAuth(),
                            'forum_name' => Auth::getAuth(),
                            'forum_moderated' => ($profile['user_comments'] == 'moderate'));
            $result = $registry->call('forums/saveFrom', array('folks', '', $info));
            if ($result instanceof PEAR_Error) {
                $notification->push($result);
            }
        }

        // Update profile
        $result = $folks_driver->saveProfile(array('user_comments' => $vars->get('user_comments')));
        if ($result instanceof PEAR_Error) {
            $notification->push($result);
        } else {
            $notification->push(_("Your comments preference was sucessfuly saved."), 'horde.success');
        }
    }
}

Horde::addScriptFile('tables.js', 'horde', true);
require FOLKS_TEMPLATES . '/common-header.inc';
require FOLKS_TEMPLATES . '/menu.inc';

echo $tabs->render('comments');
$form->renderActive(null, null, null, 'post');

if ($profile['user_comments'] == 'moderate') {
    echo '<br />';
    $result = $registry->call('forums/moderateForm', array('folks'));
    if ($result instanceof PEAR_Error) {
        echo $result->getMessage();
    } else {
        echo $result;
    }
}

require $registry->get('templates', 'horde') . '/common-footer.inc';