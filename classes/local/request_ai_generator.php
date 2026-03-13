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
 * Queue request AI guidance generation integration for assignfeedback_aifeedback.
 *
 * @package   assignfeedback_aifeedback
 * @copyright 2026 Daniel McCluskey
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignfeedback_aifeedback\local;

defined('MOODLE_INTERNAL') || die();

use core_ai\aiactions\generate_text;
use core_ai\manager;

/**
 * Sends prepared submission payloads to Moodle AI.
 */
class request_ai_generator {
    /** @var int Default max assignment description characters included in prompt. */
    private const DEFAULT_MAX_ASSIGNMENT_DESC_CHARS = 4000;
    /** @var int Default max submission characters included in prompt. */
    private const DEFAULT_MAX_SUBMISSION_CHARS = 20000;
    /** @var int Default max issue details characters included in prompt. */
    private const DEFAULT_MAX_ISSUE_CHARS = 1500;

    /**
     * Generate AI guidance for a queued request.
     *
     * @param int $requestid Queue request ID.
     * @return array Structured generation result.
     */
    public static function generate_from_request_id(int $requestid): array {
        global $DB;

        $request = $DB->get_record('assignfeedback_aifeedback_q', ['id' => $requestid], '*', MUST_EXIST);
        $resolved = request_payload_resolver::resolve_from_request($request);
        $contentstats = self::build_content_stats($resolved);

        if ($resolved['status'] === request_payload_resolver::STATUS_FAILED) {
            if (request_payload_resolver::has_pending_conversion($resolved)) {
                return [
                    'requestid' => $requestid,
                    'status' => 'payload_waiting_conversion',
                    'payloadresult' => $resolved,
                    'ai' => null,
                    'contentstats' => $contentstats,
                    'structuredfeedback' => null,
                ];
            }

            return [
                'requestid' => $requestid,
                'status' => 'payload_failed',
                'payloadresult' => $resolved,
                'ai' => null,
                'contentstats' => $contentstats,
                'structuredfeedback' => null,
            ];
        }

        $aimanager = \core\di::get(manager::class);
        if (!$aimanager->is_action_available(generate_text::class)) {
            return [
                'requestid' => $requestid,
                'status' => 'ai_unavailable',
                'payloadresult' => $resolved,
                'ai' => [
                    'success' => false,
                    'errorcode' => -1,
                    'errormessage' => get_string('aierror_actionunavailable', 'assignfeedback_aifeedback'),
                    'timecreated' => time(),
                    'model' => null,
                    'generatedcontent' => '',
                    'finishreason' => '',
                    'prompttokens' => '',
                    'completiontokens' => '',
                ],
                'contentstats' => $contentstats,
                'structuredfeedback' => null,
            ];
        }

        $prompt = self::build_prompt($resolved['payload'], $resolved['issues']);
        $action = new generate_text(
            contextid: (int) $request->contextid,
            userid: (int) $request->requestedby,
            prompttext: $prompt,
        );
        $response = $aimanager->process_action($action);
        $responsedata = $response->get_response_data();
        $generatedcontent = (string)($responsedata['generatedcontent'] ?? '');

        return [
            'requestid' => $requestid,
            'status' => $response->get_success() ? 'completed' : 'ai_error',
            'prompttext' => $prompt,
            'payloadresult' => $resolved,
            'ai' => [
                'success' => $response->get_success(),
                'errorcode' => $response->get_errorcode(),
                'errormessage' => $response->get_errormessage(),
                'timecreated' => $response->get_timecreated(),
                'model' => $response->get_model_used(),
                'generatedcontent' => $generatedcontent,
                'finishreason' => (string)($responsedata['finishreason'] ?? ''),
                'prompttokens' => (string)($responsedata['prompttokens'] ?? ''),
                'completiontokens' => (string)($responsedata['completiontokens'] ?? ''),
            ],
            'contentstats' => $contentstats,
            'structuredfeedback' => self::parse_structured_feedback($generatedcontent),
        ];
    }

