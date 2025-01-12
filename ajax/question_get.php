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
Session::checkLoginUser();
Session::checkRight(Form::$rightname, UPDATE);

if (!isset($_REQUEST['id'])) {
   http_response_code(400);
   exit();
}
$sectionId = (int) $_REQUEST['id'];

$json = [];
foreach (Question::getQuestionsFromSection($sectionId) as $question) {
    $json[$question->getID()] = [
        'y'      => $question->fields['row'],
        'x'      => $question->fields['col'],
        'width'  => $question->fields['width'],
        'height' => '1',
        'html'   => $question->getDesignHtml(),
    ];
}

echo json_encode($json);
