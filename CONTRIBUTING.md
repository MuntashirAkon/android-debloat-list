# Contributing

The repository is divided into several folders to make it easier for the first-time contributors as well as those who
do not have access to a personal computer. However, we encourage you to [create a new issue](https://github.com/MuntashirAkon/android-debloat-list/issues/new/choose)
if you are not willing to investigate your proposed changes.

To contribute, read the JSON schema section in README and then, create a pull request with the desired changes. Minor
changes should be accepted without much review, but major changes such as the addition of a new package require a
review. The review process is decided by the maintainers based on the type of change. Third-party reviews are welcome,
but the mere approval does not count, the reviewer must leave helpful comments for authors as well as the maintainers.

## Adding or Amending a Bloatware
Each json file represents a list of bloatware for a category. For example, `aosp.json` contains a list of apps that
comes prebuilt with the AOSP itself, `oem.json` contains a list of apps from all the vendors or OEMs. The list names
are very generic, and if you have a new idea for a list, it has to be generic too. For example, apps related to
productivity can be separated from `misc.json` under a new json file. _All entries must appear in the alphabetic order
based on their `id`._ You should avoid contributing to the `pending.json` file as it is kept for compatibility reasons
only and shall be removed in the future.

## Adding or Amending a Suggestion
`suggestions` folder contains a list of suggestions for a certain category. The filename of each list is considered its
_ID_ and should not contain any non-ASCII or whitespace characters. It is done this way because multiple apps could be
assigned the same set of suggestions. Here are a few things to remember before making a suggestion:
1. The number of suggestions for _Suggestion ID_ must be as small as possible. This project does not aim at cataloguing
   all the alternatives available in the wild, rather it attempts to make it easy for a user to get started with a
   viable alternative.
2. If you have a better suggestion than the existing ones, you must present supporting arguments for it.
3. Each suggested app is thoroughly reviewed, therefore, separate issues/PRs should be created for each suggestion.
   Otherwise, the PR might be discarded due to the lack of reviewers.
4. Some suggestions require a formal audit. List of _Suggestion ID_ that currently require an audit: `app_stores`,
   `email_clients`, `vpn_services`.