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

require_once($CFG->libdir . '/adminlib.php');

/**
 * No setting - just heading and text.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class snippettable extends \admin_setting {

    public $visiblename;
    public $information;

    /**
     * not a setting, just text
     * @param string $name unique ascii name, either 'mysetting' for settings that in config, or 'myplugin/mysetting' for ones in config_plugins.
     * @param string $heading heading
     * @param string $information text in box
     */
    public function __construct($name, $visiblename, $information) {
        $this->nosave = true;
        $this->visiblename=$visiblename;
        $this->information=$information;
        parent::__construct($name, $visiblename, $information,'');
    }

    /**
     * Always returns true
     * @return bool Always returns true
     */
    public function get_setting() {
        return true;
    }//end of get_setting

    /**
     * Always returns true
     * @return bool Always returns true
     */
    public function get_defaultsetting() {
        return true;
    }//get_defaultsetting

    /**
     * Never write settings
     * @return string Always returns an empty string
     */
    public function write_setting($data) {
    // do not write any setting
        return '';
    }//write_setting

    /**
     * Returns an HTML string
     * @return string Returns an HTML string
     */
    public function output_html($data, $query='') {
        global $PAGE;
        $conf = get_config('tiny_snippet');
        $snippet_details = self::fetch_snippet_details($conf);



        $table = new \html_table();
        $table->id = 'tiny_snippet_snippet_list';
        $table->head = array(
            get_string('name'),
            get_string('version'),
            get_string('description'),
            get_string('edit')
        );
        $table->headspan = array(1,1,1,1);
        $table->colclasses = array(
            'snippetnamecol','snippetversioncol', 'snippetinstructionscol', 'snippeteditcol'
        );

        //loop through snippets and add to table
        foreach ($snippet_details as $item) {
            $row = new \html_table_row();


            $titlecell = new \html_table_cell($item->title);
            $versioncell = new \html_table_cell($item->version);
            $instructionscell = new \html_table_cell($item->instructions);

            $editlink = \html_writer::link($item->url, get_string('edit'));
            $editcell = new \html_table_cell($editlink);
        /*
            $deleteurl = new \moodle_url($actionurl, array('itemid'=>$item->id,'action'=>'confirmdelete'));
            $deletelink = \html_writer::link($deleteurl, get_string('deleteitem', "local_trigger"));
            $deletecell = new \html_table_cell($deletelink);
        */
            $row->cells = array(
                $titlecell,$versioncell, $instructionscell, $editcell
            );
            $table->data[] = $row;
        }

        $snippet_table= \html_writer::table($table);


		return format_admin_setting($this, $this->visiblename,
			$snippet_table,
			$this->information, true, '','', $query);
	}//end of output html
        


     public static function fetch_snippet_details($conf){
            global $CFG;
			$ret = array();

            //Add the snippet pages
            if($conf && property_exists($conf,'snippetcount')){
                $snippetcount = $conf->snippetcount;
            }else{
                $snippetcount = settingstools::TINY_SNIPPET_SNIPPET_COUNT;
            }

            for($tindex=1;$tindex<=$snippetcount;$tindex++) {
                $snippet_details = new \stdClass();
                $snippet_details->title = \tiny_snippet\settingstools::fetch_snippet_title($conf, $tindex);

                $snippet_details->version = "";
                if(property_exists($conf,'snippetversion_' . $tindex)){
                    $snippet_details->version = $conf->{'snippetversion_' . $tindex};
                }


                $snippet_details->instructions ="";
                if(property_exists($conf,'snippetinstructions_' . $tindex)) {
                    $snippet_details->instructions = $conf->{'snippetinstructions_' . $tindex};
                }

                $snippet_details->url = new \moodle_url( '/admin/settings.php', array('section'=> 'tiny_snippet_snippetpage_' . $tindex));
                $ret[]=$snippet_details;
            }
            return $ret;
		}//end of function
}//end of class