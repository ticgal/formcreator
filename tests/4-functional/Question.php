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

namespace tests\units\GlpiPlugin\Formcreator;

use GlpiPlugin\Formcreator\Form;
use GlpiPlugin\Formcreator\Tests\CommonFunctionalTestCase;

class Question extends CommonFunctionalTestCase {
   public function testCreateQuestion() {
      $form = $this->getForm();
      $this->getSection([
         Form::getForeignKeyField() => $form->getID(),
      ]);
      $this->crawler = $this->client->request('GET', '/plugins/formcreator/front/form.form.php?id=' . $form->getID());
      $this->client->waitFor('#backtotop');

      // Open the questions tab
      $this->browsing->openTab('Questions');
      $this->takeScreenshot();

      // Add a question in the 1st and only section
      $anchorSelector = ".plugin_formcreator_section .plugin_formcreator_question a";
      $this->client->executeScript("
         $('" . $anchorSelector . "').click();
      ");
      $testedClass = $this->getTestedClassName();
      $this->client->waitFor('form[data-itemtype="' . str_replace('\\', '_', $testedClass) . '"]');
      $this->takeScreenshot();

   }
}