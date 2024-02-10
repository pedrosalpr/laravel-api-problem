# Contribution/Development

This section talks about how to contribute and develop in the package.

## Index

- [Contribution](#contribution)
- [Development](#development)
     - [Code Analysis](#code-analysis)
     - [Hooks](#hooks)

## Contribution

To contribute to the evolution of the project it is necessary to follow some steps:

- To create a branch, follow the `[PREFIX]` pattern. The accepted prefixes are those listed below:
     - `hotfix`: To fix a production bug in the application;
     - `fix`: To fix a bug in the application;
     - `feature`: To insert a new feature into the application;
     - `build`: Changes that affect the build system or external dependencies (examples: composer, dockerfile, dockercompose);
     - `ci`: Changes to CI configuration files and scripts (examples: gitlab-ci, sonarqube, lints, security);
     - `docs`: Only modification of documentation (examples: Readme, Changelog);
     - `performance`: A code change that improves performance (examples: response time, memory consumption);
     - `refactor`: A code change that does not fix a bug or add a feature;
     - `style`: Changes that do not affect the meaning of the code (examples: white space, formatting, lint, missing semicolon, etc.);
     - `test`: Adding missing tests or correcting existing tests;
     - `chore`: When you do everything and a little in the branch (write documentation, format, add tests, clean up useless code, etc.)

## Development

### Code Analysis

A code analyzer is very useful so that we can program in a certain way following the recommendations of each analyzer.

Currently in the project there is 1 analyzer:

- PHP Code Sniffer

#### Linter

This project uses the `PhpCsFixer` linter.

There are several scripts in `composer.json` for Code Sniffer:

- `lint-diff`: Runs the linter only for files that have been modified
- `lint-diff-staged`: Runs the linter for files that have been modified and are in the `staged` section of git
- `lint-fix`: Runs the linter and fixes only the files that have been modified
- `lint-fix-staged`: Runs the linter and fixes the files that have been modified and are in the `staged` section of git

> The configuration file is in the root of the project called `.php-cs-fixer.php`

#### Php Stan

This project makes use of the [*phpstan*](https://phpstan.org/) package, which performs static analysis on the code, validating quality rules. It is possible to find errors in the code without executing it.

There is the script in `composer.json` to perform analysis:

- `analyse`: Analyzes according to `phpstan` rules.

> To look at other available options, see the [*PHPStan*](https://phpstan.org/config-reference) documentation

### Hooks

There are three git hooks:

To activate them, just type the following command:

```
npm run prepare
```

- **commit-msg**: It is checked when you finish committing to check if the message complies with the rules.
- **pre-commit**: Before each commit, it is checked whether the files that are staged in git comply with the rules
- **prepare-commit-msg**: Prepares the commit to comply with the recommendations
