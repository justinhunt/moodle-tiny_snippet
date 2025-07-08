<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Tiny Snippet for TinyMCE plugin for Moodle.
 *
 * @package     tiny_snippet
 * @copyright   2023 Justin Hunt <justin@poodll.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tiny_snippet;

use context;
use editor_tiny\plugin;
use editor_tiny\plugin_with_buttons;
use editor_tiny\plugin_with_menuitems;
use editor_tiny\plugin_with_configuration;


class plugininfo extends plugin implements plugin_with_configuration, plugin_with_buttons, plugin_with_menuitems {

    public static function get_available_buttons(): array {
        return [
            'tiny_snippet/plugin',
        ];
    }

    public static function get_available_menuitems(): array {
        return [
            'tiny_snippet/plugin',
        ];
    }

    public static function get_plugin_configuration_for_context(
        context $context,
        array $options,
        array $fpoptions,
        ?\editor_tiny\editor $editor = null
    ): array {

        global $COURSE,$USER;

        $config = get_config(constants::M_COMPONENT);
        $params=[];


        if (!$context) {
            $context = \context_course::instance($COURSE->id);
        }

        $disabled = false;
        //If they don't have permission don't show it
        if (!has_capability('tiny/snippet:visible', $context)) {
            $disabled = true;
        }
        $params['disabled'] = $disabled;

        // If the poodle filter plugin is installed and enabled, add widgets to the toolbar.
        $snippetconfig = get_config('tiny_snippet');
        if ($snippetconfig->version) {
            $widgets = self::get_params_for_js();
            $params['widgets'] = $widgets;
        }else{
            $params['widgets'] = [];
        }

        return ['snippet'=>$params];
    }

    /**
     * Return the js params required for this module.
     *
     * @return array of additional params to pass to javascript init function for this module.
     */
    private static function get_params_for_js() {
        global $USER, $COURSE;

        //init our return value
        $widgets=[];

        //coursecontext
        $coursecontext=\context_course::instance($COURSE->id);

        //snippet specific
        //this has to be established. It will basically be an array of regular expressions
        //each with a title.
        $conf=get_config('tiny_snippet');
        $snippets = get_object_vars($conf);

        //Get the snippet count
        if($conf && property_exists($conf,'snippetcount')){
            $snippetcount = $conf->snippetcount;
        }else{
            $snippetcount = settingstools::TINY_SNIPPET_SNIPPET_COUNT;
        }

        //put our template into a form thats easy to process in JS
        for($snippetindex=1;$snippetindex<$snippetcount+1;$snippetindex++) {
            if (empty($snippets['snippet_' . $snippetindex])) {
                continue;
            }

            $widget = new \stdClass();
            $widget->key = $snippets['snippetkey_' . $snippetindex];
            $widget->body =$snippets['snippet_' . $snippetindex];
            $usename = trim($snippets['snippetname_' . $snippetindex]);
            if ($usename == '') {
                $widget->name = $widget->key;
            } else {
                $widget->name = $usename;
            }
            $widget->instructions = $snippets['snippetinstructions_' . $snippetindex];
            $widget->defaults  = $snippets['defaults_' . $snippetindex];

            $allvariables = self::fetch_widget_variables($widget->body);
            $alldefaults=self::fetch_widget_properties($widget->defaults);
            $uniquevariables = array_unique($allvariables);
            $widget->variables =[];
            foreach($allvariables as $var){
                $default = isset($alldefaults[$var]) ? $alldefaults[$var] : '';
                $isarray=false;
                $islongtext = false;
                if(strpos($default,'|')) {
                    $default = explode('|', $default);
                    $isarray=true;
                } else if (isset($default[0]) && $default[0] === "#") {
                    $islongtext = true;
                    $default = substr($default, 1);
                }
                $display_name = str_replace('_',' ',$var);
                $display_name = str_replace('-',' ',$display_name);
                $display_name =ucwords($display_name);
                $widget->variables[] = [
                    'key'=>$var,
                    'default'=>$default,
                    'isarray'=>$isarray,
                    'islongtext'=>$islongtext,
                    'displayname'=>$display_name];
            }

            //set the template index so we can find it easily later.
            $widget->templateindex = $snippetindex;
            $widgets[]=$widget;

        }

        return $widgets;
    }

    /**
     * Return an array of variable names
     *
     * @param string template containing @@variable@@ variables
     * @return array of variable names parsed from template string
     */
    private static function fetch_widget_variables($template) {
        $matches = array();
        $t = preg_match_all('/{{(.*?)}}/s', $template, $matches);
        if (count($matches) > 1) {
            $uniquearray= array_unique($matches[1]);
            return array_values($uniquearray);
        } else {
            return array();
        }
    }

    private static function fetch_widget_properties($propstring) {
        //Now we just have our properties string
        //Lets run our regular expression over them
        //string should be property=value,property=value
        //got this regexp from http://stackoverflow.com/questions/168171/regular-expression-for-parsing-name-value-pairs
        $regexpression = '/([^=,]*)=("[^"]*"|[^,"]*)/';
        $matches = array();

        //here we match the filter string and split into name array (matches[1]) and value array (matches[2])
        //we then add those to a name value array.
        $itemprops = array();
        if (preg_match_all($regexpression, $propstring, $matches, PREG_PATTERN_ORDER)) {
            $propscount = count($matches[1]);
            for ($cnt = 0; $cnt < $propscount; $cnt++) {
                // echo $matches[1][$cnt] . "=" . $matches[2][$cnt] . " ";
                $newvalue = $matches[2][$cnt];
                //this could be done better, I am sure. WE are removing the quotes from start and end
                //this wil however remove multiple quotes id they exist at start and end. NG really
                $newvalue = trim($newvalue, '"');
                $itemprops[trim($matches[1][$cnt])] = $newvalue;
            }
        }
        return $itemprops;
    }
}
