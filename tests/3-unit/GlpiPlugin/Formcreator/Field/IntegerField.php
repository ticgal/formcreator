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

namespace tests\units\GlpiPlugin\Formcreator\Field;

use GlpiPlugin\Formcreator\Condition;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class IntegerField extends CommonTestCase {

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Integer');
   }

   public function providerIsValid() {
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ],
            'expectedValue'   => '',
            'expectedValidity' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '2',
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ],
            'expectedValue'   => '2',
            'expectedValidity' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "2",
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => 3,
                        'range_max' => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ],
            'expectedValue'   => '2',
            'expectedValidity' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "5",
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => 3,
                        'range_max' => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ],
            'expectedValue'   => '5',
            'expectedValidity' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "3.4",
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => 3,
                        'range_max' => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ],
            'expectedValue'   => '3.4',
            'expectedValidity' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "4",
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => 3,
                        'range_max' => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ],
            'expectedValue'   => '4',
            'expectedValidity' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => ['regex' => '/[0-9]{2}\\\\.[0-9]{3}\\\\.[0-9]{3}\\\\/[0-9]{4}-[0-9]{2}/'],
                  ]
               ],
            ],
            'expectedValue'   => '4',
            'expectedValidity' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => ['regex' => '/[0-9]{2}\\\\.[0-9]{3}\\\\.[0-9]{3}\\\\/[0-9]{4}-[0-9]{2}/'],
                  ]
               ],
            ],
            'expectedValue'   => '4',
            'expectedValidity' => true
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider providerIsValid
    */
   public function testIsValid($fields, $expectedValue, $expectedValidity) {
      $section = $this->getSection();
      $fields[$section::getForeignKeyField()] = $section->getID();

      $question = $this->getQuestion($fields);

      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($fields['default_values']);

      $isValid = $instance->isValid();
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);
   }

   public function testisPublicFormCompatible() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isPublicFormCompatible();
      $this->boolean($output)->isTrue();
   }

   public function testIsPrerequisites() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isPrerequisites();
      $this->boolean($output)->isEqualTo(true);
   }

   public function testCanRequire() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->canRequire();
      $this->boolean($output)->isTrue();
   }

   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }

   public function providerGetValueForApi() {
      return [
         [
            'input'    => '42',
            'expected' => '42',
         ],
      ];
   }

   /**
    * @dataProvider providerGetValueForApi
    *
    * @return void
    */
   public function testGetValueForApi($input, $expected) {
      $question = $this->getQuestion([
         'itemtype' => Location::class
      ]);

      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($input);
      $output = $instance->getValueForApi();
      $this->string($output)->isEqualTo($expected);
   }
}