    /**
     * Build content usage stats for status messages.
     *
     * @param array $resolved Resolved payload structure.
     * @return array
     */
    private static function build_content_stats(array $resolved): array {
        $submission = $resolved['payload']['submission'] ?? [];
        $onlinetextstatus = (string)($submission['onlinetext']['status'] ?? '');
        $files = is_array($submission['files'] ?? null) ? $submission['files'] : [];

        $totalfiles = count($files);
        $okfiles = 0;
        $skippedfiles = 0;
        $pendingfiles = 0;

        foreach ($files as $file) {
            $status = (string)($file['status'] ?? '');
            if ($status === 'ok') {
                $okfiles++;
                continue;
            }

            $skippedfiles++;
            if ($status === 'conversion_pending') {
                $pendingfiles++;
            }
        }

        return [
            'onlinetextstatus' => $onlinetextstatus,
            'files_total' => $totalfiles,
            'files_ok' => $okfiles,
            'files_skipped' => $skippedfiles,
            'files_pending' => $pendingfiles,
        ];
    }

    /**
     * Build the AI prompt from resolved payload data.
     *
     * @param array $payload Resolved request payload.
     * @param array $issues Extraction issues.
     * @return string
     */
    private static function build_prompt(array $payload, array $issues): string {
        $assignmentdesclimit = self::get_prompt_char_limit(
            'maxassignmentdescchars',
            self::DEFAULT_MAX_ASSIGNMENT_DESC_CHARS
        );
        $submissionlimit = self::get_prompt_char_limit(
            'maxsubmissionchars',
            self::DEFAULT_MAX_SUBMISSION_CHARS
        );
        $assignmentname = (string)($payload['assignment']['name'] ?? '');
        $assignmentdescription = self::truncate_text(
            (string)($payload['assignment']['description'] ?? ''),
            $assignmentdesclimit
        );
        $gradingscheme = is_array($payload['assignment']['gradingscheme'] ?? null)
            ? $payload['assignment']['gradingscheme']
            : [];
        $gradingschemedescription = trim((string)($gradingscheme['description'] ?? 'Grading scheme is unavailable.'));
        $gradeconstraintsummary = trim((string)($gradingscheme['aiprompt'] ?? 'Do not invent grade options.'));
        $gradesuggestioninstruction = self::build_grade_suggestion_instruction($gradingscheme);
        $submissiontext = self::truncate_text(
            (string)($payload['submission']['combinedtext'] ?? ''),
            $submissionlimit
        );
        $issuesummary = self::summarise_issues($issues);

        return implode("\n", [
            'You are a strict third-party marker assisting a teacher with one assignment submission.',
            'Use a neutral, detached tone. Do not soften criticism to protect feelings.',
            'Your output is for marker decision support only and is not student-facing feedback.',
            'Return exactly one JSON object and no additional text.',
            'Use this exact JSON shape:',
            '{',
            '  "suggested_grade": "string",',
            '  "strengths": ["string"],',
            '  "weaknesses": ["string"],',
            '  "future_improvement_suggestions": ["string"],',
            '  "positive_tone_summary": "string",',
            '  "teacher_review_wording": "string"',
            '}',
            'Requirements:',
            '1. Use strict marking standards. suggested_grade is advisory only, must fit the grading scheme below, and must include a brief rationale.',
            '2. ' . $gradesuggestioninstruction,
            '3. Never invent grades, bands, letters, or scale labels outside the provided grading scheme.',
            '4. If the grading scheme cannot be used for a valid suggestion, set suggested_grade exactly to the fallback text required in the scheme constraints.',
            '5. Do not inflate marks. If evidence is weak, incomplete, or missing, choose a lower mark or lower scale option.',
            '6. Do not award credit for effort, intent, or assumed understanding. Award credit only for demonstrated evidence.',
            '7. Anchor strengths and weaknesses in concrete evidence from the submission and assignment brief.',
            '8. Future improvement suggestions must be practical notes for the marker, not direct instructions to the student.',
            '9. positive_tone_summary must still be factual and neutral, with no encouragement or reassurance language.',
            '10. Never address the student as "you" and do not produce ready-to-send student feedback.',
            '11. teacher_review_wording must read as an internal marker note and must require teacher rewriting before release.',
            '12. Do not include markdown or code fences.',
            '',
            'Assignment name:',
            $assignmentname,
            '',
            'Assignment description:',
            $assignmentdescription,
            '',
            'Grading scheme:',
            $gradingschemedescription,
            '',
            'Grade suggestion constraints:',
            $gradeconstraintsummary,
            '',
            'Submission text:',
            $submissiontext,
            '',
            'Extraction notes:',
            $issuesummary,
        ]);
    }

