<?php
/**
 * Folks api
 *
 * Copyright 2008 Obala d.o.o (www.obala.si)
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see http://cvs.horde.org/co.php/folks/LICENSE.
 *
 * $Id: api.php 1235 2009-01-28 19:25:04Z duck $
 *
 * @author Duck <duck@obala.net>
 * @package Folks
 */
class Folks_Api extends Horde_Registry_Api
{
    public $version = 'H4 (0.1-git)';

    public $services = array(
        'commentCallback' => array(
            'args' => array('id' => 'string'),
            'type' => 'string'
        ),

        'removeUserData' => array(
            'args' => array('user' => 'string'),
            'type' => 'boolean'
        ),

        'hasComments' => array(
            'args' => array(),
            'type' => 'boolean'
        ),

        'getOnlineUsers' => array(
            'args' => array(),
            'type' => 'array'
        ),

        'getProfile' => array(
            'args' => array(),
            'type' => 'array'
        ),

        'getFriends' => array(
            'args' => array('user' => 'string'),
            'type' => 'array'
        ),

        'addFriend' => array(
            'args' => array('friend' => 'string'),
            'type' => 'boolean'
        ),

        'getBlacklist' => array(
            'args' => array('user' => 'string'),
            'type' => 'array'
        ),

        'addBlacklisted' => array(
            'args' => array('user' => 'string'),
            'type' => 'boolean'
        ),

        'removeBlacklisted' => array(
            'args' => array('user' => 'string'),
            'type' => 'boolean'
        ),

        'isBlacklisted' => array(
            'args' => array('user' => 'string'),
            'type' => 'boolean'
        ),

        'show' => array(
            'link' => '%application%/user.php?user=|user|'
        ),

        'listTimeObjectCategories' => array(
            'type' => '{urn:horde}stringArray'
        ),

        'listTimeObjects' => array(
            'args' => array('start' => 'int', 'end' => 'int'),
            'type' => '{urn:horde}hashHash'
        ),

        'logActivity' => array(
            'args' => array('activity_message' => 'string',
            'scope' => 'string',
            'user' => 'string'),
            'type' => 'boolean'
        ),

        'getActivity' => array(
            'args' => array('user' => 'string'),
            'type' => 'boolean'
        ),

        'setStatus' => array(
            'args' => array('status' => 'boolean'),
            'args' => array('user' => 'string'),
            'type' => 'array'
        ),

        'getStatus' => array(
            'args' => array('user' => 'string'),
            'type' => 'array'
        ),

        'authenticate' => array(
            'args' => array('userID'      => 'string',
            'credentials' => '{urn:horde}hash',
            'params'      => '{urn:horde}hash'),
            'checkperms' => false,
            'type' => 'boolean'
        ),

        'userExists' => array(
            'args' => array('userId' => 'string'),
            'type' => 'boolean'
        ),

        'addUser' => array(
            'args' => array('userId' => 'string')
        ),

        'getImageUrl' => array(
            'args' => array(
                'user' => 'string',
                'view' => 'string',
                'full' => 'boolean'
            ),
            'type' => 'string'
        ),

        'userList' => array(
            'type' => '{urn:horde}stringArray'
        ),

        'removeUser' => array(
            'args' => array('userId' => 'string')
        )
    );

    public function __construct()
    {
        if (!Horde_Auth::isAdmin()) {
            unset($this->services['userList'], $this->services['removeUser']);
        }
    }

    /**
     * Returns profile image URL.
     *
     * @param string  $user      User uid
     * @param string $view       The view ('small', 'big') to show.
     * @param boolean $full      Return a path that includes the server name?
     *
     * @return string  The image path.
     */
    public function getImageUrl($user, $view = 'small', $full = false)
    {
        require_once dirname(__FILE__) . '/base.php';
        return Folks::getImageUrl($user, $view, $full);
    }

    /**
     * Callback for comment API.
     *
     * @param int $id       Internal data identifier.
     * @param string $type  Type of data to retreive (title, owner...).
     * @param array $params  Parameter to be passed to callback function
     */
    public function commentCallback($id, $type = 'title', $params = null)
    {
        static $info;

        if (!empty($info[$id][$type])) {
            return $info[$id][$type];
        }

        require_once dirname(__FILE__) . '/base.php';

        switch ($type) {

        case 'owner':
            return $id;

        case 'link':
            return Folks::getUrlFor('user', $id);

        case 'messages':

            // Update comments count
            $result = $GLOBALS['folks_driver']->updateComments($id);
            if ($result instanceof PEAR_Error) {
                return $result;
            }

            // Update activity log
            $link = '<a href="' . Folks::getUrlFor('user', $id) . '">' . $id . '</a>';
            return $GLOBALS['folks_driver']->logActivity(sprintf(_("Commented user %s."), $link), 'folks:comments');

            return true;

        default:
            return $id;
        }
    }

