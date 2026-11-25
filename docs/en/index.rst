moodle-report_extdeps
=====================

**Reports of the external dependencies of the Moodle courses.**

Shows what ``tool_extdeps`` found: the broken videos and packages of each course, what to do about each one and a
button that opens the page where it can be fixed.

.. note::

   Version 0.1.2 (alpha).

Features
--------

* **Site view**: cards per provider and a list of courses with the broken occurrences, filters and export (CSV,
  Excel, ODS and other formats).
* **All occurrences**: every place where a dependency is used, broken ones by default.
* **Course view** (for the editing teachers): section, activity, provider, situation, since when and **Edit**, which
  opens the activity, the book chapter, the lesson page, the quiz questions, the section or the course settings.
* **Dependency page**: evidence of the last check, where it is used, the history of its situation and false
  positives.
* **Check now**: an activity is checked during the request; a course, a provider or the site in the background,
  with a result page that refreshes by itself.
* **Custom reports**: the "External dependencies" source of the Report Builder.

Requirements
------------

* Moodle 4.5 or 5.3 (LTS).
* ``tool_extdeps`` 0.1.2 or later.

.. toctree::
   :maxdepth: 1

   installation
   configuration
   usage
