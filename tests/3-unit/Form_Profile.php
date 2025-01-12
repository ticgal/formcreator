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
use GlpiPlugin\Formcreator\Linker;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;
use Profile;

class Form_Profile extends CommonTestCase {

   public function testPrepareInputForAdd() {
      $instance = $this->newTestedInstance();
      $output = $instance->prepareInputForAdd([
         'uuid' => '0000',
      ]);

      $this->array($output)->HasKey('uuid');
      $this->string($output['uuid'])->isEqualTo('0000');

      $output = $instance->prepareInputForAdd([]);

      $this->array($output)->HasKey('uuid');
      $this->string($output['uuid']);
   }

   public function testExport() {
      $instance = $this->newTestedInstance();

      // Try to export an empty item
      $this->exception(function () use ($instance) {
         $instance->export();
      })->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ExportFailureException::class);

      // Prepare an item to export
      $form = $this->getForm();
      $formFk = Form::getForeignKeyField();
      $profile = new Profile();
      $profile->getFromDBByCrit([
         'name' => 'Super-Admin'
      ]);
      $instance->add([
         $formFk => $form->getID(),
         'profiles_id' => $profile->getID(),
      ]);
      $instance->getFromDB($instance->getID());

      // Export the item without the ID and with UUID
      $output = $instance->export(false);

      // Test the exported data
      $fieldsWithoutID = [
      ];
      $extraFields = [
         '_profile',
      ];
      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['uuid'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));

      // Export the item without the UUID and with ID
      $output = $instance->export(true);
      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['id'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));
   }

   public function testImport() {
      $testedClass = $this->getTestedClassName();
      $form = $this->getForm();
      $uuid = plugin_formcreator_getUuid();
      $input = [
         '_profile' => 'Technician',
         'uuid' => $uuid,
      ];

      $linker = new Linker();
      $formProfileId = $testedClass::import($linker, $input, $form->getID());
      $this->integer($formProfileId)->isGreaterThan(0);

      $instance = $this->newTestedInstance();
      $instance->delete([
         'id' => $formProfileId
      ], 1);

      unset($input['uuid']);

      $this->exception(
         function() use($linker, $input, $form, $testedClass) {
            $testedClass::import($linker, $input, $form->getID());
         }
      )->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ImportFailureException::class)
         ->hasMessage('UUID or ID is mandatory for Access type'); // passes

      $input['id'] = $formProfileId;
      $testedClass = $this->getTestedClassName();
      $formProfileId2 = $testedClass::import($linker, $input, $form->getID());
      $this->integer((int) $formProfileId)->isNotEqualTo($formProfileId2);
   }
}
