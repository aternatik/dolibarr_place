<?php
/* Module to manage locations, buildings, floors and rooms into Dolibarr ERP/CRM
 * Copyright (C) 2013-2014	Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup	place	Place module
 * 	\brief		Place module descriptor.
 * 	\file		core/modules/modPlace.class.php
 * 	\ingroup	place
 * 	\brief		Description and activation file for module Place
 */
include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";

/**
 * Description and activation class for module Place
 */
class modPlace extends DolibarrModules
{

	/**
	 * 	Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * 	@param	DoliDB		$db	Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;

		$this->db = $db;

		// Id for module (must be unique).
		// Use a free id here
		// (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 110110;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'place';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "hr";
		// Module label (no space allowed)
		// used if translation string 'ModuleXXXName' not found
		// (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description
		// used if translation string 'ModuleXXXDesc' not found
		// (where XXX is value of numeric property 'numero' of module)
		$this->description = "Place managment with resource module";
		// Possible values for version are: 'development', 'experimental' or version
		$this->version = '3.7+0.7';
		// Key used in llx_const table to save module status enabled/disabled
		// (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
		// Where to store the module in setup page
		// (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png
		// use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png
		// use this->picto='pictovalue@module'
		$this->picto = 'place@place'; // mypicto@place
		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /place/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /place/core/modules/barcode)
		// for specific css file (eg: /place/css/place.css.php)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory
			//'triggers' => 1,
			// Set this to 1 if module has its own login method directory
			//'login' => 0,
			// Set this to 1 if module has its own substitution function file
			//'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory
			//'menus' => 0,
			// Set this to 1 if module has its own barcode directory
			//'barcode' => 0,
			// Set this to 1 if module has its own models directory
			//'models' => 0,
			// Set this to relative path of css if module has its own css file
			'css' => '/place/css/place.css.php',
			// Set here all hooks context managed by module
			'hooks' => array('actioncard','actioncommdao','element_resource')
			// Set here all workflow context managed by module
			//'workflow' => array('order' => array('WORKFLOW_ORDER_AUTOCREATE_INVOICE'))
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/place/temp");
		$this->dirs = array("/place","place/building","/place/temp");

		// Config pages. Put here list of php pages
		// stored into place/admin directory, used to setup module.
		$this->config_page_url = array("admin_place.php@place");

		// Dependencies
		// List of modules id that must be enabled if this module is enabled
		$this->depends = array('modResource');
		// List of modules id to disable if this one is disabled
		$this->requiredby = array();
		// Minimum version of PHP required by module
		$this->phpmin = array(5, 3);
		// Minimum version of Dolibarr required by module
		$this->need_dolibarr_version = array(3, 6);
		$this->langfiles = array("place@place"); // langfiles@place
		// Constants
		// List of particular constants to add when module is enabled
		// (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example:
		$this->const = array(
			0=>array(
					'PLACE_DEFAULT_ZOOM_FOR_MAP',
					'chaine',
					'1',
					'This is a constant to defined default zoom into link to OSM map',
					1
			),
		    1=>array(
		        'PLACE_ADDON_PDF_ODT_PATH',
		        'chaine',
		        'DOL_DATA_ROOT/doctemplates/place',
		        '',
		        1
		    )

		);
		// Array to add new pages in new tabs
		// Example:
		$this->tabs = array(
			
		);
		

		$this->tabs = array(
			//'actioncomm:+resouces:Resource:place@place:$user->rights->place->read:/place/actioncom_resources.php?id=__ID__'
		);
        
        // This is to avoid warnings
		if (! isset($conf->place->enabled)) $conf->place->enabled=0;
		
		// Dictionnaries
		$this->dictionaries=array(
			'langs'=>'place@place',
			'tabname'=>array(MAIN_DB_PREFIX."c_placeroom_type"),
			'tablib'=>array("PlaceRoomDictType"),
			'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.pos, f.label, f.active, f.use_default FROM '.MAIN_DB_PREFIX.'c_placeroom_type as f'),
			'tabsqlsort'=>array("pos ASC"),
			'tabfield'=>array("pos,code,label,use_default"),
			'tabfieldvalue'=>array("pos,code,label,use_default"),
			'tabfieldinsert'=>array("pos,code,label,use_default"),
			'tabrowid'=>array("rowid"),
			'tabcond'=>array($conf->place->enabled)
		);

		// Boxes
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
		$this->boxes = array(); // Boxes list
		$r = 0;
		// Example:

		//$this->boxes[$r][1] = "MyBox@place";
		//$r ++;
		/*
		  $this->boxes[$r][1] = "myboxb.php";
		  $r++;
		 */