    /**
     * Comments are enebled
     */
    public function hasComments()
    {
        return $GLOBALS['conf']['comments']['allow'];
    }

    /**
     * Get online users
     */
    public function getOnlineUsers()
    {
        require_once dirname(__FILE__) . '/base.php';

        return $GLOBALS['folks_driver']->getOnlineUsers();
    }

    /**
     * Get user profile
     *
     * @param string $user User to get profile for
     */
    public function getProfile($user = null)
    {
        require_once dirname(__FILE__) . '/base.php';

        return $GLOBALS['folks_driver']->getProfile($user);
    }


    /**
     * Get user friends
     *
     * @param string $user  Username to get friends for
     *
     * @return array of users
     */
    public function getFriends($user = null)
    {
        require_once dirname(__FILE__) . '/Friends.php';

        $friends = Folks_Friends::singleton('sql', array('user' => $user));

        return $friends->getFriends();
    }

    /**
     * Add user to our friend list
     *
     * @param string $friend   Friend's usersame
     *
     * @return true or PEAR_Error
     */
    public function addFriend($user = null)
    {
        require_once dirname(__FILE__) . '/Friends.php';

        $friends = Folks_Friends::singleton('sql', array('user' => $user));

        return $friends->addFriend($user);
    }

    /**
     * Remove user from a fiend list
     *
     * @param string $friend   Friend's usersame
     *
     * @return true or PEAR_Error
     */
    public function removeFriend($user = null)
    {
        require_once dirname(__FILE__) . '/Friends.php';

        $friends = Folks_Friends::singleton('sql', array('user' => $user));

        return $friends->removeFriend($user);
    }

    /**
     * Get user blacklist
     *
     * @param string $user  Username to get blacklist for
     *
     * @return array of users
     */
    public function _getBlacklist($user = null)
    {
        require_once dirname(__FILE__) . '/Friends.php';

        $friends = Folks_Friends::singleton('sql', array('user' => $user));

        return $friends->getBlacklist();
    }

    /**
     * Add user to a blacklist list
     *
     * @param string $user   Usersame
     */
    public function addBlacklisted($user = null)
    {
        require_once dirname(__FILE__) . '/Friends.php';

        $friends = Folks_Friends::singleton('sql', array('user' => $user));

        return $friends->addBlacklisted($user);
    }

    /**
     * Remove user from a blacklist list
     *
     * @param string $user   Usersame
     */
    public function removeBlacklisted($user = null)
    {
        require_once dirname(__FILE__) . '/Friends.php';

        $friends = Folks_Friends::singleton('sql', array('user' => $user));

        return $friends->removeBlacklisted($user);
    }

    /**
     * Are we blackisted by user this user?
     *
     * @param string $user  Username to get blacklist for
     *
     * @return array of users
     */
    public function isBlacklisted($user = null)
    {
        require_once dirname(__FILE__) . '/Friends.php';

        $friends = Folks_Friends::singleton('sql', array('user' => $user));

        return $friends->isBlacklisted(Horde_Auth::getAuth());
    }

    /**
     * Users categories
     */
    public function listTimeObjectCategories()
    {
        return array('birthday_friends' => _("Friends Birthdays"));
    }

    /**
     * Lists users with birthdays/goout dates as time objects.
     *
     * @param array $categories  The time categories (from listTimeObjectCategories) to list.
     * @param Horde_Date $start       The start date of the period.
     * @param Horde_Date $end         The end date of the period.
     */
    public function listTimeObjects($categories, $start, $end)
    {
        require_once dirname(__FILE__) . '/base.php';
        require_once FOLKS_BASE . '/lib/Friends.php';

        $friends_driver = Folks_Friends::singleton('sql');
        $friends = $friends_driver->getFriends();
        if ($friends instanceof PEAR_Error) {
            return array();
        }

        $objects = array();

        foreach ($friends as $friend) {
            $user = $GLOBALS['folks_driver']->getProfile($friend);
            if ($user instanceof PEAR_Error) {
                continue;
            }

            $user['user_birthday'] = date('Y') . substr($user['user_birthday'], 4);
            $born = strtotime($user['user_birthday']);
            if ($born === false ||
                $born < $start->timestamp() ||
                $born > $end->timestamp()) {
                    continue;
                }

            $age = Folks::calcAge($user['user_birthday']);
            $desc = $age['age'] . ' (' . $age['sign'] . ')';

            $objects[$friend] = array(
                'title' => $friend,
                'description' => $desc,
                'id' => $friend,
                'start' => date('Y-m-d\TH:i:s', $born),
                'end' => date('Y-m-d\TH:i:s', $born + 1),
                'params' => array('user' => $friend),
                'link' => Folks::getUrlFor('user', $friend, true));
        }

        return $objects;
    }

