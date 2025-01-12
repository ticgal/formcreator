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
 * @copyright Copyright © 2011 - 2020 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace GlpiPlugin\Formcreator\Field\tests\units;

use GlpiPlugin\Formcreator\Tests\CommonFunctionalTestCase;
use GlpiPlugin\Formcreator\Tests\CommonQuestionTest;
use Location;

class DropdownField extends CommonFunctionalTestCase
{
   use CommonQuestionTest;

   public function testCreateForm() {
      // Use a clean entity for the tests
      $this->login('glpi', 'glpi');

      $form = $this->showCreateQuestionForm();

      // set question type
      $this->client->executeScript(
         '$(\'form[data-itemtype="GlpiPlugin_Formcreator_Question"] [name="fieldtype"]\').val("dropdown")
         $(\'form[data-itemtype="GlpiPlugin_Formcreator_Question"] [name="fieldtype"]\').select2().trigger("change")
         '
      );

      $this->client->waitForVisibility('form[data-itemtype="GlpiPlugin_Formcreator_Question"] select[name="required"]');
      $this->client->waitForVisibility('form[data-itemtype="GlpiPlugin_Formcreator_Question"] select[name="itemtype"]');
      $this->client->waitForVisibility('form[data-itemtype="GlpiPlugin_Formcreator_Question"] select[name="show_empty"]');

      // set question itemtype
      $this->client->executeScript(
         '$(\'form[data-itemtype="GlpiPlugin_Formcreator_Question"] [name="itemtype"]\').val("' . \ITILCategory::gettype() . '")
         $(\'form[data-itemtype="GlpiPlugin_Formcreator_Question"] [name="itemtype"]\').select2().trigger("change")
         '
      );
      $this->client->waitForVisibility('form[data-itemtype="GlpiPlugin_Formcreator_Question"] select[name="default_values"]');

      $this->_testQuestionCreated($form, __METHOD__);
   }

   public function testRenderQuestion() {
      $this->_testRenderQuestion([
         'fieldtype' => 'dropdown',
         'itemtype'  => Location::getType(),
      ]);
   }
}