		// Permissions
		$this->rights = array(); // Permission array used by this module
		$r = 0;

		$this->rights[$r][0] = 1101101;
		$this->rights[$r][1] = 'See places';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'read';
		$r++;

		$this->rights[$r][0] = 1101102;
		$this->rights[$r][1] = 'Modify places';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'write';
		$r++;

		$this->rights[$r][0] = 1101103;
		$this->rights[$r][1] = 'Delete places';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'delete';
		$r++;

		// Main menu entries
		$this->menu = array(); // List of menus to add
		$r = 0;

		// Menus declaration
		$this->menu[$r]=array(
			'fk_menu'=>'fk_mainmenu=tools',
			'type'=>'left',
			'titre'=> 'Menu110110PlaceIndex',
			'mainmenu'=>'tools',
			'leftmenu'=> 'place',
			'url'=> '/place/index.php',
			'langs'=> 'place@place',
			'position'=> 100,
			'enabled'=> '1',
			'perms'=> '$user->rights->place->read',
			'user'=> 0
		);
		$r++;


		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=tools,fk_leftmenu=place', //On utilise les ancres définis dans le menu parent déclaré au dessus
			'type'=> 'left', // Toujours un menu gauche
			'titre'=> 'Menu110110PlaceAdd',
			'mainmenu'=> 'tools',
			'leftmenu'=> '', // On n'indique rien ici car on ne souhaite pas intégrer de sous-menus à ce menu
			'url'=> '/place/add.php',
			'langs'=> 'place@place',
			'position'=> 101,
			'enabled'=> '1',
			'perms'=> '$user->rights->place->read',
			'target'=> '',
			'user'=> 0
		);

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=tools,fk_leftmenu=place', //On utilise les ancres définis dans le menu parent déclaré au dessus
			'type'=> 'left', // Toujours un menu gauche
			'titre'=> 'Menu110110BuildingsList',
			'mainmenu'=> 'tools',
			'leftmenu'=> '', // On n'indique rien ici car on ne souhaite pas intégrer de sous-menus à ce menu
			'url'=> '/place/building/list.php',
			'langs'=> 'place@place',
			'position'=> 102,
			'enabled'=> '1',
			'perms'=> '$user->rights->place->read',
			'target'=> '',
			'user'=> 0
		);
        
        $this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=tools,fk_leftmenu=place', //On utilise les ancres définis dans le menu parent déclaré au dessus
			'type'=> 'left', // Toujours un menu gauche
			'titre'=> 'Menu110110RoomsList',
			'mainmenu'=> 'tools',
			'leftmenu'=> '', // On n'indique rien ici car on ne souhaite pas intégrer de sous-menus à ce menu
			'url'=> '/place/room/list.php',
			'langs'=> 'place@place',
			'position'=> 103,
			'enabled'=> '1',
			'perms'=> '$user->rights->place->read',
			'target'=> '',
			'user'=> 0
		);

		// Exports
		$r = 1;

	}

	/**
	 * Function called when module is enabled.
	 * The init function add constants, boxes, permissions and menus
	 * (defined in constructor) into Dolibarr database.
	 * It also creates data directories
	 *
	 * 	@param		string	$options	Options when enabling module ('', 'noboxes')
	 * 	@return		int					1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		$sql = array();

		$result = $this->loadTables();

		return $this->_init($sql, $options);
	}

	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * 	@param		string	$options	Options when enabling module ('', 'noboxes')
	 * 	@return		int					1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}

	/**
	 * Create tables, keys and data required by module
	 * Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * and create data commands must be stored in directory /place/sql/
	 * This function is called by this->init
	 *
	 * 	@return		int		<=0 if KO, >0 if OK
	 */
	private function loadTables()
	{
		return $this->_load_tables('/place/sql/');
	}
}
