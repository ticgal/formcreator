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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

use GlpiPlugin\Formcreator\Exception\ImportFailureException;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorForm extends CommonDBTM implements
PluginFormcreatorExportableInterface
{
   static $rightname = 'entity';

   public $dohistory         = true;

   const ACCESS_PUBLIC       = 0;
   const ACCESS_PRIVATE      = 1;
   const ACCESS_RESTRICTED   = 2;

   const VALIDATION_NONE     = 0;
   const VALIDATION_USER     = 1;
   const VALIDATION_GROUP    = 2;

   public static function canCreate() {
      return Session::haveRight('entity', UPDATE);
   }

   public static function canView() {
      return Session::haveRight('entity', UPDATE);
   }

   public static function canDelete() {
      return Session::haveRight('entity', UPDATE);
   }

   public static function canPurge() {
      return Session::haveRight('entity', UPDATE);
   }

   public function canPurgeItem() {
      $DbUtil = new DbUtils();

      $criteria = [
         PluginFormcreatorForm::getForeignKeyField() => $this->getID(),
      ];
      if ($DbUtil->countElementsInTable(PluginFormcreatorFormAnswer::getTable(), $criteria) > 0) {
         return false;
      }
      return Session::haveRight('entity', UPDATE);
   }

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return _n('Form', 'Forms', $nb, 'formcreator');
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu  = parent::getMenuContent();
      $validation_image = '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/check.png"
                                title="' . __('Forms waiting for validation', 'formcreator') . '">';
      $import_image     = '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/import.png"
                                title="' . __('Import forms', 'formcreator') . '">';
      $menu['links']['search']          = PluginFormcreatorFormList::getSearchURL(false);
      $menu['links']['config']          = PluginFormcreatorForm::getSearchURL(false);
      $menu['links'][$validation_image] = PluginFormcreatorFormAnswer::getSearchURL(false);
      $menu['links'][$import_image]     = PluginFormcreatorForm::getFormURL(false)."?import_form=1";

      return $menu;
   }

   public function rawSearchOptions() {
      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => __('Characteristics')
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'searchtype'         => 'contains',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'description',
         'name'               => __('Description'),
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __('Entity'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __('Recursive'),
         'datatype'           => 'bool',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'language',
         'name'               => __('Language'),
         'datatype'           => 'specific',
         'searchtype'         => [
            '0'                  => 'equals'
         ],
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'helpdesk_home',
         'name'               => __('Homepage', 'formcreator'),
         'datatype'           => 'bool',
         'searchtype'         => [
            '0'                  => 'equals',
            '1'                  => 'notequals'
         ],
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '9',
         'table'              => $this->getTable(),
         'field'              => 'access_rights',
         'name'               => __('Access', 'formcreator'),
         'datatype'           => 'specific',
         'searchtype'         => [
            '0'                  => 'equals',
            '1'                  => 'notequals'
         ],
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '10',
         'table'              => 'glpi_plugin_formcreator_categories',
         'field'              => 'name',
         'name'               => __('Form category', 'formcreator'),
         'datatype'           => 'dropdown',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '30',
         'table'              => $this->getTable(),
         'field'              => 'is_active',
         'name'               => __('Active'),
         'datatype'           => 'specific',
         'searchtype'         => [
            '0'                  => 'equals',
            '1'                  => 'notequals'
         ],
         'massiveaction'      => true
      ];

      return $tab;
   }

   /**
    * Define how to display search field for a specific type
    *
    * @since version 0.84
    *
    * @param String $field           Name of the field as define in $this->getSearchOptions()
    * @param String $name            Name attribute for the field to be posted (default '')
    * @param Array  $values          Array of all values to display in search engine (default '')
    * @param Array  $options         Options (optional)
    *
    * @return String                 Html string to be displayed for the form field
    */
   public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;

      switch ($field) {
         case 'is_active' :
            return Dropdown::showFromArray($name, [
               '0' => __('Inactive'),
               '1' => __('Active'),
            ], [
               'value'               => $values[$field],
               'display_emptychoice' => false,
               'display'             => false
            ]);
            break;

         case 'access_rights' :
            return Dropdown::showFromArray($name, [
               self::ACCESS_PUBLIC => __('Public access', 'formcreator'),
               self::ACCESS_PRIVATE => __('Private access', 'formcreator'),
               self::ACCESS_RESTRICTED => __('Restricted access', 'formcreator'),
            ], [
               'value'               => $values[$field],
               'display_emptychoice' => false,
               'display'             => false
            ]);
            break;

         case 'language' :
            return Dropdown::showLanguages($name, [
               'value'               => $values[$field],
               'display_emptychoice' => true,
               'emptylabel'          => '--- ' . __('All langages', 'formcreator') . ' ---',
               'display'             => false
            ]);
            break;
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }


   /**
    * Define how to display a specific value in search result table
    *
    * @param  String $field   Name of the field as define in $this->getSearchOptions()
    * @param  Mixed  $values  The value as it is stored in DB
    * @param  Array  $options Options (optional)
    * @return Mixed           Value to be displayed
    */
   public static function getSpecificValueToDisplay($field, $values, array $options = []) {
      global $CFG_GLPI;
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'is_active':
            if ($values[$field] == 0) {
               $output = '<div style="text-align: center"><img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/inactive.png"
                           height="16" width="16"
                           alt="' . __('Inactive') . '"
                           title="' . __('Inactive') . '" /></div>';
            } else {
               $output = '<div style="text-align: center"><img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/active.png"
                           height="16" width="16"
                           alt="' . __('Active') . '"
                           title="' . __('Active') . '" /></div>';
            }
            return $output;
            break;

         case 'access_rights':
            switch ($values[$field]) {
               case self::ACCESS_PUBLIC :
                  return __('Public access', 'formcreator');
                  break;

               case self::ACCESS_PRIVATE :
                  return __('Private access', 'formcreator');
                  break;

               case self::ACCESS_RESTRICTED :
                  return __('Restricted access', 'formcreator');
                  break;
            }
            return '';
            break;

         case 'language' :
            if (empty($values[$field])) {
               return __('All langages', 'formcreator');
            } else {
               return Dropdown::getLanguageName($values[$field]);
            }
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   /**
    * Show the Form for the adminsitrator to edit in the config page
    *
    * @param  Array  $options Optional options
    *
    * @return NULL   Nothing, just display the form
    */
   public function showForm($ID, $options = []) {
      global $DB;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo '<tr class="tab_bg_1">';
      echo '<td width="20%"><strong>' . __('Name') . ' <span class="red">*</span></strong></td>';
      echo '<td width="30%"><input type="text" name="name" value="' . $this->fields["name"] . '" size="35"/></td>';
      echo '<td width="20%"><strong>' . __('Active') . ' <span class="red">*</span></strong></td>';
      echo '<td width="30%">';
      Dropdown::showYesNo("is_active", $this->fields["is_active"]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="tab_bg_2">';
      echo '<td>' . __('Category') . '</td>';
      echo '<td>';
      PluginFormcreatorCategory::dropdown([
         'name'  => 'plugin_formcreator_categories_id',
         'value' => ($ID != 0) ? $this->fields["plugin_formcreator_categories_id"] : 0,
      ]);
      echo '</td>';
      echo '<td>' . __('Direct access on homepage', 'formcreator') . '</td>';
      echo '<td>';
      Dropdown::showYesNo("helpdesk_home", $this->fields["helpdesk_home"]);
      echo '</td>';

      echo '</tr>';

      echo '<tr class="tab_bg_1">';
      echo '<td>' . __('Form icon', 'formcreator') . '</td>';
      echo '<td>';
      PluginFormcreatorCommon::showFontAwesomeDropdown('icon', ['value' => $this->fields['icon']]);
      Html::showColorField('icon_color', ['value' => $this->fields['icon_color']]);
      echo '</td>';
      echo '<td>' . __('Background color', 'formcreator') . '</td>';
      echo '<td>';
      Html::showColorField('background_color', ['value' => $this->fields['background_color']]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="tab_bg_1">';
      echo '<td>' . __('Description') . '</td>';
      echo '<td><input type="text" name="description" value="' . $this->fields['description'] . '" size="35" /></td>';
      echo '<td>' . __('Language') . '</td>';
      echo '<td>';
      Dropdown::showLanguages('language', [
         'value'               => ($ID != 0) ? $this->fields['language'] : $_SESSION['glpilanguage'],
         'display_emptychoice' => true,
         'emptylabel'          => '--- ' . __('All langages', 'formcreator') . ' ---',
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="tab_bg_1">';
      echo '<td>' . _n('Header', 'Headers', 1, 'formcreator') . '</td>';
      echo '<td colspan="3"><textarea name="content" cols="124" rows="10">' . $this->fields["content"] . '</textarea></td>';
      Html::initEditorSystem('content');
      echo '</tr>';

      echo '<tr class="tab_bg_2">';
      echo '<td>' . __('Need to be validate?', 'formcreator') . '</td>';
      echo '<td class="validators_bloc">';

      Dropdown::showFromArray('validation_required', [
         0 => Dropdown::EMPTY_VALUE,
         1 => _n('User', 'Users', 1),
         2 => _n('Group', 'Groups', 1),
      ], [
         'value'     =>  $this->fields["validation_required"],
         'on_change' => 'plugin_formcreator_changeValidators(this.value)'
      ]);
      echo '</td>';
      echo '<td colspan="2">';

      // Select all users with ticket validation right and the groups
      $userTable = User::getTable();
      $userFk = User::getForeignKeyField();
      $groupTable = Group::getTable();
      $groupFk = Group::getForeignKeyField();
      $profileUserTable = Profile_User::getTable();
      $profileTable = Profile::getTable();
      $profileFk = Profile::getForeignKeyField();
      $profileRightTable = ProfileRight::getTable();
      $groupUserTable = Group_User::getTable();
      $subQuery = [
         'SELECT' => "$profileUserTable.$userFk",
         'FROM' => $profileUserTable,
         'INNER JOIN' => [
            $profileTable => [
               'FKEY' => [
                  $profileTable =>  'id',
                  $profileUserTable => $profileFk,
               ]
            ],
            $profileRightTable =>[
               'FKEY' => [
                  $profileTable => 'id',
                  $profileRightTable => $profileFk,
               ]
            ],
         ],
         'WHERE' => [
            "$profileRightTable.name" => "ticketvalidation",
            [
               'OR' => [
                  "$profileRightTable.rights" => ['&', TicketValidation::VALIDATEREQUEST],
                  "$profileRightTable.rights" => ['&', TicketValidation::VALIDATEINCIDENT],
               ],
            ],
            "$userTable.is_active" => '1',
         ],
      ];
      $usersCondition = [
         "$userTable.id" => new QuerySubquery($subQuery)
      ];

      echo '<div id="validators_users">';
      Dropdown::show(
         User::class, [
         'condition' => $usersCondition
         ]
      );
      echo '</div>';

      // Validators groups
      $subQuery = [
         'SELECT' => "$groupUserTable.$groupFk",
         'FROM' => $groupUserTable,
         'INNER JOIN' => [
            $userTable => [
               'FKEY' => [
                  $groupUserTable => $userFk,
                  $userTable => 'id',
               ]
            ],
            $profileUserTable => [
               'FKEY' => [
                  $profileUserTable => $userFk,
                  $userTable => 'id',
               ],
            ],
            $profileTable => [
               'FKEY' => [
                  $profileTable =>  'id',
                  $profileUserTable => $profileFk,
               ]
            ],
            $profileRightTable =>[
               'FKEY' => [
                  $profileTable => 'id',
                  $profileRightTable => $profileFk,
               ]
            ],
         ],
         'WHERE' => [
            "$groupUserTable.$userFk" => new QueryExpression("`$userTable`.`id`"),
            "$profileRightTable.name" => "ticketvalidation",
            [
               'OR' => [
                  "$profileRightTable.rights" => ['&', TicketValidation::VALIDATEREQUEST],
                  "$profileRightTable.rights" => ['&', TicketValidation::VALIDATEINCIDENT],
               ],
            ],
            "$userTable.is_active" => '1',
         ],
      ];
      $groupsCondition = [
         "$groupTable.id" => new QuerySubquery($subQuery),
      ];
      echo '<div id="validators_groups" style="width: 100%">';
      Dropdown::show(
         Group::class, [
         'condition' => $groupsCondition
         ]
      );

      echo '</div>';

      $script = 'function plugin_formcreator_changeValidators(value) {
                  if (value == 1) {
                     document.getElementById("validators_users").style.display  = "block";
                     document.getElementById("validators_groups").style.display = "none";
                  } else if (value == 2) {
                     document.getElementById("validators_users").style.display  = "none";
                     document.getElementById("validators_groups").style.display = "block";
                  } else {
                     document.getElementById("validators_users").style.display  = "none";
                     document.getElementById("validators_groups").style.display = "none";
                  }
               }
               $(document).ready(function() {plugin_formcreator_changeValidators(' . $this->fields["validation_required"] . ');});';
      echo Html::scriptBlock($script);

      echo '</td>';
      echo '</tr>';

      echo '<td>'.__('Default form in service catalog', 'formcreator').'</td>';
      echo '<td>';
      Dropdown::showYesNo("is_default", $this->fields["is_default"]);
      echo '</td>';
      echo '</tr>';

      $this->showFormButtons($options);
   }

   public function showTargets($ID, $options = []) {
      global $CFG_GLPI;

      echo '<table class="tab_cadre_fixe">';

      echo '<tr>';
      echo '<th colspan="3">'._n('Destinations', 'Destinations', 2, 'formcreator').'</th>';
      echo '</tr>';

      $allTargets = $this->getTargetsFromForm();
      $token = Session::getNewCSRFToken();
      $i = 0;
      foreach ($allTargets as $targetType => $targets) {
         foreach ($targets as $targetId => $target) {
            $i++;
            echo '<tr class="line'.($i % 2).'">';
            $targetItemUrl = Toolbox::getItemTypeFormURL($targetType) . '?id=' . $targetId;
            echo '<td onclick="document.location=\'' . $targetItemUrl . '\'" style="cursor: pointer">';

            echo $target->fields['name'];
            echo '</td>';

            echo '<td align="center" width="32">';
            echo '<img src="'.$CFG_GLPI['root_doc'].'/plugins/formcreator/pics/edit.png"
                     alt="*" title="'.__('Edit').'" ';
            echo 'onclick="document.location=\'' . $targetItemUrl . '\'" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';

            echo '<td align="center" width="32">';
            echo '<img src="'.$CFG_GLPI['root_doc'].'/plugins/formcreator/pics/delete.png"
                     alt="*" title="'.__('Delete', 'formcreator').'"
                     onclick="plugin_formcreator_deleteTarget(\''. $target->getType() . '\', '.$targetId.', \''.$token.'\')" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';

            echo '</tr>';
         }
      }

      // Display add target link...
      echo '<tr class="line'.(($i + 1) % 2).'" id="add_target_row">';
      echo '<td colspan="3">';
      echo '<a href="javascript:plugin_formcreator_addTarget('.$ID.', \''.$token.'\');">
                <i class="fa fa-plus" />
                '.__('Add a target', 'formcreator').'
            </a>';
      echo '</td>';
      echo '</tr>';

      // OR display add target form
      echo '<tr class="line'.(($i + 1) % 2).'" id="add_target_form" style="display: none;">';
      echo '<td colspan="3" id="add_target_form_td"></td>';
      echo '</tr>';

      echo "</table>";
   }

   /**
    * Return the name of the tab for item including forms like the config page
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Config Item)
    * @param  integer    $withtemplate
    *
    * @return String                   Name to be displayed
    */
   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      global $DB;

      switch ($item->getType()) {
         case PluginFormcreatorForm::class:
            $nb = 0;
            foreach ($this->getTargetTypes() as $targetType) {
               $nb += (new DbUtils())->countElementsInTable(
                  $targetType::getTable(),
                  [
                     'WHERE' => [
                        'plugin_formcreator_forms_id' => $item->getID(),
                     ]
                  ]
               );
            }
            return [
               1 => self::createTabEntry(
                  _n('Target', 'Targets', Session::getPluralNumber(), 'formcreator'),
                  $nb
               ),
               2 => __('Preview'),
            ];
            break;
         case Central::class:
            return _n('Form', 'Forms', Session::getPluralNumber(), 'formcreator');
            break;
      }
      return '';
   }

   /**
    * Display a list of all forms on the configuration page
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Config Item)
    * @param  integer    $tabnum       Number of the current tab
    * @param  integer    $withtemplate
    *
    * @see CommonDBTM::displayTabContentForItem
    *
    * @return null                     Nothing, just display the list
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $uri = strrchr($_SERVER['HTTP_REFERER'], '/');
      if (strpos($uri, '?')) {
         $uri = substr($uri, 0, strpos($uri, '?'));
      }
      $uri = trim($uri, '/');

      switch ($uri) {
         case "form.form.php":
            switch ($tabnum) {
               case 1:
                  $item->showTargets($item->getID());
                  break;

               case 2:
                  echo '<div style="text-align: left">';
                  $item->displayUserForm($item);
                  echo '</div>';
                  break;
            }
            break;
         case 'central.php':
            $form = new static();
            $form->showForCentral();
            break;
      }
   }


   public function defineTabs($options = []) {
      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(PluginFormcreatorQuestion::class, $ong, $options);
      $this->addStandardTab(PluginFormcreatorForm_Profile::class, $ong, $options);
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab(PluginFormcreatorFormAnswer::class, $ong, $options);
      $this->addStandardTab(Log::class, $ong, $options);
      return $ong;
   }

   /**
    * Show the list of forms to be displayed to the end-user
    */
   public function showList() {
      echo '<div class="center" id="plugin_formcreator_wizard">';

      echo '<div class="plugin_formcreator_marginRight plugin_formcreator_card">';
      $this->showWizard();
      // echo '<hr style="clear:both; height:0; background: transparent; border:none" />';
      echo '</div>';

      echo '<div id="plugin_formcreator_lastForms">';
      $this->showMyLastForms();
      echo '</div>';

      echo '</div>';
   }

   public function showServiceCatalog() {
      echo "<div id='formcreator_servicecatalogue'>";

      // show wizard
      echo '<div id="plugin_formcreator_wizard" class="plugin_formcreator_menuplaceholder">';
      $this->showWizard(true);
      echo '</div>';

      echo '</div>'; // formcreator_servicecatalogue
   }

   public function showWizard($service_catalog = false) {
      echo '<div id="plugin_formcreator_wizard_categories">';
      echo '<div><h2>'._n("Category", "Categories", 2, 'formcreator').'</h2></div>';
      echo '<div><a href="#" id="wizard_seeall">' . __('see all', 'formcreator') . '</a></div>';
      echo '</div>';

      echo '<div id="plugin_formcreator_wizard_right">';

      // hook display central (for alert plugin)
      if ($service_catalog) {
         echo "<div id='plugin_formcreator_display_central'>";
         Plugin::doHook('display_central');
         echo "</div>";
      }

      echo '<div id="plugin_formcreator_searchBar">';
      $this->showSearchBar();
      echo '</div>';
      echo '<div class="plugin_formcreator_sort">';
      echo '<span class="formcreator_radios">';
      echo '<input type="radio" class="form-control" id="plugin_formcreator_mostPopular" name="sort" value="mostPopularSort" />';
      echo '<label for="plugin_formcreator_mostPopular">'.__('Popularity sort', 'formcreator').'</label>';
      echo '</span>';
      echo '<span class="formcreator_radios">';
      echo '<input type="radio" class="form-control" id="plugin_formcreator_alphabetic" name="sort" value="alphabeticSort" />';
      echo '<label for="plugin_formcreator_alphabetic">'.__('Alphabetic sort', 'formcreator').'</label>';
      echo '</span>';
      echo '</div>';
      echo '<div id="plugin_formcreator_wizard_forms">';
      echo '</div>';
      echo '</div>';
   }

   /**
    * Show form and FAQ items
    * @param number $rootCategory Items of this subtree only. 0 = no filtering
    * @param string $keywords Filter items with keywords
    * @param string $helpdeskHome show items for helpdesk only
    */
   public function showFormList($rootCategory = 0, $keywords = '', $helpdeskHome = false) {
      global $DB;

      $table_cat     = getTableForItemType('PluginFormcreatorCategory');
      $table_form    = getTableForItemType('PluginFormcreatorForm');
      $table_fp      = getTableForItemType('PluginFormcreatorForm_Profile');
      $table_section = getTableForItemType('PluginFormcreatorSections');
      $table_question= getTableForItemType('PluginFormcreatorQuestions');

      $order         = "$table_form.name ASC";

      $dbUtils = new DBUtils();
      $where_form = [
         'AND' => [
            "$table_form.is_active" => '1',
            "$table_form.is_deleted" => '0',
            "$table_form.language" => [$_SESSION['glpilanguage'], '0', '', null],
         ] + $dbUtils->getEntitiesRestrictCriteria($table_form, '', '', true, false)
      ];
      if ($helpdeskHome) {
         $where_form['AND']["$table_form.helpdesk_home"] = '1';
      }

      $selectedCategories = [];
      if ($rootCategory != 0) {
         $selectedCategories = getSonsOf($table_cat, $rootCategory);
         $where_form['AND']["$table_form.plugin_formcreator_categories_id"] = $selectedCategories;
      }

      // Find forms accessible by the current user
      if (!empty($keywords)) {
         // Determine the optimal search mode
         $searchMode = "BOOLEAN MODE";
         $formCount = $dbUtils->countElementsInTable($table_form);
         if ($formCount > 20) {
            $searchMode = "NATURAL LANGUAGE MODE";
         } else {
            $keywords = PluginFormcreatorCommon::prepareBooleanKeywords($keywords);
         }
         $keywords = $DB->escape($keywords);
         $highWeightedMatch = " MATCH($table_form.`name`, $table_form.`description`)
               AGAINST('$keywords' IN $searchMode)";
         $lowWeightedMatch = " MATCH($table_question.`name`, $table_question.`description`)
               AGAINST('$keywords' IN $searchMode)";
         $where_form['AND'][] = [
            'OR' => [
               new QueryExpression("$highWeightedMatch"),
               new QueryExpression("$lowWeightedMatch"),
            ]
         ];
      }
      $where_form['AND'][] = [
         'OR' => [
            'access_rights' => ['!=', PluginFormcreatorForm::ACCESS_RESTRICTED],
            [
               "$table_fp.profiles_id" => $_SESSION['glpiactiveprofile']['id']
            ]
         ]
      ];

      $result_forms = $DB->request([
         'SELECT' => [
            $table_form => ['id', 'name', 'icon', 'icon_color', 'background_color', 'description', 'usage_count', 'is_default'],
         ],
         'FROM' => $table_form,
         'LEFT JOIN' => [
            $table_cat => [
               'FKEY' => [
                  $table_cat => 'id',
                  $table_form => PluginFormcreatorCategory::getForeignKeyField(),
               ]
            ],
            $table_section => [
               'FKEY' => [
                  $table_section => PluginFormcreatorForm::getForeignKeyField(),
                  $table_form => 'id',
               ]
            ],
            $table_question => [
               'FKEY' => [
                  $table_question => PluginFormcreatorSection::getForeignKeyField(),
                  $table_section => 'id'
               ]
            ],
            $table_fp => [
               'FKEY' => [
                  $table_fp => PluginFormcreatorForm::getForeignKeyField(),
                  $table_form => 'id',
               ]
            ]
         ],
         'WHERE' => $where_form,
         'GROUPBY' => [
            "$table_form.id",
            "$table_form.name",
            "$table_form.description",
            "$table_form.usage_count",
            "$table_form.is_default"
         ],
         'ORDER' => [
            $order
         ],
      ]);

      $formList = [];
      if ($result_forms->count() > 0) {
         foreach ($result_forms as $form) {
            $formList[] = [
               'id'           => $form['id'],
               'name'         => $form['name'],
               'icon'         => $form['icon'],
               'icon_color'   => $form['icon_color'],
               'background_color'   => $form['background_color'],
               'description'  => $form['description'],
               'type'         => 'form',
               'usage_count'  => $form['usage_count'],
               'is_default'   => $form['is_default'] ? "true" : "false"
            ];
         }
      }

      // Find FAQ entries
      $query_faqs = new QueryExpression('(' . KnowbaseItem::getListRequest([
            'faq'      => '1',
            'contains' => $keywords
      ]) . ') AS `faqs`');
      $query_faqs = [
         'SELECT' => ['faqs' => '*'],
         'FROM' => $query_faqs,
      ];
      if (count($selectedCategories) > 0) {
         $query_faqs['WHERE'] = [
            'knowbaseitemcategories_id' => new QuerySubQuery([
               'SELECT' => 'knowbaseitemcategories_id',
               'FROM' => $table_cat,
               'WHERE' => [
                  'id' => $selectedCategories,
                  'knowbaseitemcategories_id' => ['<>, 0'],
               ],
            ]),
         ];
      } else {
         $query_faqs['INNER JOIN'] = [
            $table_cat => [
               'FKEY' => [
                  'faqs' => 'knowbaseitemcategories_id',
                  $table_cat => 'knowbaseitemcategories_id'
               ]
            ]
         ];
         $query_faqs['WHERE'] = [
            'faqs.knowbaseitemcategories_id' => ['<>', 0],
         ];
      }
      $result_faqs = $DB->request($query_faqs);
      Toolbox::logSqlDebug($result_faqs->getSQL());
      if ($result_faqs->count() > 0) {
         foreach ($result_faqs as $faq) {
            $formList[] = [
               'id'           => $faq['id'],
               'name'         => $faq['name'],
               'description'  => '',
               'type'         => 'faq',
               'usage_count'  => $faq['view'],
               'is_default'   => false
            ];
         }
      }

      if (count($formList) == 0) {
         $defaultForms = true;
         // No form nor FAQ have been selected
         // Fallback to default forms
         $where_form = [
            'AND' => [
               "$table_form.is_active" => '1',
               "$table_form.is_deleted" => '0',
               "$table_form.language" => [$_SESSION['glpilanguage'], '0', '', null],
               "$table_form.is_default" => ['<>', '0']
            ] + $dbUtils->getEntitiesRestrictCriteria($table_form, '', '', true, false),
         ];
         $where_form['AND'][] = [
            'OR' => [
               'access_rights' => ['!=', PluginFormcreatorForm::ACCESS_RESTRICTED],
               "$table_form.id" => new QuerySubQuery([
                  'SELECT' => 'plugin_formcreator_forms_id',
                  'FROM' => $table_fp,
                  'WHERE' => [
                     'profiles_id' => $_SESSION['glpiactiveprofile']['id']
                  ]
               ])
            ]
         ];

         $query_forms = [
            'SELECT' => [
               $table_form => ['id', 'name', 'icon', 'icon_color', 'background_color', 'description', 'usage_count'],
            ],
            'FROM' => $table_form,
            'LEFT JOIN' => [
               $table_cat => [
                  'FKEY' => [
                     $table_cat => 'id',
                     $table_form => PluginFormcreatorCategory::getForeignKeyField(),
                  ]
               ],
            ],
            'WHERE' => $where_form,
            'ORDER' => [
               $order
            ],
         ];
         $result_forms = $DB->request($query_forms);

         if ($result_forms->count() > 0) {
            foreach ($result_forms as $form) {
               $formList[] = [
                  'id'           => $form['id'],
                  'name'         => $form['name'],
                  'icon'         => $form['icon'],
                  'icon_color'   => $form['icon_color'],
                  'background_color'   => $form['background_color'],
                  'description'  => $form['description'],
                  'type'         => 'form',
                  'usage_count'  => $form['usage_count'],
                  'is_default'   => true
               ];
            }
         }
      } else {
         $defaultForms = false;
      }
      return ['default' => $defaultForms, 'forms' => $formList];
   }

   protected function showSearchBar() {
      echo '<form name="formcreator_search" onsubmit="javascript: return false;" >';
      echo '<input type="text" name="words" id="formcreator_search_input" required/>';
      echo '<span id="formcreator_search_input_bar"></span>';
      echo '<label for="formcreator_search_input">'.__('Please, describe your need here', 'formcreator').'</label>';
      echo '</form>';
   }

   protected function showMyLastForms() {
      global $DB;

      $userId = $_SESSION['glpiID'];
      echo '<div class="plugin_formcreator_card">';
      echo '<div class="plugin_formcreator_heading">'.__('My last forms (requester)', 'formcreator').'</div>';
      $formAnswerTable = PluginFormcreatorFormAnswer::getTable();
      $formTable = self::getTable();
      $formFk = self::getForeignKeyField();
      $request = [
         'SELECT' => [
            $formTable => ['name'],
            $formAnswerTable => ['id', 'status', 'request_date'],
         ],
         'FROM' => $formTable,
         'INNER JOIN' => [
            $formAnswerTable => [
               'FKEY' => [
                  $formTable => 'id',
                  $formAnswerTable => self::getForeignKeyField(),
               ]
            ]
         ],
         'WHERE' => [
            "$formAnswerTable.requester_id" => $userId,
            "$formTable.is_deleted" => 0,
         ],
         'ORDER' => [
            "$formAnswerTable.status ASC",
            "$formAnswerTable.request_date DESC",
         ],
         'LIMIT' => 5,
      ];
      $result = $DB->request($request);
      if ($result->count() == 0) {
         echo '<div class="line1" align="center">'.__('No form posted yet', 'formcreator').'</div>';
         echo "<ul>";
      } else {
         foreach ($result as $form) {
               echo '<li class="plugin_formcreator_answer">';
               echo ' <a class="plugin_formcreator_'.$form['status'].'" href="formanswer.form.php?id='.$form['id'].'">'.$form['name'].'</a>';
               echo '<span class="plugin_formcreator_date">'.Html::convDateTime($form['request_date']).'</span>';
               echo '</li>';
         }
         echo "</ul>";
         echo '<div align="center">';
         echo '<a href="formanswer.php?criteria[0][field]=4&criteria[0][searchtype]=equals&criteria[0][value]='.$userId.'">';
         echo __('All my forms (requester)', 'formcreator');
         echo '</a>';
         echo '</div>';
      }
      echo '</div>';

      if (Session::haveRight('ticketvalidation', TicketValidation::VALIDATEINCIDENT)
            || Session::haveRight('ticketvalidation', TicketValidation::VALIDATEREQUEST)) {

         echo '<div class="plugin_formcreator_card">';
         echo '<div class="plugin_formcreator_heading">'.__('My last forms (validator)', 'formcreator').'</div>';
         $groupList = Group_User::getUserGroups($userId);
         $groupIdList = [];
         foreach ($groupList as $group) {
            $groupIdList[] = $group['id'];
         }
         $validatorTable = PluginFormcreatorForm_Validator::getTable();
         $result = $DB->request([
            'SELECT' => [
               $formTable => ['name'],
               $formAnswerTable => ['id', 'status', 'request_date'],
            ],
            'FROM' => $formTable,
            'INNER JOIN' => [
               $formAnswerTable => [
                  'FKEY' => [
                     $formTable => 'id',
                     $formAnswerTable => self::getForeignKeyField(),
                  ]
               ],
               $validatorTable => [
                  'FKEY' => [
                     $validatorTable => $formFk,
                     $formTable => 'id'
                  ]
               ]
            ],
            'WHERE' => [
               'OR' => [
                  [
                     'AND' => [
                        "$formTable.validation_required" => 1,
                        "$validatorTable.itemtype" => User::class,
                        "$validatorTable.items_id" => $userId,
                        "$formAnswerTable.users_id_validator" => $userId
                     ]
                  ],
                  [
                     'AND' => [
                        "$formTable.validation_required" => 2,
                        "$validatorTable.itemtype" => Group::class,
                        "$validatorTable.items_id" => $groupIdList + ['NULL', '0', ''],
                        "$formAnswerTable.groups_id_validator" => $groupIdList + ['NULL', '0', ''],
                     ]
                  ]
               ]
            ],
            'LIMIT' => 5,
         ]);
         if ($result->count() == 0) {
            echo '<div class="line1" align="center">'.__('No form waiting for validation', 'formcreator').'</div>';
         } else {
            echo "<ul>";
            foreach ($result as $form) {
               echo '<li class="plugin_formcreator_answer">';
               echo ' <a class="plugin_formcreator_'.$form['status'].'" href="formanswer.form.php?id='.$form['id'].'">'.$form['name'].'</a>';
               echo '<span class="plugin_formcreator_date">'.Html::convDateTime($form['request_date']).'</span>';
               echo '</li>';
            }
            echo "</ul>";
            echo '<div align="center">';
            $criteria = 'criteria[0][field]=5&criteria[0][searchtype]=equals&criteria[0][value]=' . $_SESSION['glpiID'];
            $criteria.= "&criteria[1][link]=OR"
                      . "&criteria[1][field]=7"
                      . "&criteria[1][searchtype]=equals"
                      . "&criteria[1][value]=mygroups";

            echo '<a href="formanswer.php?' . $criteria . '">';
            echo __('All my forms (validator)', 'formcreator');
            echo '</a>';
            echo '</div>';
         }
         echo '</div>';
      }
   }

   /**
    * Display the Form end-user form to be filled
    *
    * @return Null                     Nothing, just display the form
    */
   public function displayUserForm() {
      global $CFG_GLPI, $DB;

      if (isset($_SESSION['formcreator']['data'])) {
         $data = $_SESSION['formcreator']['data'];
         unset($_SESSION['formcreator']['data']);
      } else {
         $data = null;
      }

      // Print css media
      echo Html::css("plugins/formcreator/css/print_form.css", ['media' => 'print']);

      // Display form
      echo "<form name='form' method='post' role='form' enctype='multipart/form-data'
               action='". $CFG_GLPI['root_doc'] . "/plugins/formcreator/front/form.form.php'
               class='formcreator_form form_horizontal'>";
      echo "<h1 class='form-title'>";
      echo $this->fields['name'] . "&nbsp;";
      echo "<img src='".FORMCREATOR_ROOTDOC."/pics/print.png' class='pointer print_button'
                 title='" . __("Print this form", 'formcreator') . "' onclick='window.print();'>";
      echo '</h1>';

      // Form Header
      if (!empty($this->fields['content'])) {
         echo '<div class="form_header">';
         echo html_entity_decode($this->fields['content']);
         echo '</div>';
      }
      // Get and display sections of the form
      $questions     = [];

      $find_sections = $DB->request([
         'SELECT' => ['id', 'name'],
         'FROM'   => PluginFormcreatorSection::getTable(),
         'WHERE'  => [
            'plugin_formcreator_forms_id' => $this->getID()
         ],
         'ORDER'  => 'order ASC'
      ]);
      echo '<div class="form_section">';
      foreach ($find_sections as $section_line) {
         echo '<h2>' . $section_line['name'] . '</h2>';

         // Display all fields of the section
         $questions = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => PluginFormcreatorQuestion::getTable(),
            'WHERE'  => [
               'plugin_formcreator_sections_id' => $section_line['id']
            ],
            'ORDER'  => 'order ASC'
         ]);
         foreach ($questions as $question_line) {
            $question = new PluginFormcreatorQuestion();
            $question->getFromDB($question_line['id']);
            $field = PluginFormcreatorFields::getFieldInstance(
               $question->fields['fieldtype'],
               $question
            );
            if (!$field->isPrerequisites()) {
               continue;
            }
            if (isset($data['formcreator_field_' . $question_line['id']])) {
               $field->parseAnswerValues($data);
            } else {
               $field->deserializeValue($question->fields['default_values']);
            }
            $field->show();
         }
      }
      echo Html::scriptBlock('$(function() {
         formcreatorShowFields($("form[name=\'form\']"));
      })');

      // Show validator selector
      if ($this->fields['validation_required'] > 0) {
         $table_form_validator = PluginFormcreatorForm_Validator::getTable();
         $validators = [0 => Dropdown::EMPTY_VALUE];

         // Groups
         if ($this->fields['validation_required'] == 2) {
            $groupTable = Group::getTable();
            $formFk = self::getForeignKeyField();
            $result = $DB->request([
               'SELECT' => [
                  $groupTable => ['id', 'completename']
               ],
               'FROM' => $groupTable,
               'LEFT JOIN' => [
                  $table_form_validator => [
                     'FKEY' => [
                        $table_form_validator => 'items_id',
                        $groupTable => 'id'
                     ]
                  ],
               ],
               'WHERE' => [
                  "$table_form_validator.itemtype" => Group::class,
                  "$table_form_validator.$formFk" => $this->getID(),
               ],
            ]);
            foreach ($result as $validator) {
               $validators[$validator['id']] = $validator['completename'];
            }

         } else {
            // Users
            $userTable = User::getTable();
            $result = $DB->request([
               'SELECT' => [
                  $userTable => ['id', 'name', 'realname', 'firstname']
               ],
               'FROM' => $userTable,
               'LEFT JOIN' => [
                  $table_form_validator => [
                     'FKEY' => [
                        $table_form_validator => 'items_id',
                        $userTable => 'id'
                     ]
                  ],
               ],
               'WHERE' => [
                  "$table_form_validator.itemtype" => User::class,
                  "$table_form_validator.$formFk" => $this->getID(),
               ],
            ]);
            foreach ($result as $validator) {
               $validators[$validator['id']] = formatUserName($validator['id'], $validator['name'], $validator['realname'], $validator['firstname']);
            }
         }

         echo '<div class="form-group required liste line' . (count($questions) + 1) % 2 . '" id="form-validator">';
         echo '<label>' . __('Choose a validator', 'formcreator') . ' <span class="red">*</span></label>';
         Dropdown::showFromArray('formcreator_validator', $validators);
         echo '</div>';
      }

      echo '</div>';

      // Display submit button
      echo '<div class="center">';
      echo '<input type="submit" name="submit_formcreator" class="submit_button" value="' . __('Send') . '" />';
      echo '</div>';

      echo '<input type="hidden" name="formcreator_form" value="' . $this->getID() . '">';
      echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
      echo '<input type="hidden" name="uuid" value="' .$this->fields['uuid'] . '">';
      echo '</form>';
   }

   /**
    * Prepare input data for adding the form
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
    */
   public function prepareInputForAdd($input) {
      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      // Control fields values :
      // - name is required
      if (isset($input['name'])) {
         if (empty($input['name'])) {
            Session::addMessageAfterRedirect(
               __('The name cannot be empty!', 'formcreator'),
               false,
               ERROR
            );
            return [];
         }
      }

      if (!isset($input['requesttype'])) {
         $requestType = new RequestType();
         $requestType->getFromDBByCrit(['name' => ['LIKE', 'Formcreator']]);
         $input['requesttype'] = $requestType->getID();
      }

      return $input;
   }

   /**
    * Actions done after the ADD of the item in the database
    *
    * @return void
    */
   public function post_addItem() {
      $this->updateValidators();
      return true;
   }

   /**
    * Prepare input data for updating the form
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
    */
   public function prepareInputForUpdate($input) {
      if (isset($input['access_rights'])
            || isset($_POST['massiveaction'])
            || isset($input['usage_count'])) {

         if (isset($input['access_rights'])
            && $input['access_rights'] == self::ACCESS_PUBLIC
         ) {
            // check that accessibility to the form is compatible with its questions
            $fields = $this->getFields();
            $incompatibleQuestion = false;
            foreach ($fields as $field) {
               if (!$field->isAnonymousFormCompatible()) {
                  $incompatibleQuestion = true;
                  $message = __('The question %s is not compatible with public forms', 'formcreator');
                  Session::addMessageAfterRedirect(sprintf($message, $field->getLabel()), false, ERROR);
               }
            }
            if ($incompatibleQuestion) {
               return [];
            }
         }

         return $input;
      } else {
         $this->updateValidators();
         return $this->prepareInputForAdd($input);
      }
   }

   /**
    * Actions done after the PURGE of the item in the database
    *
    * @return void
    */
   public function post_purgeItem() {
      $associated = [
         PluginFormcreatorTargetTicket::class,
         PluginFormcreatorTargetChange::class,
         PluginFormcreatorSection::class,
         PluginFormcreatorForm_Validator::class,
         PluginFormcreatorForm_Profile::class,
      ];
      foreach ($associated as $itemtype) {
         $item = new $itemtype();
         $item->deleteByCriteria(['plugin_formcreator_forms_id' => $this->getID()]);
      }
   }

   /**
    * Save form validators
    *
    * @return void
    */
   private function updateValidators() {
      if (!isset($this->input['validation_required'])) {
         return;
      }

      $form_validator = new PluginFormcreatorForm_Validator();
      $form_validator->deleteByCriteria(['plugin_formcreator_forms_id' => $this->getID()]);

      if ($this->input['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_USER
          && !empty($this->input['_validator_users'])
          || $this->input['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_GROUP
          && !empty($this->input['_validator_groups'])) {

         switch ($this->input['validation_required']) {
            case PluginFormcreatorForm_Validator::VALIDATION_USER:
               $validators = $this->input['_validator_users'];
               $validatorItemtype = User::class;
               break;
            case PluginFormcreatorForm_Validator::VALIDATION_GROUP:
               $validators = $this->input['_validator_groups'];
               $validatorItemtype = Group::class;
               break;
         }
         foreach ($validators as $itemId) {
            $form_validator = new PluginFormcreatorForm_Validator();
            $form_validator->add([
               'plugin_formcreator_forms_id' => $this->getID(),
               'itemtype'                    => $validatorItemtype,
               'items_id'                    => $itemId
            ]);
         }
      }
   }

   /**
    * Validates answers of a form and saves them in database
    *
    * @param array $input fields from the HTML form
    * @return integer|boolean ID of the formanswer if success, false otherwise
    */
   public function saveForm($input) {
      $valid = true;
      $fieldValidities = [];

      $fields = $this->getFields();
      foreach ($fields as $id => $question) {
         $fieldValidities[$id] = $fields[$id]->parseAnswerValues($input);
      }
      // any invalid field will invalidate the answers
      $valid = !in_array(false, $fieldValidities, true);

      if ($valid) {
         foreach ($fields as $id => $question) {
            if (!$fields[$id]->isPrerequisites()) {
               continue;
            }
            if (PluginFormcreatorFields::isVisible($id, $fields) && !$fields[$id]->isValid()) {
               $valid = false;
               break;
            }
         }
      }

      // Check required_validator
      if ($this->fields['validation_required'] && empty($input['formcreator_validator'])) {
         Session::addMessageAfterRedirect(__('You must select validator!', 'formcreator'), false, ERROR);
         $valid = false;
      }

      if (!$valid) {
         // Save answers in session to display it again with the same values
         $_SESSION['formcreator']['data'] = Toolbox::stripslashes_deep($input);
         return false;
      }

      $formanswer = new PluginFormcreatorFormAnswer();
      return $formanswer->saveAnswers($this, $input, $fields);
   }

   public function increaseUsageCount() {
      // Increase usage count of the form
      $this->update([
            'id' => $this->getID(),
            'usage_count' => $this->getField('usage_count') + 1,
      ]);
   }

   /**
    * gets a form from database from a question
    *
    * @param integer $questionId
    */
   public function getByQuestionId($questionId) {
      $formTable = PluginFormcreatorForm::getTable();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $sectionTable = PluginFormcreatorSection::getTable();
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $questionTable = PluginFormcreatorQuestion::getTable();
      $this->getFromDBByRequest([
         'INNER JOIN' => [
            $sectionTable => [
               'FKEY' => [
                  $formTable    => 'id',
                  $sectionTable => $formFk,
               ]
            ],
            $questionTable => [
               'FKEY' => [
                  $questionTable => $sectionFk,
                  $sectionTable  => 'id'
               ]
            ]
         ],
         'WHERE' => [
            $questionTable . '.id' => $questionId,
         ]
      ]);
   }

   /**
    * Duplicate a from. Execute duplicate action for massive action.
    *
    * NB: Queries are made directly in SQL without GLPI's API to avoid controls made by Add(), prepareInputForAdd(), etc.
    *
    * @return Boolean true if success, false otherwise.
    */
   public function duplicate() {
      $linker = new PluginFormcreatorLinker();

      $export = $this->export(true);
      $new_form_id =  static::import($linker, $export);
      if ($new_form_id === false) {
         return false;
      }
      $newForm = new self();
      $newForm->getFromDB($new_form_id);
      $newName = $newForm->fields['name'] . ' [' . __('Duplicate', 'formcreator') . ']';
      $newForm->update([
         'id' => $new_form_id,
         'name' => Toolbox::addslashes_deep($newName),
      ]);
      $linker->linkPostponed();


      return $new_form_id;
   }

   /**
    * Transfer a form to another entity. Execute transfert action for massive action.
    *
    * @return Boolean true if success, false otherwize.
    */
   public function transfer($entity) {
      global $DB;

      $result = $DB->update(
         self::getTable(),
         [
            Entity::getForeignKeyField() => $entity
         ],
         [
            'id' => $this->getID()
         ]
      );
      return $result;
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    */
   public static function showMassiveActionsSubForm(MassiveAction $ma) {
      switch ($ma->getAction()) {
         case 'Transfert':
            Entity::dropdown([
               'name' => 'entities_id',
            ]);
            echo '<br /><br />' . Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
      }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * Execute massive action for PluginFormcreatorFrom
    *
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      switch ($ma->getAction()) {
         case 'Duplicate' :
            foreach ($ids as $id) {
               if ($item->getFromDB($id) && $item->duplicate() !== false) {
                  Session::addMessageAfterRedirect(sprintf(__('Form duplicated: %s', 'formcreator'), $item->getName()));
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  // Example of ko count
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
            }
            return;
         case 'Transfert' :
            foreach ($ids as $id) {
               if ($item->getFromDB($id) && $item->transfer($ma->POST['entities_id'])) {
                  Session::addMessageAfterRedirect(sprintf(__('Form Transfered: %s', 'formcreator'), $item->getName()));
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  // Example of ko count
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
            }
            return;
         case 'Export' :
            foreach ($ids as $id) {
               if ($item->getFromDB($id)) {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  // Example of ko count
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
            }
            echo "<br>";
            echo "<div class='center'>";
            echo "<a href='#' onclick='window.history.back()'>".__("Back")."</a>";
            echo "</div>";

            $listOfId = ['plugin_formcreator_forms_id' => array_values($ids)];
            Html::redirect(FORMCREATOR_ROOTDOC."/front/export.php?".Toolbox::append_params($listOfId));
            header("Content-disposition:attachment filename=\"test\"");
            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

   public static function countAvailableForm() {
      global $DB;

      $formTable        = PluginFormcreatorForm::getTable();
      $formFk           = PluginFormcreatorForm::getForeignKeyField();
      $formProfileTable = PluginFormcreatorForm_Profile::getTable();

      if ($DB->tableExists($formTable)
          && $DB->tableExists($formProfileTable)
          && isset($_SESSION['glpiactiveprofile']['id'])) {
         $nb = (new DBUtils())->countElementsInTableForMyEntities(
            $formTable,
            [
               'WHERE' => [
                  "$formTable.is_active" => '1',
                  "$formTable.is_deleted" => '0',
                  "$formTable.language" => [$_SESSION['glpilanguage'], '0', '', null],
                  [
                     'OR' => [
                        "$formTable.access_rights" => ['<>', PluginFormcreatorForm::ACCESS_RESTRICTED],
                        "$formTable.id" => new QuerySubQuery([
                           'SELECT' => $formFk,
                           'FROM' => $formProfileTable,
                           'WHERE' => [
                              'profiles_id' => $_SESSION['glpiactiveprofile']['id']
                           ]
                        ]),
                     ],
                  ],
               ],
            ]
         );
      }

      return $nb;
   }

   function export($remove_uuid = false) {
      global $DB;

      if ($this->isNewItem()) {
         return false;
      }

      $formFk        = PluginFormcreatorForm::getForeignKeyField();
      $form           = $this->fields;
      $form_section   = new PluginFormcreatorSection;
      $form_validator = new PluginFormcreatorForm_Validator;
      $form_profile   = new PluginFormcreatorForm_Profile;

      // replace entity id
      $form['_entity']
         = Dropdown::getDropdownName(Entity::getTable(),
                                       $form['entities_id']);

      // replace form category id
      $form['_plugin_formcreator_category'] = '';
      if ($form['plugin_formcreator_categories_id'] > 0) {
         $form['_plugin_formcreator_category']
            = Dropdown::getDropdownName(PluginFormcreatorCategory::getTable(),
                                        $form['plugin_formcreator_categories_id']);
      }

      // remove non needed keys
      unset($form['plugin_formcreator_categories_id'],
            $form['entities_id'],
            $form['usage_count']);

      // restrictions
      $form['_profiles'] = [];
      $all_profiles = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => $form_profile::getTable(),
         'WHERE'  => [
            $formFk => $this->getID()
         ]
      ]);
      foreach ($all_profiles as $profile) {
         $form_profile->getFromDB($profile['id']);
         $form['_profiles'][] = $form_profile->export($remove_uuid);
      }

      // get sections
      $form['_sections'] = [];
      $all_sections = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => $form_section::getTable(),
         'WHERE'  => [
            $formFk => $this->getID()
         ]
      ]);
      foreach ($all_sections as $section) {
         $form_section->getFromDB($section['id']);
         $form['_sections'][] = $form_section->export($remove_uuid);
      }

      // get validators
      $form['_validators'] = [];
      $all_validators = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => $form_validator::getTable(),
         'WHERE'  => [
            'plugin_formcreator_forms_id' => $this->getID()
         ]
      ]);
      foreach ($all_validators as $validator) {
         $form_validator->getFromDB($validator['id']);
         $form['_validators'][] = $form_validator->export($remove_uuid);
      }

      // get targets
      $form['_targets'] = [];
      $all_targets = $this->getTargetsFromForm();
      foreach ($all_targets as $targetType => $targets) {
         foreach ($targets as $target) {
            $form['_targets'][$target->getType()][] = $target->export($remove_uuid);
         }
      }

      // get validators
      $form['_validators'] = [];
      $all_validators = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => $form_validator::getTable(),
         'WHERE'  => [
            $formFk => $this->getID()
         ]
      ]);
      foreach ($all_validators as $validator) {
         $form_validator->getFromDB($validator['id']);
         $form['_validators'][] = $form_validator->export($remove_uuid);
      }

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      }
      unset($form[$idToRemove]);

      return $form;
   }

   /**
    * Display an html form to upload a json with forms data
    */
   public function showImportForm() {
      $documentType = new DocumentType();
      $jsonTypeExists = $documentType->getFromDBByCrit(['ext' => 'json']);
      $jsonTypeEnabled = $jsonTypeExists && $documentType->getField('is_uploadable');
      $canAddType = $documentType->canCreate();
      $canUpdateType = $documentType->canUpdate();

      if (! ($jsonTypeExists && $jsonTypeEnabled)) {
         if (!$jsonTypeExists) {
            $message = __('Upload of JSON files not allowed.', 'formcreator');
            if ($canAddType) {
               $destination = PluginFormcreatorForm::getFormURL();
               $message .= __('You may allow JSON files right now.', 'formcreator');
               $button = Html::submit(_x('button', 'Create', 'formcreator'), ['name' => 'filetype_create']);
            } else {
               $destination = PluginFormcreatorForm::getSearchURL();
               $message .= __('Please contact your GLPI administrator.', 'formcreator');
               $button = Html::submit(_x('button', 'Back', 'formcreator'), ['name' => 'filetype_back']);
            }
         } else {
            $message = __('Upload of JSON files not enabled.', 'formcreator');
            if ($canUpdateType) {
               $destination = PluginFormcreatorForm::getFormURL();
               $message .= __('You may enable JSON files right now.', 'formcreator');
               $button = Html::submit(_x('button', 'Enable', 'formcreator'), ['name' => 'filetype_enable']);
            } else {
               $message .= __('You may enable JSON files right now.', 'formcreator');
               $message .= __('Please contact your GLPI administrator.', 'formcreator');
               $button = Html::submit(_x('button', 'Back', 'formcreator'), ['name' => 'filetype_back']);
            }
         }
         echo '<div class="spaced" id="tabsbody">';
         echo "<form name='form' method='post' action='". $destination."'>";
         echo '<table class="tab_cadre_fixe" id="mainformtable">';
         echo '<tr class="headerRow">';
         echo '<th>';
         echo __('Import forms');
         echo '</th>';
         echo '</tr>';
         echo '<tr>';
         echo '<td class="center">';
         echo $message;
         echo '</td>';
         echo '</tr>';
         echo '<td class="center">';
         echo $button;
         echo '</td>';
         echo '</tr>';
         echo '<tr>';
         echo '</table>';
         echo '</div>';
         Html::closeForm();
         echo '</div>';
      } else {
         echo "<form name='form' method='post' action='".
               PluginFormcreatorForm::getFormURL().
               "?import_send=1' enctype=\"multipart/form-data\">";

         echo "<div class='spaced' id='tabsbody'>";
         echo "<table class='tab_cadre_fixe' id='mainformtable'>";
         echo "<tr class='headerRow'>";
         echo "<th>";
         echo __("Import forms");
         echo "</th>";
         echo "</tr>";
         echo "<tr>";
         echo "<td>";
         echo Html::file(['name' => 'json_file']);
         echo "</td>";
         echo "</tr>";
         echo "<td class='center'>";
         echo Html::submit(_x('button', 'Send'), ['name' => 'import_send']);
         echo "</td>";
         echo "</tr>";
         echo "<tr>";
         echo "</table>";
         echo "</div>";

         Html::closeForm();
      }
   }

   /**
    * Process import of json file(s) sended by the submit of self::showImportForm
    * @param  array  $params GET/POST data that need to contain the filename(s) in _json_file key
    */
   public function importJson($params = []) {
      // parse json file(s)
      foreach ($params['_json_file'] as $filename) {
         if (!$json = file_get_contents(GLPI_TMP_DIR."/".$filename)) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file is empty"));
            continue;
         }
         if (!$forms_toimport = json_decode($json, true)) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file seems corrupt"));
            continue;
         }
         if (!isset($forms_toimport['forms'])) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file seems corrupt"));
            continue;
         }

         foreach ($forms_toimport['forms'] as $form) {
            set_time_limit(30);
            $linker = new PluginFormcreatorLinker();
            try {
               self::import($linker, $form);
            } catch (ImportFailureException $e) {
               // Import failed, give up
               continue;
            }
            if (!$linker->linkPostponed()) {
               Session::addMessageAfterRedirect(sprintf(__("Failed to import %s", "formcreator"),
                                                           $$form['name']));
            }
         }
         Session::addMessageAfterRedirect(sprintf(__("Forms successfully imported from %s", "formcreator"),
                                                      $filename));
      }
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException('UUID or ID is mandatory');
      }

      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $item = new self();
      // Find an existing form to update, only if an UUID is available
      $itemId = false;
      /** @var string $idKey key to use as ID (id or uuid) */
      $idKey = 'id';
      if (isset($input['uuid'])) {
         // Try to find an existing item to update
         $idKey = 'uuid';
         $itemId = plugin_formcreator_getFromDBByField(
            $item,
            'uuid',
            $input['uuid']
         );
      }

      // Set entity of the form
      $entity = new Entity();
      $entityFk = Entity::getForeignKeyField();
      $entityId = $_SESSION['glpiactive_entity'];
      if (isset($input['_entity'])) {
         plugin_formcreator_getFromDBByField(
            $entity,
            'completename',
            $input['_entity']
         );
         // Check rights on the destination entity of the form
         if (!$entity->isNewItem() && $entity->canUpdateItem()) {
            $entityId = $entity->getID();
         } else {
            if ($itemId !== false) {
               // The form is in an entity where we don't have UPDATE right
               Session::addMessageAfterRedirect(
                  sprintf(__('The form %1$s already exists and is in an unmodifiable entity.', 'formcreator'), $input['name']),
                  false,
                  WARNING
               );
               throw new ImportFailureException('failed to add or update the item');
            } else {
               // The form is in an entity which does not exists yet
               Session::addMessageAfterRedirect(
                  sprintf(__('The entity %1$s is required for the form %2$s.', 'formcreator'), $input['_entity'], $input['name']),
                  false,
                  WARNING
               );
               throw new ImportFailureException('failed to add or update the item');
            }
         }
      }
      $input[$entityFk] = $entityId;

      // Import form category
      $formCategory = new PluginFormcreatorCategory();
      $formCategoryFk = PluginFormcreatorCategory::getForeignKeyField();
      $formCategoryId = 0;
      if ($input['_plugin_formcreator_category'] != '') {
         $formCategoryId = $formCategory->import([
            'completename' => Toolbox::addslashes_deep($input['_plugin_formcreator_category']),
         ]);
      }
      $input[$formCategoryFk] = $formCategoryId;

      // Escape text fields
      foreach (['name', 'description', 'content'] as $key) {
         $input[$key] = $DB->escape($input[$key]);
      }

      // Add or update the form
      $originalId = $input[$idKey];
      if ($itemId !== false) {
         $input['id'] = $itemId;
         $item->update($input);
      } else {
         unset($input['id']);
         $itemId = $item->add($input);
      }
      if ($itemId === false) {
         throw new ImportFailureException('failed to add or update the item');
      }

      // add the form to the linker
      $linker->addObject($originalId, $item);

      // import form_profiles
      if (isset($input['_profiles'])) {
         $importedItems = [];
         foreach ($input['_profiles'] as $formProfile) {
            $importedItem = PluginFormcreatorForm_Profile::import(
               $linker,
               $formProfile,
               $itemId
            );
            if ($importedItem === false) {
               // Falied to import a form_profile
               return false;
            }
            $importedItems[] = $importedItem;
         }
         // Delete all other restrictions
         if (count($importedItems) > 0) {
            $FormProfile = new PluginFormcreatorForm_Profile();
            $FormProfile->deleteByCriteria([
               $formFk => $itemId,
               ['NOT' => ['id' => $importedItems]]
            ]);
         }
      }

      // import form's sections
      if (isset($input['_sections'])) {
         // sort questions by order
         usort($input['_sections'], function ($a, $b) {
            if ($a['order'] == $b['order']) {
               return 0;
            }
            return ($a['order'] < $b['order']) ? -1 : 1;
         });

         // Import each section
         $importedItems = [];
         foreach ($input['_sections'] as $section) {
            $importedItem = PluginFormcreatorSection::import(
               $linker,
               $section,
               $itemId
            );
            if ($importedItem === false) {
               // Falied to import a section
               return false;
            }
            $importedItems[] = $importedItem;
         }
         // Delete all other restrictions
         $FormProfile = new PluginFormcreatorSection();
         $FormProfile->deleteByCriteria([
            $formFk => $itemId,
            ['NOT' => ['id' => $importedItems]]
         ]);
      }

      // import form's targets
      if (isset($input['_targets'])) {
         foreach ((new self())->getTargetTypes() as $targetType) {
            // import targets
            $importedItems = [];
            if (isset($input['_targets'][$targetType])) {
               foreach ($input['_targets'][$targetType] as $targetData) {
                  $importedItem = $targetType::import(
                     $linker,
                     $targetData,
                     $itemId
                  );
                  if ($importedItem === false) {
                     // Falied to import a section
                     return false;
                  }
                  $importedItems[] = $importedItem;
               }
            }
            // delete other targets of the itemtype $targetType
            if (count($importedItems)) {
               $target = new $targetType();
               $target->deleteByCriteria([
                  $formFk => $itemId,
                  ['NOT' => ['id' => $importedItems]]
               ]);
            }
         }
      }

      // Import validators
      if (isset($input['_validators'])) {
         $importedItems = [];
         foreach ($input['_validators'] as $validator) {
            $importedItem = PluginFormcreatorForm_Validator::import(
               $linker,
               $validator,
               $itemId
            );
            if ($importedItem === false) {
               // Failed to import a section
               return false;
            }
            $importedItems[] = $importedItem;
         }
         if (count($importedItems)) {
            $form_validator = new PluginFormcreatorForm_Validator;
            $form_validator->deleteByCriteria([
               $formFk => $itemId,
               ['NOT' => ['id' => $importedItems]]
            ]);
         }
      }

      return $itemId;
   }

   public function createDocumentType() {
      $documentType = new DocumentType();
      $success = $documentType->add([
         'name'            => 'JSON file',
         'ext'             => 'json',
         'icon'            => '',
         'is_uploadable'   => '1'
      ]);
      if (!$success) {
         Session::addMessageAfterRedirect(__('Failed to create JSON document type', 'formcreator'));
      }
   }

   public function enableDocumentType() {
      $documentType = new DocumentType();
      if (!$documentType->getFromDBByCrit(['ext' => 'json'])) {
         Session::addMessageAfterRedirect(__('JSON document type not found', 'formcreator'));
      } else {
         $success = $documentType->update([
            'id'              => $documentType->getID(),
            'is_uploadable'   => '1'
         ]);
         if (!$success) {
            Session::addMessageAfterRedirect(__('Failed to update JSON document type', 'formcreator'));
         }
      }
   }

   /**
    * show list of available forms
    */
   public function showForCentral() {
      global $DB, $CFG_GLPI;

      // Define tables
      $cat_table        = PluginFormcreatorCategory::getTable();
      $categoryFk       = PluginFormcreatorCategory::getForeignKeyField();
      $form_table       = PluginFormcreatorForm::getTable();
      $formFk           = PluginFormcreatorForm::getForeignKeyField();
      $table_fp         = PluginFormcreatorForm_Profile::getTable();
      $formProfileFk    = PluginFormcreatorForm_Profile::getForeignKeyField();
      $entitiesRestrict  = (new DBUtils())->getEntitiesRestrictCriteria($form_table, '', '', true, false);
      $language   = $_SESSION['glpilanguage'];

      // Show form whithout category
      $formCategoryFk = PluginFormcreatorCategory::getForeignKeyField();
      $result_forms = $DB->request([
         'SELECT' => [
            $form_table => ['id', 'name', 'description']
         ],
         'FROM' => $form_table,
         'WHERE' => [
            "$form_table.$formCategoryFk" => 0,
            "$form_table.is_active" => 1,
            "$form_table.is_deleted" => 0,
            "$form_table.hesldesk_home" => 1,
            "$form_table.language" => [0, '', null, $language],
            [
               'OR' => [
                  'acess_rights' => ['<>', PluginFormcreatorForm::ACCESS_RESTRICTED],
                  "$form_table.id" => new QuerySubQuery([
                     'SELECT' => $formProfileFk,
                     'FROM' => $table_fp,
                     'WHERE' => [
                        'profiles_id' => $_SESSION['glpiactiveprofile']['id']
                     ],
                  ]),
               ]
            ]
         ] + $entitiesRestrict,
         'ORDER' => "$form_table.name ASC",
      ]);

      // Show categories which have at least one form user can access
      $result = $DB->request([
         'SELECT' => [
            $cat_table => [
               'name', 'id'
            ]
         ],
         'FROM' => $cat_table,
         'INNER JOIN' => [
            $form_table => [
               'FKEY' => [
                  $cat_table => 'id',
                  $form_table => $categoryFk
               ]
            ]
         ],
         'WHERE' => [
            "$form_table.is_active" => 1,
            "$form_table.is_deleted" => 0,
            "$form_table.helpdesk_home" => 1,
            "$form_table.language" => [$language, 0, null, ''],
            [
               'OR' => [
                  "$form_table.access_rights" => ['<>', PluginFormcreatorForm::ACCESS_RESTRICTED],
                  "$form_table.'id'" => new QuerySubQuery([
                     'SELECT' => $formFk,
                     'FROM' => $table_fp,
                     'WHERE' => [
                        'profiles_id' => $_SESSION['glpiactiveprofile']['id']
                     ]
                  ]),
               ],
            ],
         ] + $entitiesRestrict,
         'GROUPBY' => [
            "$cat_table.id"
         ]
      ]);
      if ($result->count() > 0 || $result_forms->count() > 0) {
         echo '<table class="tab_cadrehov homepage_forms_container" id="homepage_forms_container">';
         echo '<tr class="noHover">';
         echo '<th><a href="../plugins/formcreator/front/formlist.php">' . _n('Form', 'Forms', 2, 'formcreator') . '</a></th>';
         echo '</tr>';

         if ($result_forms->count() > 0) {
            echo '<tr class="noHover"><th>' . __('Forms without category', 'formcreator') . '</th></tr>';
            $i = 0;
            foreach ($result_forms as $form) {
               $i++;
               echo '<tr class="line' . ($i % 2) . ' tab_bg_' . ($i % 2 +1) . '">';
               echo '<td>';
               echo '<img src="' . $CFG_GLPI['root_doc'] . '/pics/plus.png" alt="+" title=""
                   onclick="showDescription(' . $form['id'] . ', this)" align="absmiddle" style="cursor: pointer">';
               echo '&nbsp;';
               echo '<a href="' . $CFG_GLPI['root_doc']
               . '/plugins/formcreator/front/formdisplay.php?id=' . $form['id'] . '"
                  title="' . $form['description'] . '">'
                              . $form['name']
                              . '</a></td>';
                              echo '</tr>';
                              echo '<tr id="desc' . $form['id'] . '" class="line' . ($i % 2) . ' form_description">';
                              echo '<td><div>' . $form['description'] . '&nbsp;</div></td>';
                              echo '</tr>';
            }
         }

         if ($result->count() > 0) {
            // For each categories, show the list of forms the user can fill
            $i = 0;
            foreach ($result as $category) {
               $categoryId = $category['id'];
               echo '<tr class="noHover"><th>' . $category['name'] . '</th></tr>';
               $result_forms = $DB->request([
                  'SELECT' => [
                     $form_table => ['id', 'name', 'description'],
                  ],
                  'FROM' => $form_table,
                  'WHERE' => [
                     $categoryFk => [$categoryId],
                     "$form_table.is_active" => 1,
                     "$form_table.is_deleted" => 0,
                     "$form_table.helpdesk_home" => 1,
                     "$form_table.language" => [$language, 0, null, ''],
                     [
                        'OR' => [
                           "$form_table.access_rights" => ['<>', PluginFormcreatorForm::ACCESS_RESTRICTED],
                           "$form_table.'id'" => new QuerySubQuery([
                              'SELECT' => $formFk,
                              'FROM' => $table_fp,
                              'WHERE' => [
                                 'profiles_id' => $_SESSION['glpiactiveprofile']['id']
                              ]
                           ]),
                        ],
                     ],
                  ] + $entitiesRestrict,
                  'ORDER' => [
                     "$form_table.name ASC",
                  ]
               ]);
               $i = 0;
               foreach ($result_forms as $form) {
                  $i++;
                  echo '<tr class="line' . ($i % 2) . ' tab_bg_' . ($i % 2 +1) . '">';
                  echo '<td>';
                  echo '<img src="' . $CFG_GLPI['root_doc'] . '/pics/plus.png" alt="+" title=""
                      onclick="showDescription(' . $form['id'] . ', this)" align="absmiddle" style="cursor: pointer">';
                  echo '&nbsp;';
                  echo '<a href="' . $CFG_GLPI['root_doc']
                  . '/plugins/formcreator/front/formdisplay.php?id=' . $form['id'] . '"
                     title="' . $form['description'] . '">'
                                 . $form['name']
                                 . '</a></td>';
                                 echo '</tr>';
                                 echo '<tr id="desc' . $form['id'] . '" class="line' . ($i % 2) . ' form_description">';
                                 echo '<td><div>' . $form['description'] . '&nbsp;</div></td>';
                                 echo '</tr>';
               }
            }
         }
         echo '</table>';
         echo '<br />';
         echo '<script type="text/javascript">
            function showDescription(id, img){
               if(img.alt == "+") {
                 img.alt = "-";
                 img.src = "' . $CFG_GLPI['root_doc'] . '/pics/moins.png";
                 document.getElementById("desc" + id).style.display = "table-row";
               } else {
                 img.alt = "+";
                 img.src = "' . $CFG_GLPI['root_doc'] . '/pics/plus.png";
                 document.getElementById("desc" + id).style.display = "none";
               }
            }
         </script>';
      }
   }

   static function getInterface() {
      if (isset($_SESSION['glpiactiveprofile']['interface'])
            && ($_SESSION['glpiactiveprofile']['interface'] == 'helpdesk')) {
         if (plugin_formcreator_replaceHelpdesk()) {
            return 'servicecatalog';
         }
         return 'self-service';
      }
      if (!empty($_SESSION['glpiactiveprofile'])) {
         return 'central';
      }

      return 'public';
   }

   static function header() {
      switch (self::getInterface()) {
         case "servicecatalog";
            return PluginFormcreatorWizard::header(__('Service catalog', 'formcreator'));
         case "self-service";
            return Html::helpHeader(__('Form list', 'formcreator'), $_SERVER['PHP_SELF']);
         case "central";
            return Html::header(
               __('Form Creator', 'formcreator'),
               $_SERVER['PHP_SELF'],
               'helpdesk',
               'PluginFormcreatorFormlist'
            );
         case "public";
         default:
            return Html::nullHeader(__('Form Creator', 'formcreator'), $_SERVER['PHP_SELF']);
      }
   }

   /**
    * Gets the footer HTML
    *
    * @return string HTML to show a footer
    */
   static function footer() {
      switch (self::getInterface()) {
         case "servicecatalog";
            return PluginFormcreatorWizard::footer();
         case "self-service";
            return Html::helpFooter();
         case "central";
            return Html::footer();
         case "public";
         default:
            return Html::nullFooter();
      }
   }

   /**
    * Is the form accessible anonymously (without being logged in) ?
    * @return boolean true if the form is accessible anonymously
    */
   public function isPublicAccess() {
      if ($this->isNewItem()) {
         return false;
      }
      return ($this->getField('access_rights') === (string) \PluginFormcreatorForm::ACCESS_PUBLIC);
   }

   /**
    * gets the form containing the given section
    *
    * @param PluginFormcreatorSection $section
    * @return boolean true if success else false
    */
   public function getFromDBBySection(PluginFormcreatorSection $section) {
      if ($section->isNewItem()) {
         return false;
      }
      return $this->getFromDB($section->getField(self::getForeignKeyField()));
   }

   /**
    * Get an array of instances of all fields for the form
    *
    * @return array
    */
   public function getFields() {
      $fields = [];
      if ($this->isNewItem()) {
         return $fields;
      }

      $question = new PluginFormcreatorQuestion();
      $found_questions = $question->getQuestionsFromForm($this->getID());
      foreach ($found_questions as $id => $question) {
         $fields[$id] = PluginFormcreatorFields::getFieldInstance(
            $question->fields['fieldtype'],
            $question
         );
      }

      return $fields;
   }

   /**
    * Get supported target itemtypes
    *
    * @return array
    */
   public function getTargetTypes() {
      return [
         PluginFormcreatorTargetTicket::class,
         PluginFormcreatorTargetChange::class
      ];
   }

   /**
    * get all targets associated to the form
    *
    * @param integer $formId
    * @return array
    */
   public function getTargetsFromForm() {
      global $DB;

      $targets = [];
      if ($this->isNewItem()) {
         return [];
      }

      foreach ($this->getTargetTypes() as $targetType) {
         $request = [
            'SELECT' => 'id',
            'FROM' => $targetType::getTable(),
            'WHERE' => [
               self::getForeignKeyField() => $this->getID(),
            ]
         ];
         foreach ($DB->request($request) as $row) {
            $target = new $targetType();
            $target->getFromDB($row['id']);
            $targets[$targetType][$row['id']] = $target;
         }
      }

      return $targets;
   }

   public  function showAddTargetForm() {
      echo '<form name="form_target" method="post" action="'.static::getFormURL().'">';
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="4">'.__('Add a target', 'formcreator').'</th></tr>';

      echo '<tr class="line1">';
      echo '<td width="15%"><strong>'.__('Name').' <span style="color:red;">*</span></strong></td>';
      echo '<td width="40%"><input type="text" name="name" style="width:100%;" value="" /></td>';
      echo '<td width="15%"><strong>'._n('Type', 'Types', 1).' <span style="color:red;">*</span></strong></td>';
      echo '<td width="30%">';
      $targetTypes = [];
      foreach ($this->getTargetTypes() as $targetType) {
         $targetTypes[$targetType] = $targetType::getTypeName(1);
      }
      Dropdown::showFromArray(
         'itemtype',
         $targetTypes,
         [
            'display_emptychoice' => true
         ]
      );
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td colspan="4" class="center">';
      echo '<input type="hidden" name="plugin_formcreator_forms_id" value="'.(int) $_REQUEST['form_id'].'" />';
      echo '<input type="submit" name="add_target" class="submit_button" value="'.__('Add').'" />';
      echo '</td>';
      echo '</tr>';

      echo '</table>';
      Html::closeForm();
   }

   /**
    * Add a target item to the form
    *
    * @param string $input
    * @return integer|false ID of the new item or false on error
    */
   public function addTarget($input) {
      $itemtype = $input['itemtype'];
      if (!in_array($itemtype, $this->getTargetTypes())) {
         Session::addMessageAfterRedirect(
            __('Unsupported target type.', 'formcreator'),
            false,
            ERROR
         );
         return false;
      }

      // Check the form exists
      $form = new self();
      if (!$form->getFromDB($input[self::getForeignKeyField()])) {
         // The linked form does not exists
         Session::addMessageAfterRedirect(
            __('The form does not exists.', 'formcreator'),
            false,
            ERROR
         );
         return false;
      }

      // Create the target
      $item = new $itemtype();
      unset($input['itemtype']);
      return $item->add($input);
   }

   /**
    * Delete a target fromfor the form
    *
    * @param aray $input
    * @return boolean
    */
   public function deleteTarget($input) {
      $itemtype = $input['itemtype'];
      if (!in_array($itemtype, $this->getTargetTypes())) {
         Session::addMessageAfterRedirect(
            __('Unsuported target type.', 'formcreator'),
            false,
            ERROR
         );
         return false;
      }

      $item = new $itemtype();
      $item->delete(['id' => $input['items_id']]);
      return true;
   }
}
