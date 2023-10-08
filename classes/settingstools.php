<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tiny_snippet;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');


/**
 *
 * This is a class containing static functions for general snippet filter things
 * like embedding recorders and managing them
 *
 * @package   tiny_snippet
 * @since      Moodle 3.2
 * @copyright  2016 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class settingstools
{
 const TINY_SNIPPET_SNIPPET_COUNT = 5;

public static function fetch_general_items($conf){

	$items = array();
	
	$items[]= new \admin_setting_configtext('tiny_snippet/snippetcount',
				get_string('snippetcount', 'tiny_snippet'),
				get_string('snippetcount_desc', 'tiny_snippet'),
				 self::TINY_SNIPPET_SNIPPET_COUNT, PARAM_INT,20);

    return $items;

}//end of function fetch widget items

    //make a readable snippet name for menus and lists etc
    public static function fetch_snippet_title($conf,$tindex){
        //snippet display name
        $tname='';
        if($conf && property_exists($conf,'snippetname_' . $tindex)){
            $tname = $conf->{'snippetname_' . $tindex};
        }
        if(empty($tname) && $conf && property_exists($conf,'snippetkey_' . $tindex)){
            $tname = $conf->{'snippetkey_' . $tindex};
        }
        if(empty($tname)){$tname=$tindex;}
        $snippettitle = get_string('snippetpageheading', 'tiny_snippet',$tname);

        return $snippettitle;
    }

public static function fetch_snippet_pages($conf){
		$pages = array();

		//Add the snippet pages
		if($conf && property_exists($conf,'snippetcount')){
			$snippetcount = $conf->snippetcount;
		}else{
			$snippetcount = self::TINY_SNIPPET_SNIPPET_COUNT;
		}
                
        //fetch preset data, just once so we do nto need to repeat the call a zillion times
        $presetdata = snippetpresets::fetch_presets();
                
		for($sindex=1;$sindex<=$snippetcount;$sindex++){
		 
			 //snippet display name
             $sname='';
            if($conf && property_exists($conf,'snippetname_' . $sindex)){
				$sname = $conf->{'snippetname_' . $sindex};
            }
			if(empty($sname) && $conf && property_exists($conf,'snippetkey_' . $sindex)){
				$sname = $conf->{'snippetkey_' . $sindex};
        	}
			if(empty($sname)){$sname= get_string('snippet', 'tiny_snippet') . ' ' . $sindex;}
			$snippettitle = get_string('snippetpageheading', 'tiny_snippet',$sname);
		 
			 //snippet settings Page Settings 
			$settings_page = new \admin_settingpage('tiny_snippet_snippetpage_' . $sindex,$snippettitle,'moodle/site:config',true);
                   

			$settings_page->add(new \admin_setting_heading('tiny_snippet/snippetheading_' . $sindex,
				get_string('snippet', 'tiny_snippet') . ' ' . $sindex, ''));
			$settings_page->add(new snippetpresets('tiny_snippet/snippetpresets_' . $sindex,
					get_string('presets', 'tiny_snippet'), get_string('presets_desc', 'tiny_snippet'),$sindex,$presetdata));
			$settings_page->add(new \admin_setting_configtext('tiny_snippet/snippetname_' . $sindex,
				get_string('snippetname', 'tiny_snippet'), '', '', PARAM_RAW));
			$settings_page->add(new \admin_setting_configtext('tiny_snippet/snippetkey_' . $sindex,
				get_string('snippetkey', 'tiny_snippet'), '', '', PARAM_TEXT));
			$settings_page->add(new \admin_setting_configtextarea('tiny_snippet/snippetinstructions_' . $sindex,
				get_string('snippetinstructions', 'tiny_snippet'), '', '', PARAM_RAW));
			$settings_page->add(new \admin_setting_configtextarea('tiny_snippet/snippet_' . $sindex,
				get_string('snippet', 'tiny_snippet'), '', '', PARAM_RAW));
			$settings_page->add(new \admin_setting_configtextarea('tiny_snippet/defaults_' . $sindex,
				get_string('defaults', 'tiny_snippet'), '', '', PARAM_RAW));
			$settings_page->add(new \admin_setting_configtext('tiny_snippet/snippetversion_' . $sindex,
				get_string('snippetversion', 'tiny_snippet') . ' ' . $sindex , '', '1.0.0', PARAM_TEXT));
              
			$pages[] = $settings_page;
		}

		return $pages;
	}//end of function fetch snippet pages

}//end of class
