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
use GlpiPlugin\Formcreator\Section;

include ('../../../inc/includes.php');
Session::checkRight(Form::$rightname, UPDATE);

if (!isset($_REQUEST['id'])) {
    http_response_code(400);
    Session::addMessageAfterRedirect(__('Bad request', 'formcreator'), false, ERROR);
    exit;
}

$section = new Section();
if (!$section->canUpdate()) {
    http_response_code(403);
    Session::addMessageAfterRedirect(__('You don\'t have right for this action', 'formcreator'), false, ERROR);
    exit;
}

if (!$section->update($_REQUEST)) {
    http_response_code(500);
    Session::addMessageAfterRedirect(__('Could not update the section', 'formcreator'), false, ERROR);
    exit;
}
echo json_encode(['id' => $section->getID(), 'name' => $section->fields['name']], JSON_UNESCAPED_UNICODE);