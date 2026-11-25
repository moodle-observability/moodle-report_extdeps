<img src="docs/logo/logo.svg" alt="moodle-report_extdeps logo" width="96" align="right" />

# moodle-report_extdeps

[![Moodle Plugin CI](https://github.com/moodle-observability/moodle-report_extdeps/actions/workflows/ci.yml/badge.svg)](https://github.com/moodle-observability/moodle-report_extdeps/actions/workflows/ci.yml)
[![Documentation](https://github.com/moodle-observability/moodle-report_extdeps/actions/workflows/docs.yml/badge.svg)](https://github.com/moodle-observability/moodle-report_extdeps/actions/workflows/docs.yml)

Reports of the external dependencies of the Moodle courses (`report_extdeps`). Shows the dependencies found by [moodle-tool_extdeps](https://github.com/moodle-observability/moodle-tool_extdeps): which are broken, what to do and a link to edit the place where each one is used. It stores no data of its own.

**Status:** 0.1.2 (alpha). Download it from the [releases](https://github.com/moodle-observability/moodle-report_extdeps/releases).

Documentation (English and Brazilian Portuguese): https://moodle-observability.github.io/moodle-report_extdeps/

Requirements: Moodle 4.5 or 5.3 (LTS). Needs `tool_extdeps` 0.1.2 or later.

Quick start: install the plugin in `report/extdeps` (`public/report/extdeps` on Moodle 5.1 and later) and upgrade Moodle. Then open *Site administration > Reports > External dependencies*, or *Reports* in a course.

Part of [Moodle Observability](https://github.com/moodle-observability). Contributions: see the [contributing guide](https://github.com/moodle-observability/.github/blob/main/CONTRIBUTING.md).

License: [GPL-3.0](LICENSE).