    /**
     * Build scheme-specific suggested_grade formatting rules.
     *
     * @param array $gradingscheme Resolved grading scheme payload.
     * @return string
     */
    private static function build_grade_suggestion_instruction(array $gradingscheme): string {
        $type = (string)($gradingscheme['type'] ?? '');
        if ($type === 'numeric') {
            return 'For numeric grading schemes, suggested_grade must be a numeric range in the form "X - X + 3" that stays fully inside the allowed min/max. Use a narrow band of 3 to 5 marks where possible. If the configured range is smaller, use the narrowest valid interval. Do not return a single exact mark.';
        }

        return 'For non-numeric grading schemes, suggested_grade must be one valid option exactly as configured.';
    }

    /**
     * Parse model output into the expected structured object when possible.
     *
     * @param string $generatedcontent Raw generated content.
     * @return array|null
     */
    private static function parse_structured_feedback(string $generatedcontent): ?array {
        $clean = trim($generatedcontent);
        if ($clean === '') {
            return null;
        }

        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/is', $clean, $matches)) {
            $clean = trim($matches[1]);
        }

        $decoded = json_decode($clean, true);
        if (!is_array($decoded)) {
            return null;
        }

        return [
            'suggested_grade' => trim((string)($decoded['suggested_grade'] ?? '')),
            'strengths' => self::normalise_list_field($decoded['strengths'] ?? []),
            'weaknesses' => self::normalise_list_field($decoded['weaknesses'] ?? []),
            'future_improvement_suggestions' => self::normalise_list_field(
                $decoded['future_improvement_suggestions'] ?? []
            ),
            'positive_tone_summary' => trim((string)($decoded['positive_tone_summary'] ?? '')),
            'teacher_review_wording' => trim((string)($decoded['teacher_review_wording'] ?? '')),
        ];
    }

    /**
     * Normalise possibly-mixed list content into an array of strings.
     *
     * @param mixed $value Candidate list value.
     * @return array
     */
    private static function normalise_list_field(mixed $value): array {
        if (!is_array($value)) {
            return [];
        }

        $normalised = [];
        foreach ($value as $item) {
            $itemtext = trim((string)$item);
            if ($itemtext !== '') {
                $normalised[] = $itemtext;
            }
        }

        return $normalised;
    }

    /**
     * Summarise payload extraction issues for prompt context.
     *
     * @param array $issues Issue list.
     * @return string
     */
    private static function summarise_issues(array $issues): string {
        if (empty($issues)) {
            return 'No extraction issues were reported.';
        }

        $lines = [];
        foreach ($issues as $issue) {
            $code = (string)($issue['code'] ?? 'unknown_issue');
            $message = (string)($issue['message'] ?? '');
            $lines[] = $code . ': ' . $message;
        }

        $issuelimit = self::get_prompt_char_limit(
            'maxissuechars',
            self::DEFAULT_MAX_ISSUE_CHARS
        );

        return self::truncate_text(implode("\n", $lines), $issuelimit);
    }

    /**
     * Get a configured prompt section character limit.
     *
     * @param string $configname Plugin config key.
     * @param int $default Default value when config is missing or invalid.
     * @return int
     */
    private static function get_prompt_char_limit(string $configname, int $default): int {
        $value = \get_config('assignfeedback_aifeedback', $configname);
        if (!is_numeric($value)) {
            return $default;
        }

        $limit = (int)$value;
        if ($limit < 0) {
            return $default;
        }

        return $limit;
    }

    /**
     * Truncate long prompt sections to a maximum size.
     *
     * @param string $text Input text.
     * @param int $maxchars Maximum allowed length. Use 0 for no limit.
     * @return string
     */
    private static function truncate_text(string $text, int $maxchars): string {
        $trimmed = trim($text);
        if ($maxchars === 0) {
            return $trimmed;
        }

        if (\core_text::strlen($trimmed) <= $maxchars) {
            return $trimmed;
        }

        return \core_text::substr($trimmed, 0, $maxchars) . "\n[TRUNCATED]";
    }
}
