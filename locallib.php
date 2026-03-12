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
 * Assignment feedback plugin for AI Feedback Assistant.
 *
 * @package   assignfeedback_aifeedback
 * @copyright 2026 Daniel McCluskey
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Assignment feedback plugin for AI Feedback Assistant.
 */
class assign_feedback_aifeedback extends assign_feedback_plugin {
    /**
     * Return plugin display name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_aifeedback');
    }

    /**
     * Add grading form elements for the teacher-facing AI guidance entry point.
     *
     * @param stdClass|null $grade
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @param int $userid
     * @return bool
     */
    public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {
        global $PAGE;

        $context = $this->assignment->get_context();
        if (!has_capability('assignfeedback/aifeedback:generate', $context)) {
            return false;
        }

        $cmid = (int) $this->assignment->get_course_module()->id;
        $buttonid = 'assignfeedback_aifeedback_generate_' . $userid;
        $refreshid = 'assignfeedback_aifeedback_refresh_' . $userid;
        $statusid = 'assignfeedback_aifeedback_status_' . $userid;
        $refreshinfoid = 'assignfeedback_aifeedback_refreshinfo_' . $userid;
        $outputid = 'assignfeedback_aifeedback_output_' . $userid;
        $reviewdetailsid = 'assignfeedback_aifeedback_reviewdetails_' . $userid;

        $button = html_writer::tag(
            'button',
            get_string('generatebutton', 'assignfeedback_aifeedback'),
            [
                'type' => 'button',
                'id' => $buttonid,
                'class' => 'btn btn-sm btn-secondary',
            ]
        );

        $refreshbutton = html_writer::tag(
            'button',
            get_string('refreshbutton', 'assignfeedback_aifeedback'),
            [
                'type' => 'button',
                'id' => $refreshid,
                'class' => 'btn btn-sm btn-outline-secondary ms-1',
            ]
        );

        $actions = html_writer::div(
            $button . $refreshbutton,
            'assignfeedback-aifeedback-actions'
        );

        $status = html_writer::div(
            get_string('statusplaceholder', 'assignfeedback_aifeedback'),
            'alert alert-info mt-2 mb-1 py-2',
            [
                'id' => $statusid,
                'role' => 'status',
                'aria-live' => 'polite',
            ]
        );

        $refreshinfo = html_writer::div(
            get_string('refreshactivityidle', 'assignfeedback_aifeedback'),
            'text-muted small mb-0',
            [
                'id' => $refreshinfoid,
                'aria-live' => 'polite',
            ]
        );

        $reviewsummary = html_writer::tag(
            'summary',
            get_string('reviewoutputlabel', 'assignfeedback_aifeedback'),
            [
                'class' => 'small font-weight-bold mt-1',
            ]
        );

        $reviewnote = html_writer::div(
            get_string('reviewguidancenote', 'assignfeedback_aifeedback'),
            'small text-muted mt-1 mb-2'
        );

        $reviewtextarea = html_writer::tag(
            'textarea',
            '',
            [
                'id' => $outputid,
                'class' => 'form-control',
                'rows' => 8,
                'readonly' => 'readonly',
                'placeholder' => get_string('reviewplaceholder', 'assignfeedback_aifeedback'),
            ]
        );

        $reviewdetails = html_writer::tag(
            'details',
            $reviewsummary . $reviewnote . $reviewtextarea,
            [
                'id' => $reviewdetailsid,
                'class' => 'assignfeedback-aifeedback-review mt-2',
                'open' => 'open',
            ]
        );

        $content = html_writer::div(
            $actions . $status . $refreshinfo . $reviewdetails,
            'assignfeedback-aifeedback-uientry'
        );

        $mform->addElement(
            'static',
            'assignfeedback_aifeedback_uientry_' . $userid,
            get_string('uientrylabel', 'assignfeedback_aifeedback'),
            $content
        );

        $PAGE->requires->js_call_amd('assignfeedback_aifeedback/requestui', 'init', [[
            'ajaxurl' => (new moodle_url('/mod/assign/feedback/aifeedback/ajax.php'))->out(false),
            'buttonid' => $buttonid,
            'cmid' => $cmid,
            'outputid' => $outputid,
            'pollintervalms' => 7000,
            'refreshid' => $refreshid,
            'refreshactivitychecking' => get_string('refreshactivitychecking', 'assignfeedback_aifeedback'),
            'refreshactivityidle' => get_string('refreshactivityidle', 'assignfeedback_aifeedback'),
            'refreshinfoid' => $refreshinfoid,
            'refreshjustnow' => get_string('refreshjustnow', 'assignfeedback_aifeedback'),
            'refreshsecondsago' => get_string('refreshsecondsago', 'assignfeedback_aifeedback'),
            'requestfailed' => get_string('requestfailed', 'assignfeedback_aifeedback'),
            'requesting' => get_string('statuscreating', 'assignfeedback_aifeedback'),
            'statusplaceholder' => get_string('statusplaceholder', 'assignfeedback_aifeedback'),
            'statusid' => $statusid,
            'userid' => (int) $userid,
        ]]);

        return true;
    }

    /**
     * UI-only plugin stage; no feedback field is persisted here.
     *
     * @param stdClass $grade
     * @param stdClass $data
     * @return bool
     */
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
        return false;
    }
}
