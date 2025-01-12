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

namespace tests\units\GlpiPlugin\Formcreator\Target;

use Entity;
use GlpiPlugin\Formcreator\Form;
use GlpiPlugin\Formcreator\FormAnswer;
use GlpiPlugin\Formcreator\Linker;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;
use Group_Problem;
use Item_Problem;
use Problem_Supplier;
use Problem_User;
use ProblemTemplate;
use Profile;
use Session;
use User;

class Problem extends CommonTestCase {

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testSetTargetEntity':
         case 'testImport':
            $this->boolean($this->login('glpi', 'glpi'))->isTrue();
            break;
      }
   }

   public function providerGetTypeName() {
      return [
         [
            'number' => 0,
            'expected' => 'Target problems',
         ],
         [
            'number' => 1,
            'expected' => 'Target problem',
         ],
         [
            'number' => 2,
            'expected' => 'Target problems',
         ],
      ];
   }

   /**
    * @dataProvider providerGetTypeName
    * @param integer $number
    * @param string $expected
    */
   public function testGetTypeName($number, $expected) {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getTypeName($number);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerPrepareInputForUpdate() {
      $testedClass = $this->getTestedClassName();
      return [
         [
            'input' => [
               'name' => '',
               'content' => '',
               'sla_rule' => (string) $testedClass::SLA_RULE_NONE,
               'ola_rule' => (string) $testedClass::OLA_RULE_NONE,
            ],
            'expected' => [
            ],
            'message' => 'The name cannot be empty!',
         ],
         [
            'input' => [
               'name' => 'something',
               'content' => '',
               'sla_rule' => (string) $testedClass::SLA_RULE_NONE,
               'ola_rule' => (string) $testedClass::OLA_RULE_NONE,
            ],
            'expected' => [
            ],
            'message' => 'The description cannot be empty!',
         ],
         [
            'input' => [
               'name' => 'something',
               'content' => 'foo',
               'destination_entity' => $testedClass::DESTINATION_ENTITY_SPECIFIC,
               '_destination_entity_value_specific' => '0',
               'urgency_rule' => $testedClass::URGENCY_RULE_SPECIFIC,
               '_urgency_specific' => '3',
               'category_rule' => $testedClass::CATEGORY_RULE_NONE,
               'category_question' => '0',
               'sla_rule' => (string) $testedClass::SLA_RULE_NONE,
               'ola_rule' => (string) $testedClass::OLA_RULE_NONE,
            ],
            'expected' => [
               'name' => 'something',
               'content' => 'foo',
               'destination_entity' => $testedClass::DESTINATION_ENTITY_SPECIFIC,
               'destination_entity_value' => '0',
               'urgency_rule' => $testedClass::URGENCY_RULE_SPECIFIC,
               'urgency_question' => '3',
               'category_rule' => $testedClass::CATEGORY_RULE_NONE,
               'category_question' => '0',
            ],
            'message' => null,
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareInputForUpdate
    */
   public function testPrepareInputForUpdate($input, $expected, $message) {
      $instance = $this->newTestedInstance();
      $output = $instance->prepareInputForUpdate($input);

      if ($message !== null) {
         $this->sessionHasMessage($message, ERROR);
         $this->array($output)->hasSize(0);
         return;
      }

      $this->string($output['name'])->isEqualTo($expected['name']);
      $this->string($output['content'])->isEqualTo($expected['content']);
      $this->string($output['name'])->isEqualTo($expected['name']);
      $this->integer($output['destination_entity'])->isEqualTo($expected['destination_entity']);
      $this->string($output['destination_entity_value'])->isEqualTo($expected['destination_entity_value']);
      $this->integer($output['urgency_rule'])->isEqualTo($expected['urgency_rule']);
      $this->string($output['urgency_question'])->isEqualTo($expected['urgency_question']);
      $this->integer($output['category_rule'])->isEqualTo($expected['category_rule']);
      $this->string($output['category_question'])->isEqualTo($expected['category_question']);
   }

   public function testGetEnumUrgencyRule() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getEnumUrgencyRule();
      $this->array($output)->isEqualTo([
         $testedClass::URGENCY_RULE_NONE      => 'Urgency from template or Medium',
         $testedClass::URGENCY_RULE_SPECIFIC  => 'Specific urgency',
         $testedClass::URGENCY_RULE_ANSWER    => 'Equals to the answer to the question',
      ]);
   }

   public function testGetEnumCategoryRule() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getEnumCategoryRule();
      $this->array($output)->isEqualTo([
         $testedClass::CATEGORY_RULE_NONE          => 'Category from template or none',
         $testedClass::CATEGORY_RULE_SPECIFIC      => 'Specific category',
         $testedClass::CATEGORY_RULE_ANSWER        => 'Equals to the answer to the question',
         $testedClass::CATEGORY_RULE_LAST_ANSWER   => 'Last valid answer',
      ]);
   }

   public function testGetItem_User() {
      $instance = $this->newTestedInstance();
      $output = $this->callPrivateMethod($instance, 'getItem_User');
      $this->object($output)->isInstanceOf(Problem_User::class);
      $this->boolean($output->isNewItem())->isTrue();
   }

   public function testGetItem_Group() {
      $instance = $this->newTestedInstance();
      $output = $this->callPrivateMethod($instance, 'getItem_Group');
      $this->object($output)->isInstanceOf(Group_Problem::class);
      $this->boolean($output->isNewItem())->isTrue();
   }

   public function testGetItem_Supplier() {
      $instance = $this->newTestedInstance();
      $output = $this->callPrivateMethod($instance, 'getItem_Supplier');
      $this->object($output)->isInstanceOf(Problem_Supplier::class);
      $this->boolean($output->isNewItem())->isTrue();
   }

   public function testGetItem_Item() {
      $instance = $this->newTestedInstance();
      $output = $this->callPrivateMethod($instance, 'getItem_Item');
      $this->object($output)->isInstanceOf(Item_Problem::class);
      $this->boolean($output->isNewItem())->isTrue();
   }

   public function testGetCategoryFilter() {
      $instance = $this->newTestedInstance();
      $output = $this->callPrivateMethod($instance, 'getCategoryFilter');
      $this->array($output)->isEqualTo([
         'is_problem' => 1,
      ]);
   }

   public function testGetTaggableFields() {
      $instance = $this->newTestedInstance();
      $output = $this->callPrivateMethod($instance, 'getTaggableFields');
      $this->array($output)->isEqualTo([
         'target_name',
         'content',
         'impactcontent',
         'causecontent',
         'symptomcontent',
      ]);
   }

   public function testGetTargetItemtypeName() {
      $instance = $this->newTestedInstance();
      $output = $this->callPrivateMethod($instance, 'getTargetItemtypeName');
      $this->string($output)->isEqualTo(\Problem::class);
   }

   /**
    *
    * @return void
    */
   public function  testSetTargetEntity() {
      global $CFG_GLPI;

      $testedClass = $this->getTestedClassName();

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $form = $this->getForm();
      $formFk = Form::getForeignKeyField();
      $targetProblem = $this->getTargetProblem([
         $formFk => $form->getID(),
      ]);

      // Use a dummy class to access protected methods
      $instance = $this->newTestedInstance();
      $instance->getFromDB($targetProblem->getID());

      // Test current entity of the requester
      $entity = new Entity();
      $entityId = $entity->import([
         'entities_id' => '0',
         'name' => $this->getUniqueString()
      ]);
      Session::changeActiveEntities($entityId);
      $targetProblem->skipChecks = true;
      $targetProblem->update([
         'id' => $targetProblem->getID(),
         'destination_entity' => $testedClass::DESTINATION_ENTITY_CURRENT,
         'destination_entity_value' => '0',
      ]);
      $targetProblem->skipChecks = false;
      $instance->getFromDB($targetProblem->getID());

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $formAnswer = new FormAnswer();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => $entityId,
      ]);
      $formAnswer->getFromDB($formAnswer->getID());
      $requesterId = Session::getLoginUserID();
      $output = $this->callPrivateMethod($instance, 'setTargetEntity', [], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo($entityId);

      // Test requester's entity
      $targetProblem->skipChecks = true;
      $targetProblem->update([
         'id' => $targetProblem->getID(),
         'destination_entity' => $testedClass::DESTINATION_ENTITY_REQUESTER,
         'destination_entity_value' => '0',
      ]);
      $targetProblem->skipChecks = false;
      $instance->getFromDB($targetProblem->getID());

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $formAnswer = new FormAnswer();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => $entityId,
      ]);
      \Session::changeActiveEntities($entityId);
      $requesterId = Session::getLoginUserID();
      $output = $this->callPrivateMethod($instance, 'setTargetEntity', [], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo(0);

      // Test requester's first entity (alphanumeric order)
      $targetProblem->skipChecks = true;
      $targetProblem->update([
         'id' => $targetProblem->getID(),
         'destination_entity' => $testedClass::DESTINATION_ENTITY_REQUESTER_DYN_FIRST,
         'destination_entity_value' => '0',
      ]);
      $targetProblem->skipChecks = false;
      $instance->getFromDB($targetProblem->getID());
      $entityId = $entity->import([
         'entities_id' => '0',
         'name' => $this->getUniqueString(),
      ]);
      $user = new User();
      $user->add([
         'name' => $this->getUniqueString(),
         'password' => 'passwd',
         'password2' => 'passwd',
         '_profiles_id' => '3', // Admin
         '_entities_id' => $entityId,
      ]);
      $entity = new Entity();
      $profileUser = new \Profile_User();
      // A login resyncs a user. Must login nefore adding the dynamic profile
      $this->boolean($this->login($user->fields['name'], 'passwd'))->isTrue();
      $profileUser->add([
         User::getForeignKeyField()    => $user->getID(),
         Profile::getForeignKeyField() => 4, // Super admin
         Entity::getForeignKeyField()  => $entityId,
         'is_dynamic'                   => '1',
      ]);

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $formAnswer = new FormAnswer();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => 0,
      ]);
      $requesterId = Session::getLoginUserID();
      $output = $this->callPrivateMethod($instance, 'setTargetEntity', [], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo($entityId);

      // Test requester's last entity (alphanumeric order)
      $targetProblem->skipChecks = true;
      $targetProblem->update([
         'id' => $targetProblem->getID(),
         'destination_entity' => $testedClass::DESTINATION_ENTITY_REQUESTER_DYN_LAST,
         'destination_entity_value' => '0',
      ]);
      $targetProblem->skipChecks = false;
      $instance->getFromDB($targetProblem->getID());

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $formAnswer = new FormAnswer();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => $entityId,
      ]);
      $requesterId = \Session::getLoginUserID();
      $output = $this->callPrivateMethod($instance, 'setTargetEntity', [], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo($entityId);

      // Test specific entity
      $this->boolean($this->login('glpi', 'glpi'))->isTrue();
      $entityId = $entity->import([
         'entities_id' => '0',
         'name' => $this->getUniqueString(),
      ]);
      $targetProblem->skipChecks = true;
      $targetProblem->update([
         'id' => $targetProblem->getID(),
         'destination_entity' => $testedClass::DESTINATION_ENTITY_SPECIFIC,
         'destination_entity_value' => "$entityId",
      ]);
      $targetProblem->skipChecks = false;
      $instance->getFromDB($targetProblem->getID());

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $formAnswer = new FormAnswer();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => 0,
      ]);
      $requesterId = \Session::getLoginUserID();
      $output = $this->callPrivateMethod($instance, 'setTargetEntity', [], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo($entityId);

      // Test form's entity
      $entityId = $entity->import([
         'entities_id' => '0',
         'name' => $this->getUniqueString(),
      ]);
      $targetProblem->skipChecks = true;
      $targetProblem->update([
         'id' => $targetProblem->getID(),
         'destination_entity' => $testedClass::DESTINATION_ENTITY_FORM,
         'destination_entity_value' => '0',
      ]);
      $targetProblem->skipChecks = false;
      $form->update([
         'id' => $form->getID(),
         'entities_id' => $entityId,
      ]);
      $instance->getFromDB($targetProblem->getID());

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $formAnswer = new FormAnswer();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => 0,
      ]);
      $requesterId = Session::getLoginUserID();
      $output = $this->callPrivateMethod($instance, 'setTargetEntity', [], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo($entityId);
   }

   public function testExport() {
      $instance = $this->newTestedInstance();

      // Try to export an empty item
      $this->exception(function () use ($instance) {
         $instance->export();
      })->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ExportFailureException::class);

      // Prepare an item to export
      $instance = $this->getTargetProblem();
      $instance->getFromDB($instance->getID());

      // Export the item without the ID and with UUID
      $output = $instance->export(false);

      // Test the exported data
      $fieldsWithoutID = [
         'name',
         'target_name',
         'content',
         'impactcontent',
         'causecontent',
         'symptomcontent',
         'urgency_rule',
         'urgency_question',
         'destination_entity',
         'destination_entity_value',
         'tag_type',
         'tag_questions',
         'tag_specifics',
         'category_rule',
         'category_question',
         'show_rule',
      ];
      $extraFields = [
         '_problemtemplate',
         '_actors',
         'conditions',
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
         'name' => $this->getUniqueString(),
         'target_name' => $this->getUniqueString(),
         '_problemtemplate' => '',
         'content' => $this->getUniqueString(),
         'impactcontent' => $this->getUniqueString(),
         'controlistcontent' => $this->getUniqueString(),
         'rolloutplancontent' => $this->getUniqueString(),
         'backoutplancontent' => $this->getUniqueString(),
         'checklistcontent' => $this->getUniqueString(),
         'due_date_rule' => $testedClass::DUE_DATE_RULE_NONE,
         'due_date_question' => '0',
         'due_date_value' => '',
         'due_date_period' => '',
         'urgency_rule' => $testedClass::URGENCY_RULE_NONE,
         'urgency_question' => '0',
         'validation_followup' => '1',
         'destination_entity' => '0',
         'destination_entity_value' => 0,
         'tag_type' => $testedClass::TAG_TYPE_NONE,
         'tag_questions' => '0',
         'tag_specifics' => '',
         'category_rule' => $testedClass::CATEGORY_RULE_NONE,
         'category_question' => '0',
         'uuid' => $uuid,
      ];

      $linker = new Linker();
      $targetProblemId = $testedClass::import($linker, $input, $form->getID());
      $this->integer($targetProblemId)->isGreaterThan(0);

      unset($input['uuid']);

      $this->exception(
         function() use($linker, $input, $form, $testedClass) {
            $testedClass::import($linker, $input, $form->getID());
         }
      )->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ImportFailureException::class)
         ->hasMessage('UUID or ID is mandatory for Target problem'); // passes

      $input['id'] = $targetProblemId;
      $targetProblemId2 = $testedClass::import($linker, $input, $form->getID());
      $this->integer((int) $targetProblemId)->isNotEqualTo($targetProblemId2);

      $this->newTestedInstance()->delete([
         'id' => $targetProblemId2,
      ]);

      // Check successful link with template
      $templateName = 'problem template ' . $this->getUniqueString();
      $problemTemplate = new ProblemTemplate();
      $problemTemplate->add([
         'name' => $templateName,
         'entities_id' => 0,
         'is_recursive' => 1,
      ]);
      $this->boolean($problemTemplate->isNewItem())->isFalse();
      $input['_problemtemplate'] = $templateName;

      $linker = new Linker();
      $targetProblemId3 = $testedClass::import($linker, $input, $form->getID());
      $this->integer((int) $targetProblemId)->isNotEqualTo($targetProblemId3);
      $targetProblem = $this->newTestedInstance();
      $targetProblem->getFromDB($targetProblemId3);
      $this->integer((int) $targetProblem->fields['problemtemplates_id'])
         ->isEqualTo($problemTemplate->getID());
   }

   public function testIsEntityAssign() {
      $instance = $this->newTestedInstance();
      $this->boolean($instance->isEntityAssign())->isFalse();
   }

   public function testdeleteObsoleteItems() {
      $form = $this->getForm();
      $targetProblem1 = $this->getTargetProblem([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $targetProblem2 = $this->getTargetProblem([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $instance = $this->newTestedInstance();
      $instance->deleteObsoleteItems($form, [$targetProblem2->getID()]);

      $checkDeleted = $this->newTestedInstance();
      $this->boolean($checkDeleted->getFromDB($targetProblem1->getID()))->isFalse();
      $checkDeleted = $this->newTestedInstance();
      $this->boolean($checkDeleted->getFromDB($targetProblem2->getID()))->isTrue();
   }
}
