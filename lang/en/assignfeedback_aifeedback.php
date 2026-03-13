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
 * Strings for component 'assignfeedback_aifeedback', language 'en'.
 *
 * @package   assignfeedback_aifeedback
 * @copyright 2026 Daniel McCluskey
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'AI Feedback Assistant';
$string['enabled'] = 'AI Feedback Assistant';
$string['enabled_help'] = 'Enable this plugin for assignment grading.';
$string['default'] = 'Enabled by default';
$string['default_help'] = 'Enable this plugin by default for new assignments.';
$string['captureprompt'] = 'Capture sent AI prompts (debug)';
$string['captureprompt_help'] = 'Store full sent prompt text in queue records for debugging. Not recommended to keep on unless needed for troubleshooting, as prompts can be very long and may contain sensitive information.';
$string['limitsheading'] = 'Limits';
$string['limitsdescription'] = 'Configure how much content is included in the AI prompt. Set a limit to 0 to disable it.';
$string['maxassignmentdescchars'] = 'Assignment description character limit';
$string['maxassignmentdescchars_help'] = 'Maximum number of assignment description characters included in the AI prompt. Set to 0 for no limit.';
$string['maxsubmissionchars'] = 'Submission text character limit';
$string['maxsubmissionchars_help'] = 'Maximum number of submission text characters included in the AI prompt. Set to 0 for no limit.';
$string['maxissuechars'] = 'Extraction notes character limit';
$string['maxissuechars_help'] = 'Maximum number of extraction note characters included in the AI prompt. Set to 0 for no limit.';
$string['supportheading'] = 'Support';
$string['supportprovidername'] = 'Cheltenham Interactive LTD';
$string['supportdescription'] = 'This plugin is free and open source. If you need help implementing or extending it, professional Moodle support is available from {$a}.';

$string['aifeedback:generate'] = 'Generate AI guidance while grading submissions';
$string['uientrylabel'] = 'AI Feedback Assistant';
$string['generatebutton'] = 'Generate AI guidance';
$string['refreshbutton'] = 'Refresh status';
$string['statusplaceholder'] = 'No AI guidance request has been queued yet.';
$string['statuscreating'] = 'Creating AI guidance request...';
$string['reviewoutputlabel'] = 'Results';
$string['reviewguidancenote'] = 'Guidance only, not shared with students. Final feedback and marks are the teacher\'s responsibility.';
$string['reviewplaceholder'] = 'AI marker guidance will appear here when ready.';
$string['refreshactivityidle'] = 'Waiting for next status check.';
$string['refreshactivitychecking'] = 'Checking request status...';
$string['refreshjustnow'] = 'Last refreshed just now.';
$string['refreshsecondsago'] = 'Last refreshed {$a} seconds ago.';
$string['requestcreated'] = 'Queued AI guidance request (ID: {$a}).';
$string['requestfailed'] = 'Could not create AI guidance request.';
$string['requeststatusqueued'] = 'AI guidance request is queued.';
$string['requeststatusqueuedconversion'] = 'AI guidance request is queued while file conversion finishes.';
$string['requeststatusprocessing'] = 'AI guidance is being generated.';
$string['requeststatuscompleted'] = 'AI guidance is ready for review.';
$string['requeststatuscompletedwithcontent'] = 'AI guidance is ready for review. {$a}';
$string['requeststatusfailed'] = 'AI guidance generation failed: {$a}';
$string['requeststatusfailedgeneric'] = 'AI guidance generation failed.';
$string['requeststatusunknown'] = 'Queue status: {$a}';
$string['contentsummaryonlinetextincluded'] = 'Used online text submission content.';
$string['contentsummaryfilesincluded'] = 'Used extracted text from {$a} submitted file(s).';
$string['contentsummaryfilesskipped'] = 'Skipped {$a} submitted file(s) that could not be processed.';
$string['invalidgradingtarget'] = 'Selected user cannot be graded in this assignment.';
$string['invalidaction'] = 'Invalid AI guidance action.';
$string['pluginnotenabled'] = 'AI Feedback Assistant is not enabled for this assignment.';
$string['payloaderror_assignmentmismatch'] = 'Queue record does not match this assignment.';
$string['payloaderror_submissionmissing'] = 'No submission found for this user and attempt.';
$string['payloaderror_nocontent'] = 'No submission text could be extracted for this request.';
$string['payloaderror_unsupportedfiletype'] = 'File type is not supported for extraction.';
$string['payloaderror_conversionunsupported'] = 'Text conversion is unavailable for this file on this site.';
$string['payloaderror_conversionpending'] = 'File conversion is still running.';
$string['payloaderror_conversionfailed'] = 'File conversion failed. {$a}';
$string['payloaderror_conversionoutputmissing'] = 'Converted text file not found.';
$string['payloaderror_convertedempty'] = 'Converted file is empty and cannot be used for AI guidance.';
$string['payloaderror_conversiontimeout'] = 'File conversion did not finish in time. Queue a new request later.';
$string['aierror_actionunavailable'] = 'AI text generation is unavailable with current provider settings.';
$string['taskprocessqueuerequest'] = 'Process queued AI guidance';

$string['privacy:metadata:requestqueue'] = 'Queue of teacher-triggered AI guidance requests.';
$string['privacy:metadata:assignment'] = 'Assignment ID.';
$string['privacy:metadata:coursemoduleid'] = 'Course module ID.';
$string['privacy:metadata:contextid'] = 'Context ID.';
$string['privacy:metadata:foruserid'] = 'Student user ID.';
$string['privacy:metadata:requestedby'] = 'Teacher user ID that queued the request.';
$string['privacy:metadata:gradeid'] = 'Related grade record ID at queue time, if available.';
$string['privacy:metadata:attemptnumber'] = 'Submission attempt number at queue time.';
$string['privacy:metadata:status'] = 'Queue status.';
$string['privacy:metadata:attempts'] = 'Number of background processing attempts.';
$string['privacy:metadata:resultjson'] = 'Serialized processing result, including AI response metadata and generated guidance.';
$string['privacy:metadata:prompttext'] = 'Full prompt text sent to the AI provider when prompt capture is enabled.';
$string['privacy:metadata:errorcode'] = 'Latest processing error code when generation fails.';
$string['privacy:metadata:errormessage'] = 'Latest processing error message when generation fails.';
$string['privacy:metadata:timecompleted'] = 'Time processing completed.';
$string['privacy:metadata:timecreated'] = 'Time the request was created.';
$string['privacy:metadata:timemodified'] = 'Time the request was last updated.';
