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
use GlpiPlugin\Formcreator\Question;

include ('../../../inc/includes.php');
Session::checkRight(Form::$rightname, UPDATE);

$question = new Question();
if (!$question->canCreate()) {
    http_response_code(403);
    echo array_shift($_SESSION['MESSAGE_AFTER_REDIRECT'][ERROR]);
    unset($_SESSION['MESSAGE_AFTER_REDIRECT'][ERROR]);
    exit;
}

if (!$question->add($_REQUEST)) {
    http_response_code(500);
    Session::addMessageAfterRedirect(__('Could not add the question', 'formcreator'), false, ERROR);
    exit;
}
$json = [
    'y'      => $question->fields['row'],
    'x'      => $question->fields['col'],
    'width'  => $question->fields['width'],
    'height' => '1',
    'html'   => $question->getDesignHtml(),
];
echo json_encode($json, JSON_UNESCAPED_UNICODE);