    /**
     * Log user's activity
     *
     * @param mixed $message    Activity message or details
     * @param string $scope    Scope
     * @param string $user    $user
     *
     * @return boolean  True on success or a PEAR_Error object on failure.
     */
    public function logActivity($message, $scope = 'folks', $user = null)
    {
        if (empty($user)) {
            $user = Horde_Auth::getAuth();
        } elseif ($user !== Horde_Auth::getAuth() && !Horde_Auth::isAdmin('admin:' . $scope)) {
            return PEAR::raiseError(_("You cannot log activities for other users."));
        }

        require_once dirname(__FILE__) . '/base.php';

        return $GLOBALS['folks_driver']->logActivity($message, $scope, $user);
    }

    /**
     * Get user's activity
     *
     * @param string $user    Username
     * @param int $limit    Number of actions to return
     *
     * @return array    Activity log
     */
    public function getActivity($user, $limit = 10)
    {
        require_once dirname(__FILE__) . '/base.php';

        return $GLOBALS['folks_driver']->getActivity($user, $limit);
    }

    /**
     * Set user status
     *
     * @param booelan $online True to set user online, false to push it offline
     * @param string $user    Username
     *
     * @return boolean True if succes, PEAR_Error on failure
     */
    public function setStatus($online = true, $user = null)
    {
        require_once dirname(__FILE__) . '/base.php';

        if ($user == null) {
            $user = Horde_Auth::getAuth();
        }

        if ($online) {
            return $GLOBALS['folks_driver']->resetOnlineUsers();
        } else {
            $result = $GLOBALS['folks_driver']->deleteOnlineUser($user);
            $GLOBALS['cache']->expire('folksOnlineUsers');
            return $result;
        }
    }

    /**
     * Get user status
     *
     * @param string $user    Username
     *
     * @return boolean True if user is online, false otherwise
     */
    public function getStatus($user = null)
    {
        require_once dirname(__FILE__) . '/base.php';

        if ($user == null) {
            $user = Horde_Auth::getAuth();
        }

        return $GLOBALS['folks_driver']->isOnline($user);
    }

    /**
     * Authenticate a givern user
     *
     * @param string $userID       Username
     * @param array  $credentials  Array of criedentials (password requied)
     * @param array  $params       Additional params
     *
     * @return boolean  Whether IMP authentication was successful.
     */
    public function authenticate($userID, $credentials, $params)
    {
        require_once dirname(__FILE__) . '/base.php';

        return $GLOBALS['folks_driver']->comparePassword($userID, $credentials['password']);
    }

    /**
     * Check if a user exists
     *
     * @param string $userID       Username
     *
     * @return boolean  True if user exists
     */
    public function userExists($userId)
    {
        require_once dirname(__FILE__) . '/base.php';

        return $GLOBALS['folks_driver']->userExists($userId);
    }

    /**
     * Lists all users in the system.
     *
     * @return array  The array of userIds, or a PEAR_Error object on failure.
     */
    public function userList()
    {
        require_once dirname(__FILE__) . '/base.php';

        $users = array();
        foreach ($GLOBALS['folks_driver']->getUsers() as $user) {
            $users[] = $user['user_uid'];
        }

        return $users;
    }

    /**
     * Adds a set of authentication credentials.
     *
     * @param string $userId  The userId to add.
     *
     * @return boolean  True on success or a PEAR_Error object on failure.
     */
    public function addUser($userId)
    {
        require_once dirname(__FILE__) . '/base.php';

        return $GLOBALS['folks_driver']->addUser($userId);
    }

    /**
     * Deletes a set of authentication credentials.
     *
     * @param string $userId  The userId to delete.
     *
     * @return boolean  True on success or a PEAR_Error object on failure.
     */
    public function removeUser($userId)
    {
        require_once dirname(__FILE__) . '/base.php';

        return $GLOBALS['folks_driver']->deleteUser($userId);
    }

    /**
     * Deletes a user and its data
     *
     * @param string $userId  The userId to delete.
     *
     * @return boolean  True on success or a PEAR_Error object on failure.
     */
    public function removeUserData($user = null)
    {
        return $this->removeUser($user);
    }

}
