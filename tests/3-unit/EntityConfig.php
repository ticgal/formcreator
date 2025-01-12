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

use Entity;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;
use Ticket;

class EntityConfig extends CommonTestCase {
   public function providerGetTabNameForItem() {
      return [
         [
            new Entity,
            ['1' => 'Forms'],
         ],
         [
            new Ticket,
            [],
         ]
      ];
   }

   /**
    * @dataProvider providerGetTabNameForItem
    */
   public function testGetTabNameForItem($item, $expected) {
      $instance = $this->newTestedInstance();
      $output = $instance->getTabNameForItem($item);
      $this->array($output)->isIdenticalTo($expected);
   }

   public function testGetUsedConfig() {
      $this->login('glpi', 'glpi');

      // Create an entity with 2 sub entities
      $base = 'Root entity > ' . $this->getUniqueString();
      $entity = new Entity();
      $entityId = $entity->add([
         'name' => $this->getUniqueString(),
         'entities_id' => '0',
      ]);
      $this->boolean($entity->isNewID($entityId))->isFalse();

      $entityId1 = $entity->add([
         'name' => "$base > a",
         'entities_id' => $entityId,
      ]);
      $this->boolean($entity->isNewID($entityId1))->isFalse();

      $entityId2 = $entity->add([
         'name' => "b",
         'entities_id' => $entityId1,
      ]);
      $this->boolean($entity->isNewID($entityId2))->isFalse();

      $entityId3 = $entity->add([
         'name' => "c",
         'entities_id' => $entityId,
      ]);
      $this->boolean($entity->isNewID($entityId3))->isFalse();

      // Set configuration for the 2 sub entities
      $testedClass = $this->getTestedClassName();
      $instance = $this->newTestedInstance();
      $instance->add([
         'entities_id'         => $entityId,
         'replace_helpdesk'    => $testedClass::CONFIG_EXTENDED_SERVICE_CATALOG,
         'is_kb_separated'     => $testedClass::CONFIG_KB_MERGED,
         'sort_order'          => $testedClass::CONFIG_SORT_ALPHABETICAL,
         'home_page'           => $testedClass::CONFIG_HOME_PAGE_ONLY_FORM,
         'is_category_visible' => $testedClass::CONFIG_CATEGORY_VISIBLE,
         'is_folded_menu'      => $testedClass::CONFIG_LEFT_MENU_FOLDED,
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      $instance = $this->newTestedInstance();
      $instance->add([
         'entities_id'         => $entityId1,
         'replace_helpdesk'    => $testedClass::CONFIG_SIMPLIFIED_SERVICE_CATALOG,
         'is_kb_separated'     => $testedClass::CONFIG_KB_MERGED,
         'sort_order'          => $testedClass::CONFIG_SORT_ALPHABETICAL,
         'home_page'           => $testedClass::CONFIG_HOME_PAGE_ONLY_FORM,
         'is_category_visible' => $testedClass::CONFIG_CATEGORY_VISIBLE,
         'is_folded_menu'      => $testedClass::CONFIG_LEFT_MENU_FOLDED,
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      $instance = $this->newTestedInstance();
      $instance->add([
         'entities_id'         => $entityId2,
         'replace_helpdesk'    => $testedClass::CONFIG_EXTENDED_SERVICE_CATALOG,
         'is_kb_separated'     => $testedClass::CONFIG_KB_DISTINCT,
         'sort_order'          => $testedClass::CONFIG_SORT_POPULARITY,
         'home_page'           => $testedClass::CONFIG_HOME_PAGE_FORM_AND_REQUEST,
         'is_category_visible' => $testedClass::CONFIG_CATEGORY_HIDDEN,
         'is_folded_menu'      => $testedClass::CONFIG_LEFT_MENU_UNFOLDED,
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      $instance = $this->newTestedInstance();
      $instance->add([
         'entities_id'         => $entityId3,
         'replace_helpdesk'    => $testedClass::CONFIG_PARENT,
         'is_kb_separated'     => $testedClass::CONFIG_PARENT,
         'sort_order'          => $testedClass::CONFIG_PARENT,
         'homepage'            => $testedClass::CONFIG_PARENT,
         'is_category_visible' => $testedClass::CONFIG_PARENT,
         'is_folded_menu'      => $testedClass::CONFIG_PARENT,
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      // Test settings of entities
      $output = $instance::getUsedConfig('replace_helpdesk', $entityId1);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_SIMPLIFIED_SERVICE_CATALOG);
      $output = $instance::getUsedConfig('is_kb_separated', $entityId1);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_KB_MERGED);
      $output = $instance::getUsedConfig('sort_order', $entityId1);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_SORT_ALPHABETICAL);
      $output = $instance::getUsedConfig('home_page', $entityId1);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_HOME_PAGE_ONLY_FORM);
      $output = $instance::getUsedConfig('is_category_visible', $entityId1);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_CATEGORY_VISIBLE);
      $output = $instance::getUsedConfig('is_folded_menu', $entityId1);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_LEFT_MENU_FOLDED);

      $output = $instance::getUsedConfig('replace_helpdesk', $entityId2);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_EXTENDED_SERVICE_CATALOG);
      $output = $instance::getUsedConfig('is_kb_separated', $entityId2);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_KB_DISTINCT);
      $output = $instance::getUsedConfig('sort_order', $entityId2);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_SORT_POPULARITY);
      $output = $instance::getUsedConfig('home_page', $entityId2);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_HOME_PAGE_FORM_AND_REQUEST);
      $output = $instance::getUsedConfig('is_category_visible', $entityId2);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_CATEGORY_HIDDEN);
      $output = $instance::getUsedConfig('is_folded_menu', $entityId2);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_LEFT_MENU_UNFOLDED);

      $output = $instance::getUsedConfig('replace_helpdesk', $entityId3);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_EXTENDED_SERVICE_CATALOG);
      $output = $instance::getUsedConfig('is_kb_separated', $entityId3);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_KB_MERGED);
      $output = $instance::getUsedConfig('sort_order', $entityId3);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_SORT_ALPHABETICAL);
      $output = $instance::getUsedConfig('home_page', $entityId3);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_HOME_PAGE_ONLY_FORM);
      $output = $instance::getUsedConfig('is_category_visible', $entityId3);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_CATEGORY_VISIBLE);
      $output = $instance::getUsedConfig('is_folded_menu', $entityId3);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_LEFT_MENU_FOLDED);

      // Check change on parent entity propagates to child with inherited settings
      $instance = $this->newTestedInstance();
      $instance->getFromDBByCrit(['entities_id' => $entityId]);
      $instance->update([
         'id'               => $instance->getID(),
         'replace_helpdesk' => $testedClass::CONFIG_SIMPLIFIED_SERVICE_CATALOG,
         'is_kb_separated'  => $testedClass::CONFIG_KB_DISTINCT,
         'sort_order'       => $testedClass::CONFIG_SORT_POPULARITY,
         'home_page'       => $testedClass::CONFIG_HOME_PAGE_ONLY_FORM,
         'is_folded_menu'       => $testedClass::CONFIG_LEFT_MENU_FOLDED,
      ]);

      $output = $instance::getUsedConfig('replace_helpdesk', $entityId3);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_SIMPLIFIED_SERVICE_CATALOG);
      $output = $instance::getUsedConfig('is_kb_separated', $entityId3);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_KB_DISTINCT);
      $output = $instance::getUsedConfig('sort_order', $entityId3);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_SORT_POPULARITY);
      $output = $instance::getUsedConfig('home_page', $entityId3);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_HOME_PAGE_ONLY_FORM);
      $output = $instance::getUsedConfig('is_folded_menu', $entityId3);
      $this->integer((int) $output)->isEqualTo($testedClass::CONFIG_LEFT_MENU_FOLDED);
   }

   public function testGetEnumHelpdeskMode() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getEnumHelpdeskMode();
      $this->array($output)->isEqualTo([
         $testedClass::CONFIG_PARENT                     => __('Inheritance of the parent entity'),
         $testedClass::CONFIG_GLPI_HELPDSK               => __('GLPi\'s helpdesk', 'formcreator'),
         $testedClass::CONFIG_SIMPLIFIED_SERVICE_CATALOG => __('Service catalog simplified', 'formcreator'),
         $testedClass::CONFIG_EXTENDED_SERVICE_CATALOG   => __('Service catalog extended', 'formcreator'),
      ]);
   }

   public function testGetEnumSort() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getEnumSort();
      $this->array($output)->isEqualTo([
         $testedClass::CONFIG_PARENT            => __('Inheritance of the parent entity'),
         $testedClass::CONFIG_SORT_POPULARITY   => __('Popularity sort', 'formcreator'),
         $testedClass::CONFIG_SORT_ALPHABETICAL => __('Alphabetic sort', 'formcreator'),
      ]);
   }

   public function testGetEnumKbMode() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getEnumKbMode();
      $this->array($output)->isEqualTo([
         $testedClass::CONFIG_PARENT      => __('Inheritance of the parent entity'),
         $testedClass::CONFIG_KB_MERGED   => __('Merged with Forms', 'formcreator'),
         $testedClass::CONFIG_KB_DISTINCT => __('Distinct menu entry', 'formcreator'),
      ]);
   }

   public function testGetEnumSearchVisibility() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getEnumSearchVisibility();
      $this->array($output)->isEqualTo([
         $testedClass::CONFIG_PARENT         => __('Inheritance of the parent entity'),
         $testedClass::CONFIG_SEARCH_VISIBLE => __('Visible', 'formcreator'),
         $testedClass::CONFIG_SEARCH_HIDDEN  => __('Hidden', 'formcreator'),
      ]);
   }

   public function testGetEnumHeaderVisibility() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getEnumheaderVisibility();
      $this->array($output)->isEqualTo([
         $testedClass::CONFIG_PARENT         => __('Inheritance of the parent entity'),
         $testedClass::CONFIG_HEADER_VISIBLE => __('Visible', 'formcreator'),
         $testedClass::CONFIG_HEADER_HIDDEN  => __('Hidden', 'formcreator'),
      ]);
   }

   public function testGetEnumDashboardVisibility() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getEnumheaderVisibility();
      $this->array($output)->isEqualTo([
         $testedClass::CONFIG_PARENT            => __('Inheritance of the parent entity'),
         $testedClass::CONFIG_DASHBOARD_VISIBLE => __('Visible', 'formcreator'),
         $testedClass::CONFIG_DASHBOARD_HIDDEN  => __('Hidden', 'formcreator'),
      ]);
   }

   public function testGvetEnumHomePage() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getEnumHomePage();
      $this->array($output)->isEqualTo([
         $testedClass::CONFIG_PARENT                       => __('Inheritance of the parent entity'),
         $testedClass::CONFIG_HOME_PAGE_ONLY_FORM          => __('Only forms', 'formcreator'),
         $testedClass::CONFIG_HOME_PAGE_FORM_AND_REQUEST   => __('Forms and list of requests', 'formcreator'),
      ]);
   }

   public function getEnumCategoryVisibility() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getEnumHomePage();
      $this->array($output)->isEqualTo([
         $testedClass::CONFIG_PARENT            => __('Inheritance of the parent entity'),
         $testedClass::CONFIG_CATEGORY_VISIBLE  => __('Visible', 'formcreator'),
         $testedClass::CONFIG_CATEGORY_HIDDEN   => __('Hidden', 'formcreator'),
      ]);
   }

   public function getEnumLeftMenuVisibility() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getEnumHomePage();
      $this->array($output)->isEqualTo([
         $testedClass::CONFIG_PARENT              => __('Inheritance of the parent entity'),
         $testedClass::CONFIG_LEFT_MENU_FOLED     => __('Folded', 'formcreator'),
         $testedClass::CONFIG_LEFT_MENU_UNFOLED   => __('Unfolded', 'formcreator'),
      ]);
   }
}
