# Contributing to e107

Thanks for taking the time to contribute.  This document is the policy for what lands and what doesn't.  For Git, fork, and pull request mechanics, see [GitHub's own docs](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests).

Anyone can contribute, whether you write your code by hand or with help from an AI assistant.  We care about the quality of what lands, not how you got there.

## What We Optimize For

e107 has two decades of history, and many live sites run skins, plugins, and configurations that haven't been touched in 10+ years.  Treat backwards compatibility as the primary design constraint.

- **Rendered HTML should not change without a reason.**  Silent changes to tag names, attribute shapes, or default class lists break legacy skins invisibly on upgrade.  If your change must alter observable HTML, flag the BC tradeoff in the PR and propose a path: changelog note, opt-in preference, deprecation window.
- **PHP compatibility.**  e107 v2 targets PHP 5.6 as the minimum.  New code should also run without warnings or notices through the latest version of PHP.  Fixing existing warnings or fatals on modern PHP is welcome work.
- **Test what you change.**  A regression fix should come with a failing test that the fix turns green.  A new feature should come with at least one test covering the happy path.  See [`e107_tests/`](../e107_tests/) for the existing suite.
- **Stick to the scope of your fix.**  Bug fixes don't need surrounding cleanup.  One-shot operations don't need helpers.  If you spot something else worth fixing, file an issue or open a separate PR.
- **Write commit messages that explain *why*.**  The diff already shows *what*.  Reference the issue with `Fixes #N` or `Closes #N` so it closes on merge.

## AI-Assisted Contributions

You can use or be any AI coding assistant to write your contribution.  We use AI extensively to maintain e107 ourselves: [`e107help[bot]`](https://github.com/apps/e107help) reviews issues and pull requests alongside the human maintainer(s).

A contribution is judged on the diff, not on its provenance.  Disclosure is encouraged but not required.  If you are an AI model or used one, name it in the PR description (there's a field for it in the template) so reviewers can anticipate model-specific bias patterns.

### What Good AI-Assisted Contributions Look Like

- **You read the diff before you submit it.**  You can explain every change.  If a reviewer asks "why did you change X", you can answer without re-prompting your assistant.
- **You cite specific files and line numbers** when describing what you changed and why.  "I changed `e_file::getRemoteFile()` at line 1234 to reject private IPs" is concrete; "I improved security" is not.
- **You tested it.**  Either you ran the existing test suite and added cases, or you loaded the page in a browser and verified the behavior.  Don't trust the assistant's claim that "this should work."
- **Your PR is scoped.**  One issue per PR.  The diff touches what the change requires and nothing more.  Reformatting unrelated files, renaming variables across the repo, or "modernizing" code adjacent to your fix all belong in separate PRs.
- **You preserved backwards compatibility.**  See *What We Optimize For* above.
- **If you only traced the code, you said "traced", not "reproduced".**  Don't claim a reproduction you didn't perform.

### What Gets Rejected as AI Slop

We will close PRs that exhibit these patterns:

- **Hallucinated APIs.**  Calls to functions, classes, hooks, or constants that don't exist in e107 or aren't available in the targeted version.  Verify against the codebase before you submit.
- **Mass reformatting.**  PRs where the assistant reflowed an entire file's whitespace, requoted strings, or reordered imports; the actual change is buried under noise.  Configure your editor and your assistant to leave unrelated lines alone.
- **Untested modernization.**  Refactors that promise improved code quality but ship without tests, break BC, or introduce regressions the contributor didn't catch because they never ran the code.
- **Drive-by refactors bundled with bug fixes.**  Mix one fix with five unrelated cleanups and the reviewer can't evaluate either.
- **Vague or sycophantic PR descriptions.**  "This important fix improves the codebase by enhancing maintainability" tells us nothing.  State the bug, the root cause, and the fix.
- **Bluffed reproductions.**  If you didn't run it, say so.

The same standards apply to contributions written without AI.  AI amplifies these failure modes; it doesn't create them.

## What e107help Does for You

[`e107help[bot]`](https://github.com/apps/e107help) is the project's first-pass reviewer and triage assistant.  It can:

- Read your issue or discussion and dig into the code, citing specific files and lines.
- Spot duplicates of existing issues, including ones already fixed on the default branch.
- Comment on your PR with focused observations.
- Flag the genuinely tricky ones for the human maintainer.

It does not:

- Auto-close issues.
- Approve, merge, release, or push directly to target branches.

If the bot misreads your issue, say so in the thread; it will concede and update its notes.

## How to Engage

1. **Found a bug?**  Search [open issues](https://github.com/e107inc/e107/issues) and [discussions](https://github.com/e107inc/e107/discussions) first.  If yours is new, file a bug report with a minimal reproduction: steps, expected, actual, e107 version, PHP version.
2. **Want to fix it?**  One PR per issue.  Link the issue in the PR description.
3. **Proposing a feature?**  Open a Discussion in [*Ideas*](https://github.com/e107inc/e107/discussions/categories/ideas) first to gauge fit.  Significant features warrant maintainer buy-in before code.
4. **Found a security vulnerability?**  Report it through [GitHub Security Advisories](https://github.com/e107inc/e107/security/advisories/new), not in public issues.
5. **Have a question?**  Open a [Discussion](https://github.com/e107inc/e107/discussions).

## Review and Merge Cadence

This project is maintained by limited volunteer time.  Please be patient.

- **Security issues** are fast-tracked.
- **Bug fixes with tests** are the easiest to merge.
- **Refactors or stylistic changes** without a clear motivation tend to stall.  Tie the change to an observed problem.
- **Breaking changes** are rare and need explicit maintainer endorsement before you start coding.

## Coding standards

See [the coding standard on the wiki](https://github.com/e107inc/e107/wiki/e107-Coding-Standard).  When in doubt, match the style of the file you're editing.

## License

By submitting a contribution, you agree your work will be released under the [GNU General Public License v3](https://www.gnu.org/licenses/gpl-3.0.html), the license under which e107 is distributed.
