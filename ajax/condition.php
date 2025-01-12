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

use GlpiPlugin\Formcreator\Condition;
use GlpiPlugin\Formcreator\ConditionnableInterface;
use GlpiPlugin\Formcreator\Form;

include ('../../../inc/includes.php');
Session::checkRight(Form::$rightname, UPDATE);

// integrity check
if (!isset($_POST['itemtype']) || !isset($_POST['items_id'])) {
   http_response_code(400);
   die();
}
if (!is_subclass_of($_POST['itemtype'], ConditionnableInterface::class)) {
   http_response_code(400);
   die();
}

// Build an empty item or load it from DB
/** @var CommonDBTM $parent */
$parent = new $_POST['itemtype'];
if ($parent::isNewID((int) $_POST['items_id'])) {
   $parent->getEmpty();
   $parent->fields = array_intersect_key($_POST, $parent->fields);
} else {
   if (!$parent->getFromDB((int) $_POST['items_id'])) {
      http_response_code(404);
      die();
   }
}

// get an empty condition HTML table row
$condition = new Condition();
$condition->fields['itemtype'] = $_POST['itemtype'];
$condition->fields['items_id'] = (int) $_POST['items_id'];
echo $condition->getConditionHtml($parent);
