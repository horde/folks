<?php

use Horde\Util\Util;

/**
 * Copyright 2002-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author Chuck Hagenbuch <chuck@horde.org>
 * @author Jan Schneider <jan@horde.org>
 */

require_once __DIR__ . '/lib/base.php';

$shares = $injector->getInstance('Horde_Core_Factory_Share')->create();
$groups = $injector->getInstance('Horde_Group');
$auth = $injector->getInstance('Horde_Core_Factory_Auth')->create();

$reload = false;
$actionID = Util::getFormData('actionID', 'edit');
switch ($actionID) {
    case 'edit':
        try {
            $share = $shares->getShareById(Util::getFormData('cid', 0));
            $perm = $share->getPermission();
        } catch (Horde_Exception_NotFound $e) {
            if (($category = Util::getFormData('share')) !== null) {
                $share = $shares->getShare($category);
                $perm = $share->getPermission();
            }
        }
        if (!$GLOBALS['registry']->getAuth()
            || (isset($share) && $GLOBALS['registry']->getAuth() != $share->get('owner'))) {
            exit('permission denied');
        }
        break;

    case 'editform':
        try {
            $share = $shares->getShareById(Util::getFormData('cid'));
        } catch (Horde_Exception_NotFound $e) {
            $notification->push(_("Attempt to edit a non-existent share."), 'horde.error');
        }
        if ($share) {
            if (!$GLOBALS['registry']->getAuth()
                || $GLOBALS['registry']->getAuth() != $share->get('owner')) {
                exit('permission denied');
            }
            $perm = $share->getPermission();

            // Process owner and owner permissions.
            $old_owner = $share->get('owner');
            $new_owner = $registry->convertUsername(Util::getFormData('owner', $old_owner), true);
            if ($old_owner !== $new_owner && !empty($new_owner)) {
                if ($old_owner != $GLOBALS['registry']->getAuth() && !$registry->isAdmin()) {
                    $notification->push(_("Only the owner or system administrator may change ownership or owner permissions for a share"), 'horde.error');
                } else {
                    $share->set('owner', $new_owner);
                    $share->save();
                }
            }

            // Process default permissions.
            if (Util::getFormData('default_show')) {
                $perm->addDefaultPermission(Horde_Perms::SHOW, false);
            } else {
                $perm->removeDefaultPermission(Horde_Perms::SHOW, false);
            }
            if (Util::getFormData('default_read')) {
                $perm->addDefaultPermission(Horde_Perms::READ, false);
            } else {
                $perm->removeDefaultPermission(Horde_Perms::READ, false);
            }
            if (Util::getFormData('default_edit')) {
                $perm->addDefaultPermission(Horde_Perms::EDIT, false);
            } else {
                $perm->removeDefaultPermission(Horde_Perms::EDIT, false);
            }
            if (Util::getFormData('default_delete')) {
                $perm->addDefaultPermission(Horde_Perms::DELETE, false);
            } else {
                $perm->removeDefaultPermission(Horde_Perms::DELETE, false);
            }

            // Process guest permissions.
            if (Util::getFormData('guest_show')) {
                $perm->addGuestPermission(Horde_Perms::SHOW, false);
            } else {
                $perm->removeGuestPermission(Horde_Perms::SHOW, false);
            }
            if (Util::getFormData('guest_read')) {
                $perm->addGuestPermission(Horde_Perms::READ, false);
            } else {
                $perm->removeGuestPermission(Horde_Perms::READ, false);
            }
            if (Util::getFormData('guest_edit')) {
                $perm->addGuestPermission(Horde_Perms::EDIT, false);
            } else {
                $perm->removeGuestPermission(Horde_Perms::EDIT, false);
            }
            if (Util::getFormData('guest_delete')) {
                $perm->addGuestPermission(Horde_Perms::DELETE, false);
            } else {
                $perm->removeGuestPermission(Horde_Perms::DELETE, false);
            }

            // Process creator permissions.
            if (Util::getFormData('creator_show')) {
                $perm->addCreatorPermission(Horde_Perms::SHOW, false);
            } else {
                $perm->removeCreatorPermission(Horde_Perms::SHOW, false);
            }
            if (Util::getFormData('creator_read')) {
                $perm->addCreatorPermission(Horde_Perms::READ, false);
            } else {
                $perm->removeCreatorPermission(Horde_Perms::READ, false);
            }
            if (Util::getFormData('creator_edit')) {
                $perm->addCreatorPermission(Horde_Perms::EDIT, false);
            } else {
                $perm->removeCreatorPermission(Horde_Perms::EDIT, false);
            }
            if (Util::getFormData('creator_delete')) {
                $perm->addCreatorPermission(Horde_Perms::DELETE, false);
            } else {
                $perm->removeCreatorPermission(Horde_Perms::DELETE, false);
            }

            // Process user permissions.
            $u_names = Util::getFormData('u_names');
            $u_show = Util::getFormData('u_show');
            $u_read = Util::getFormData('u_read');
            $u_edit = Util::getFormData('u_edit');
            $u_delete = Util::getFormData('u_delete');

            foreach ($u_names as $key => $user) {
                // Apply backend hooks
                $user = $registry->convertUsername($user, true);
                // If the user is empty, or we've already set permissions
                // via the owner_ options, don't do anything here.
                if (empty($user) || $user == $new_owner) {
                    continue;
                }

                if (!empty($u_show[$key])) {
                    $perm->addUserPermission($user, Horde_Perms::SHOW, false);
                } else {
                    $perm->removeUserPermission($user, Horde_Perms::SHOW, false);
                }
                if (!empty($u_read[$key])) {
                    $perm->addUserPermission($user, Horde_Perms::READ, false);
                } else {
                    $perm->removeUserPermission($user, Horde_Perms::READ, false);
                }
                if (!empty($u_edit[$key])) {
                    $perm->addUserPermission($user, Horde_Perms::EDIT, false);
                } else {
                    $perm->removeUserPermission($user, Horde_Perms::EDIT, false);
                }
                if (!empty($u_delete[$key])) {
                    $perm->addUserPermission($user, Horde_Perms::DELETE, false);
                } else {
                    $perm->removeUserPermission($user, Horde_Perms::DELETE, false);
                }
            }

            // Process group permissions.
            $g_names = Util::getFormData('g_names');
            $g_show = Util::getFormData('g_show');
            $g_read = Util::getFormData('g_read');
            $g_edit = Util::getFormData('g_edit');
            $g_delete = Util::getFormData('g_delete');

            foreach ($g_names as $key => $group) {
                if (empty($group)) {
                    continue;
                }

                if (!empty($g_show[$key])) {
                    $perm->addGroupPermission($group, Horde_Perms::SHOW, false);
                } else {
                    $perm->removeGroupPermission($group, Horde_Perms::SHOW, false);
                }
                if (!empty($g_read[$key])) {
                    $perm->addGroupPermission($group, Horde_Perms::READ, false);
                } else {
                    $perm->removeGroupPermission($group, Horde_Perms::READ, false);
                }
                if (!empty($g_edit[$key])) {
                    $perm->addGroupPermission($group, Horde_Perms::EDIT, false);
                } else {
                    $perm->removeGroupPermission($group, Horde_Perms::EDIT, false);
                }
                if (!empty($g_delete[$key])) {
                    $perm->addGroupPermission($group, Horde_Perms::DELETE, false);
                } else {
                    $perm->removeGroupPermission($group, Horde_Perms::DELETE, false);
                }
            }

            $result = $share->setPermission($perm, false);
            if (is_a($result, 'PEAR_Error')) {
                $notification->push($result, 'horde.error');
            } else {
                $result = $share->save();
                if (is_a($result, 'PEAR_Error')) {
                    $notification->push($result, 'horde.error');
                } else {
                    if (Util::getFormData('save_and_finish')) {
                        echo Horde::wrapInlineScript(['window.close();']);
                        exit;
                    }
                    $notification->push(sprintf(_("Updated \"%s\"."), $share->get('name')), 'horde.success');
                }
            }
        }
        break;
}

if (is_a($share, 'PEAR_Error')) {
    $title = _("Edit Permissions");
} else {
    $title = sprintf(_("Edit Permissions for %s"), $share->get('name'));
}

if ($auth->hasCapability('list')) {
    $userList = $auth->listNames();
} else {
    $userList = [];
}

$groupList = [];
try {
    $groupList = $groups->listAll(empty($conf['share']['any_group'])
                                  ? $registry->getAuth()
                                  : null);
    asort($groupList);
} catch (Horde_Group_Exception $e) {
    Horde::log($e, 'NOTICE');
}

$page_output->topbar = $page_output->sidebar = false;

$page_output->header([
    'title' => $title,
]);
$notification->notify(['listeners' => 'status']);
require $registry->get('templates', 'horde') . '/shares/edit.inc';
$page_output->footer();
