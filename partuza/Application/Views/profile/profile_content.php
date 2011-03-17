<?php
if ($vars['is_owner']) {
  $this->template('profile/profile_friendrequests.php', $vars);
}
$width = 488;
$view = 'profile';
foreach ($vars['applications'] as $gadget) {
  $has_app = false;
  if (isset($person_apps) && !$vars['is_owner']) {
    foreach ($person_apps as $papp) {
      if ($papp['id'] == $gadget['id']) {
        $has_app = true;
        break;
      }
    }
  }
  $this->template('/gadget/gadget.php', array('width' => $width, 'gadget' => $gadget, 'person' => $vars['person'], 'view' => $view, 'has_app' => $has_app));
}
?><br />