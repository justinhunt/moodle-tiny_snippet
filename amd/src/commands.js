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
 * Commands helper for the Moodle tiny_snippet plugin.
 *
 * @module      tiny_snippet/commands
 * @copyright   2023 Justin Hunt <justin@poodll.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getButtonImage} from 'editor_tiny/utils';
import {get_string as getString} from 'core/str';
import {
    component,
    widgetsButtonName,
    widgetsMenuItemName
} from './common';
import widget_selector from './widget_selector';
import {getConfig} from "./options";

/**
 * Handle the action for your plugin.
 * @param {TinyMCE.editor} editor The tinyMCE editor instance.
 */
const handleAction = (editor) => {
    // TODO Handle the action.
    window.console.log(editor);
};

/**
 * Get the setup function for the buttons.
 *
 * This is performed in an async function which ultimately returns the registration function as the
 * Tiny.AddOnManager.Add() function does not support async functions.
 *
 * @returns {function} The registration function to call within the Plugin.add function.
 */
export const getSetup = async() => {
    const [
        widgetsButtonNameTitle,
        widgetsMenuItemNameTitle,
        widgetsIcon,
    ] = await Promise.all([
        getString('button_widgets', component),
        getString('menuitem_widgets', component),
        getButtonImage('snippet', component),
    ]);

    return (editor) => {


        // Register the Moodle SVG as an icon suitable for use as a TinyMCE toolbar button.
        editor.ui.registry.addIcon('snippetwidgetsicon', widgetsIcon.html);

        // Register the widgets icon if its not disabled (via permissions)
        var config = getConfig(editor);
        if(!config.disabled) {

            // Register the widgets Toolbar Button.
            editor.ui.registry.addButton(widgetsButtonName, {
                icon: 'snippetwidgetsicon',
                tooltip: widgetsButtonNameTitle,
                onAction: () => widget_selector.display(editor),
            });

            // Add the widgets Menu Item.
            // This allows it to be added to a standard menu, or a context menu.
            editor.ui.registry.addMenuItem(widgetsMenuItemName, {
                icon: 'snippetwidgetsicon',
                text: widgetsMenuItemNameTitle,
                onAction: () => widget_selector.display(editor),
            });
        }
    };
};
