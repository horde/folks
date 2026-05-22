<h1><?php echo $title ?></h1>

<ul class="notices">
 <li>
  <?php echo Horde_::img('alerts/warning.png') . sprintf(_("User %s would like to his profile remains visible only to his friends."), $user) ?></li>
<?php
if ($registry->hasMethod('letter/compose')) {
    /**
     * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
     * @deprecated Use Horde_Themes_Image::tag() instead
     * @see Horde_Deprecated::img()
     */
    echo '<li>' . Horde::img('letter.png')
                        . sprintf(_("You can still send a private message to user %s."), $user)
                        . ' <a href="' . $registry->callByPackage('letter', 'compose', [['user_to' => $user]]) . '">' . _("Click here") . '</a>'
                         . '</li>';
}
?>
</ul>
