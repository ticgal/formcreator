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

use AuthLDAP;
use GlpiPlugin\Formcreator\Tests\CommonFunctionalTestCase;
use GlpiPlugin\Formcreator\Tests\CommonQuestionTest;

class LdapselectField extends CommonFunctionalTestCase
{
   use CommonQuestionTest;

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testCreateForm':
         case 'testRenderQuestion':
            break;
      }
   }

   public function testCreateForm() {
      // Use a clean entity for the tests
      $this->login('glpi', 'glpi');

      $form = $this->showCreateQuestionForm();

      $authLdap = new AuthLDAP();
      $authLdap->add([
         'name' => 'LDAP for Formcreator',
         'condition'   => '(&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))',
         'login_field' => 'samaccountname',
         'sync_field'  => 'objectguid',
      ]);
      $this->boolean($authLdap->isNewItem())->isFalse();

      // set question type
      $this->client->executeScript('
         $(\'form[data-itemtype="GlpiPlugin_Formcreator_Question"] [name="fieldtype"]\').val("ldapselect")
         $(\'form[data-itemtype="GlpiPlugin_Formcreator_Question"] [name="fieldtype"]\').select2().trigger("change")
         '
      );

      $this->client->waitForVisibility('form[data-itemtype="GlpiPlugin_Formcreator_Question"] select[name="required"]');
      $this->client->waitForVisibility('form[data-itemtype="GlpiPlugin_Formcreator_Question"] select[name="show_empty"]');
      $this->client->waitForVisibility('form[data-itemtype="GlpiPlugin_Formcreator_Question"] input[name="ldap_filter"]');
      $this->client->waitForVisibility('form[data-itemtype="GlpiPlugin_Formcreator_Question"] select[name="ldap_attribute"]');
      $this->client->waitForVisibility('form[data-itemtype="GlpiPlugin_Formcreator_Question"] select[name="ldap_auth"]');

      $this->browsing->selectInDropdown('form[data-itemtype="GlpiPlugin_Formcreator_Question"] [name="ldap_auth"]', $authLdap->getID(), $authLdap->fields['name']);
      $this->browsing->selectInDropdown('form[data-itemtype="GlpiPlugin_Formcreator_Question"] [name="ldap_attribute"]', 12, '(AD)User ID');

      $this->_testQuestionCreated($form, __METHOD__);
   }
   public function testRenderQuestion() {
      $authLdap = new AuthLDAP();
      $authLdap->add([
         'name' => 'LDAP for Formcreator',
         'condition'   => '(&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))',
         'login_field' => 'samaccountname',
         'sync_field'  => 'objectguid',
      ]);
      $this->boolean($authLdap->isNewItem())->isFalse();

      $this->_testRenderQuestion([
         'fieldtype' => 'ldapselect',
         'ldap_auth' => $authLdap->getID()
      ]);
   }
}
