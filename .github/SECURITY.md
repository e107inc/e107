# Security Policy

## Reporting a Vulnerability

**Do not open a public issue for a security vulnerability.**

Report it privately through [GitHub Security Advisories](https://github.com/e107inc/e107/security/advisories/new). This is the preferred channel, because the report, the discussion and the fix stay together until the advisory is published.

If you cannot use GitHub, email security@e107.org, which reaches the maintainers.

Security reports are fast-tracked. You should expect an initial response within a few days.

A useful report gives the steps to reproduce the defect, the earliest affected release you have found, and one CVSS vector. [Rating Severity](#rating-severity) explains how e107 scores.

## Disclosure Policy

Due to advances in AI-assisted vulnerability scanning, valid reports are treated as zero-days: we assume the vulnerability has already been found elsewhere and patch as fast as possible. Advisories are published on release with full details. We credit reporters unless they prefer to remain anonymous.

## Terms

In this policy, *hardening* means a defect that is fixed without an advisory.

## Supported Versions

Every maintained release line receives security fixes. The Release Lifecycle table in `README.md` shows which lines are maintained.

Every PHP version that e107 still installs on is in scope, the oldest included. The PHP Compatibility table in `README.md` gives the PHP range each release line is intended to work on. A defect that appears only with some versions of PHP, the database server, a library or the locale still counts.

## Scope

In scope:

- e107 core, including the plugins and themes that ship with it.
- Third-party libraries that e107 bundles, where the flaw can be exploited through an e107 installation.

Out of scope:

- Plugins and themes distributed separately from e107. Report those to their authors.
- The test harness.
- A server set up contrary to e107's documented requirements.

A defect in code that no release has shipped yet is hardening. Report it through the same private channel.

## Trust Model

### Guests and Members

Guests are untrusted.

A member acts only within the user classes they hold. A member who reaches content, actions or settings outside those classes is in scope.

### Posting HTML

The permission to post HTML is a grant of trust equivalent to running script; whom to grant it to is the site owner's decision. Script from a holder of that permission is by design, and a report that needs the attacker to hold it is not a vulnerability.

Content is judged by its author's permissions, never by the reader's.

### Administrators

The main administrator holds every permission. A delegated administrator holds only the areas they were granted.

Some permissions already let their holder run arbitrary code: posting HTML, and installing or uploading plugins or themes (see [Trusted by Design](#trusted-by-design)). A delegated administrator who holds one of them can already do anything on the site, so using it to gain more is not a vulnerability.

It is a vulnerability when a delegated administrator who holds none of those permissions:

- gains the main administrator's powers;
- writes settings reserved to the main administrator;
- plants script that runs for the main administrator.

### Trusted by Design

The following already give their holder control of the site. A defect reachable only by someone who holds one of them is hardening:

- the permission to post HTML (see [Posting HTML](#posting-html));
- the permission to install or upload plugins or themes;
- access from outside e107: the configuration file, the database, or a shell on the server.

### Debug Output

Debug output is in scope, and a defect in what it prints is rated like a defect on any other page.

### Cross-Site Request Forgery

A permission check is no defence against cross-site request forgery, because a forged request carries the victim's permissions. Every request that changes state must be protected against forgery, whatever permission it also needs.

### Mail to Administrators

A guest should not be able to choose when the site emails its administrator, so events a guest can cause belong in the administration area rather than in an email. A path that lets a guest do so is hardening. Mail the site owner has chosen to receive, such as contact-form messages and opt-in notifications, is out of scope.

## Rating Severity

- Give one CVSS vector per report, taken from a reproduction.
- Script that runs in an administrator's session is scored as code execution on the server, because an administrator may hold the permission to install plugins: the impact reaches beyond the browser, and confidentiality, integrity and availability impact are all high. Privileges required is the attacker's own level, and user interaction is scored as usual.
- Exploitation is scored as harder (high attack complexity, or the matching metric of the CVSS version you use) when it needs any of:
  - non-default settings, beyond switching on a feature or a bundled plugin;
  - behaviour specific to some versions of PHP, the database server, a library or the locale;
  - a page on a related domain.
- Score at the value you measured. Where a worse case is plausible, describe it in the report rather than in the vector.
- Make one report per independently fixable defect. Conditions that cannot occur without each other are one defect. Where defects chain, report each one and cross-reference them.
- State the earliest affected release you have found.
- Loss of availability alone, caused by a single cross-site request, is hardening.

## Patches

A candidate patch is welcome. Attach it to the private report, never to a public pull request. A patch fits e107 best when it:

- works on every maintained release line and on every PHP version that e107 still installs on;
- fixes the root cause in one shared place rather than at each caller;
- leaves every existing public or protected method signature as it is, neither breaking nor widening it, because even a new parameter with a default can break subclasses that override the method;
- includes a regression test that fails without the fix;
- limits any upgrade step to stored data it can prove came from the defect, never loses data, and leaves anything doubtful in place for the advisory's advice to cover;
- names any compatibility cost it brings. A suggested release-note line is welcome, but the maintainers write the release notes.

## Automated Scanners

On the repository's default branch, the test harness's Docker image builds a self-contained e107 site, with its web server and database in one container, so it needs no network once built. The README beside that image's Dockerfile, `e107_tests/docker/README.md`, describes how to start and use it.
