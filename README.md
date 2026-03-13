# AI Feedback Assistant (`assignfeedback_aifeedback`)

AI Feedback Assistant is a teacher-side helper for assignment grading.

It adds a `Generate AI guidance` action on the grading screen and returns draft marker guidance based on the assignment brief and submitted work. The teacher reviews and rewrites this guidance before using it.

![Example of AI feedback guidance on the grading screen](docs/images/preview.webp)




## Quick Start

- Requires Moodle `5.1+`.
- Install to `mod/assign/feedback/aifeedback`.
- Complete the plugin upgrade from `Site administration > Notifications`.
- Configure at least one Moodle AI provider that supports text generation.
- Setup guide: [AI provider setup](docs/AI_PROVIDER_SETUP.md).

## Scope and Accountability

This plugin exists to support teacher judgement, not replace it.

Grades can affect student confidence, progression, and future opportunities.  
For that reason, this project is intentionally scoped as a teacher assistant and not an auto-grading system.

- It does not assign final grades.
- It does not auto-save feedback.
- It does not auto-send feedback to students.
- The teacher remains responsible for the final mark and final wording shared with the student.

## Data Protection and GDPR

This plugin sends assessment data to the configured AI provider when a teacher requests AI guidance.

- Submission content may include sensitive or personal data from student work.
- Assignment metadata and extracted submission text are included in the request payload.
- Data leaves Moodle and is processed under the selected provider's terms, location, and retention policies.

Before enabling this plugin, the site owner should confirm legal and policy requirements for their institution.

- Confirm a lawful basis for processing under UK GDPR/EU GDPR (as applicable).
- Complete a DPIA or equivalent risk assessment if required.
- Inform staff and students about AI processing in local privacy notices.
- Restrict provider access and plugin usage to approved contexts only.
- Disable the plugin where external AI processing is not permitted.

## Prompt Summary

The plugin sends one combined prompt with:

- Assignment name and description
- Assignment grading scheme (numeric range or scale options)
- Submission content (online text and/or extracted `.docx` / `.pdf` text)
- Extraction notes (for example, unsupported files or partial extraction)

It asks the AI to return teacher-facing guidance with:

- An advisory suggested grade within the real grading scheme
- Key strengths and weaknesses
- Practical future improvement suggestions
- Wording written for marker review, not direct student delivery

## Limitations

- Supports online text and file extraction for `.docx` and `.pdf` only.
- File conversion support depends on Moodle file conversion services for the site.

## Support

This plugin is free and open source.

If you need help implementing or extending it, professional Moodle support is available from Cheltenham Interactive LTD:

https://cheltenhaminteractive.com

## TODO

- Add an admin setting to force this plugin to use a specific Moodle AI provider instead of the first available provider.
- Add usage limits per course, per day, per submission record, and per user.
- Add a feature to restrict plugin availability to selected courses and/or course categories.
- Add a cleanup/retention task so AI feedback results are kept only for a configurable period (for example, X days/months).
- Investigate contributing to Moodle core to support provider-side file uploads, so this plugin does not need to extract file text and inject it into prompts.
