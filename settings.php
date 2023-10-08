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

/**
 * Snippet settings.
 *
 * @package   tiny_snippet
 * @copyright COPYRIGHTINFO
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
if (is_siteadmin()) {


	$conf = get_config('tiny_snippet');

    //use this to put it all in a category
	$tiny_category='tiny_snippet';
    $ADMIN->add('editortiny', new admin_category($tiny_category, new lang_string('pluginname', 'tiny_snippet')));

    //use this to put it all on one page
   // $tiny_category='editortiny';


    //General settings
    $general_settings = new admin_settingpage('tiny_snippet_settings',get_string('pluginname', 'tiny_snippet'));

    //add basic items to page, snippet count really
    $general_items =  \tiny_snippet\settingstools::fetch_general_items($conf);
    foreach ($general_items as $general_item) {
        $general_settings->add($general_item);
    }

    //add table of templates to page
    $snippettable_item =  new \tiny_snippet\snippettable('tiny_snippet/snippettable',
    get_string('snippets', 'tiny_snippet'), '');
    $general_settings->add($snippettable_item);

    //add page to category
    $ADMIN->add($tiny_category,$general_settings);

    //add Snippets pages to category (hidden from nav)
    $snippet_pages =  \tiny_snippet\settingstools::fetch_snippet_pages($conf);
    foreach ($snippet_pages as $snippet_page) {
        $ADMIN->add($tiny_category, $snippet_page);
    }

    //set the default return to null
    $settings=null;

}//end of if site admin