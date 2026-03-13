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
 * Admin settings for the AI Feedback Assistant assignment feedback plugin.
 *
 * @package   assignfeedback_aifeedback
 * @copyright 2026 Daniel McCluskey
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_configcheckbox(
    'assignfeedback_aifeedback/default',
    new lang_string('default', 'assignfeedback_aifeedback'),
    new lang_string('default_help', 'assignfeedback_aifeedback'),
    0
));

$settings->add(new admin_setting_configcheckbox(
    'assignfeedback_aifeedback/captureprompt',
    new lang_string('captureprompt', 'assignfeedback_aifeedback'),
    new lang_string('captureprompt_help', 'assignfeedback_aifeedback'),
    0
));

$settings->add(new admin_setting_heading(
    'assignfeedback_aifeedback_limits',
    new lang_string('limitsheading', 'assignfeedback_aifeedback'),
    new lang_string('limitsdescription', 'assignfeedback_aifeedback')
));

$settings->add(new admin_setting_configtext(
    'assignfeedback_aifeedback/maxassignmentdescchars',
    new lang_string('maxassignmentdescchars', 'assignfeedback_aifeedback'),
    new lang_string('maxassignmentdescchars_help', 'assignfeedback_aifeedback'),
    4000,
    PARAM_INT
));

$settings->add(new admin_setting_configtext(
    'assignfeedback_aifeedback/maxsubmissionchars',
    new lang_string('maxsubmissionchars', 'assignfeedback_aifeedback'),
    new lang_string('maxsubmissionchars_help', 'assignfeedback_aifeedback'),
    20000,
    PARAM_INT
));

$settings->add(new admin_setting_configtext(
    'assignfeedback_aifeedback/maxissuechars',
    new lang_string('maxissuechars', 'assignfeedback_aifeedback'),
    new lang_string('maxissuechars_help', 'assignfeedback_aifeedback'),
    1500,
    PARAM_INT
));

$supporturl = new moodle_url('https://cheltenhaminteractive.com');
$supportlink = html_writer::link($supporturl, "Cheltenham Interactive LTD", [
    'target' => '_blank',
    'rel' => 'noopener noreferrer',
]);

$settings->add(new admin_setting_heading(
    'assignfeedback_aifeedback_support',
    new lang_string('supportheading', 'assignfeedback_aifeedback'),
    get_string('supportdescription', 'assignfeedback_aifeedback', $supportlink),
));
