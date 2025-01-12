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
use GlpiPlugin\Formcreator\Question;
use GlpiPlugin\Formcreator\QuestionRange;
use GlpiPlugin\Formcreator\QuestionRegex;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class FloatField extends CommonTestCase {

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Decimal number');
   }

   public function provider() {
      $dataset = [
         'empty value' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => '',
                        'range_max'       => '',
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ]
            ],
            'expectedValue'   => '',
            'expectedValidity' => true,
            'expectedMessage' => '',
         ],
         'integer value' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '2',
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => '',
                        'range_max'       => '',
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ]
            ],
            'expectedValue'   => '2',
            'expectedValidity' => true,
            'expectedMessage' => '',
         ],
         'too low value' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "2",
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => 3,
                        'range_max'       => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ]
            ],
            'expectedValue'   => '2',
            'expectedValidity' => false,
            'expectedMessage' => 'The following number must be greater than 3: question',
          ],
         'too high value' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "5",
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => 3,
                        'range_max'       => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ]
            ],
            'expectedValue'   => '5',
            'expectedValidity' => false,
            'expectedMessage' => 'The following number must be lower than 4: question',
         ],
         'float iin range' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "3.141592",
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => 3,
                        'range_max'       => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ]
            ],
            'expectedValue'   => '3.141592',
            'expectedValidity' => true,
            'expectedMessage' => '',
         ],
         'empty value and regex with backslash' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "",
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => '',
                        'range_max'       => '',
                     ],
                     'regex' => ['regex' => '/[0-9]{2}\\\\.[0-9]{3}/'],
                  ]
               ]
            ],
            'expectedValue'   => '',
            'expectedValidity' => true,
            'expectedMessage' => '',
         ],
         'value not matching regex' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "1.234",
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => '',
                        'range_max'       => '',
                     ],
                     'regex' => ['regex' => '/[0-9]{2}\\\\.[0-9]{3}/'],
                  ]
               ]
            ],
            'expectedValue'   => '',
            'expectedValidity' => false,
            'expectedMessage' => 'Specific format does not match: question',
         ],
         'value matching regex' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "12.345",
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => '',
                        'range_max'       => '',
                     ],
                     'regex' => ['regex' => '/[0-9]{2}\\\\.[0-9]{3}/'],
                  ]
               ]
            ],
            'expectedValue'   => '',
            'expectedValidity' => true,
            'expectedMessage' => '',
         ],
         [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "",
               'order'           => '1',
               'show_rule'       => Condition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => '',
                        'range_max'       => '',
                     ],
                     'regex' => ['regex' => '/[0-9]{2}\\\\.[0-9]{3}\\\\.[0-9]{3}\\\\/[0-9]{4}-[0-9]{2}/'],
                  ]
               ]
            ],
            'expectedValue'   => '',
            'expectedValidity' => true,
            'expectedMessage' => '',
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testIsValid($fields, $expectedValue, $expectedValidity, $expectedMessage) {
      $section = $this->getSection();
      $fields[$section::getForeignKeyField()] = $section->getID();

      $question = new Question();
      $question->add($fields);
      $this->boolean($question->isNewItem())->isFalse(json_encode($_SESSION['MESSAGE_AFTER_REDIRECT'], JSON_PRETTY_PRINT));

      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($fields['default_values']);
      $_SESSION["MESSAGE_AFTER_REDIRECT"] = [];

      $isValid = $instance->isValid();
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);

      // Check error message
      if (!$isValid) {
         $this->sessionHasMessage($expectedMessage, ERROR);
      } else {
         $this->sessionHasNoMessage();
      }
   }

   public function testGetEmptyParameters() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->getEmptyParameters();
      $this->array($output)
         ->hasKey('range')
         ->hasKey('regex')
         ->array($output)->size->isEqualTo(2);
      $this->object($output['range'])
         ->isInstanceOf(QuestionRange::class);
      $this->object($output['regex'])
         ->isInstanceOf(QuestionRegex::class);
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
            'input'    => '3.14',
            'expected' => '3.14',
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
      ]);

      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($input);
      $output = $instance->getValueForApi();
      $this->string($output)->isEqualTo($expected);
   }
}
