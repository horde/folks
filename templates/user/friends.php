<h1><?php echo $title ?></h1>

<ul class="notices">
<li><img src="<?php echo $registry->getImageDir('horde')?>/alerts/warning.png"><?php echo sprintf(_("User %s would like to his profile remains visible only to his friends."), $user) ?></li>
<?php 
if ($registry->hasMethod('letter/compose')) {
    echo '<li><img src="' . $registry->getImageDir('letter') . '/letter.png">'
                    . sprintf(_("You can still send a private message to user %s."), $user)
                    . ' <a href="' . $registry->callByPackage('letter', 'compose', array(array('user_to' => $user)))  . '">' . _("Click here") . '</a>'
                     . '</li>';
}
?>
</ul>
