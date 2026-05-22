<h1><?php echo $title ?></h1>

<ul class="notices">
 <li>
  <?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo Horde::img('alerts/warning.png') . sprintf(_("User %s has been disabled."), $user) ?>
 </li>
</ul>
