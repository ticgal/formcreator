<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

use GlpiPlugin\Formcreator\Form;
use GlpiPlugin\Formcreator\Target\Problem as TargetProblem;
use GlpiPlugin\Formcreator\Target_Actor;

include ('../../../../inc/includes.php');

Session::checkRight(Form::$rightname, UPDATE);

// Check if plugin is activated...
if (!(new Plugin())->isActivated('formcreator')) {
   Html::displayNotFoundError();
}
$targetProblem = new TargetProblem();

// Edit an existing target problem
if (isset($_POST['update'])) {
   $targetProblem->getFromDB((int) $_POST['id']);
   if (!$targetProblem->canUpdateItem()) {
      Session::addMessageAfterRedirect(__('No right to update this item.', 'formcreator'), false, ERROR);
   } else {
      $targetProblem->update($_POST);
   }
   Html::back();

} else if (isset($_POST['actor_role'])) {
   $id          = (int) $_POST['id'];
   $actor_value = isset($_POST['actor_value_' . $_POST['actor_type']])
                  ? $_POST['actor_value_' . $_POST['actor_type']]
                  : '';
   $use_notification = ($_POST['use_notification'] == 0) ? 0 : 1;
   $targetProblem_actor = new Target_Actor();
   $targetProblem_actor->add([
      'itemtype'         => $targetProblem->getType(),
      'items_id'         => $id,
      'actor_role'       => $_POST['actor_role'],
      'actor_type'       => $_POST['actor_type'],
      'actor_value'      => $actor_value,
      'use_notification' => $use_notification,
   ]);
   Html::back();

} else if (isset($_GET['delete_actor'])) {
   $targetProblem_actor = new Target_Actor();
   $targetProblem_actor->delete([
      'itemtype' => $targetProblem->getType(),
      'items_id' => $id,
      'id'       => (int) $_GET['delete_actor']
   ]);
   Html::back();

   // Show target ticket form
} else {
   Html::header(
      __('Form Creator', 'formcreator'),
      $_SERVER['PHP_SELF'],
      'admin',
      Form::class
   );

   $targetProblem->getFromDB((int) $_REQUEST['id']);
   $form = Form::getByItem($targetProblem);
   $_SESSION['glpilisttitle'][$targetProblem::getType()] = sprintf(
      __('%1$s = %2$s'),
      $form->getTypeName(1), $form->getName()
   );
   $_SESSION['glpilisturl'][$targetProblem::getType()]   = $form->getFormURL()."?id=".$form->getID();

   $targetProblem->display($_REQUEST);

   Html::footer();
}
