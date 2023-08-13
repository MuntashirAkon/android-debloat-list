# Android Debloat List

A comprehensive list of apps that come preinstalled with many ROMs and how to remove and replace them, intended for
backend rather than frontend. This list is kept in synchronised with
the [Universal Android Debloater](https://github.com/0x192/universal-android-debloater) (UAD) project.

## JSON Schema

Our JSON schema is quite different from the one used in the UAD project.

- `id` (string) - Name of the package. It has to be unique per entry across all the list.
- `label` (string) _optional_ - The readable name of the package in English, currently optional as UAD does not have
  this field.
- `dependencies` (list of strings) _optional_ - Package names of the apps that this app depends on.
- `required_by` (list of strings) _optional_ - Package names of the apps that depend on this app.
- `tags` (list of strings) _optional_ - List of predefined tags/categories.
- `description` (string) - Description of the package under 200 words.
- `web` (list of strings) _optional_ - List of websites including Google Play Store, investigation reports, etc.
- `removal` (string) - How should this app be removed. The values are predefined.
- `warning` (string) _optional_ - Additional description for the app that require user's attention.
- `suggestions` (string) _optional_ - Suggestion ID.

### Values for `removal`

| name      | description                                                                   |
|-----------|-------------------------------------------------------------------------------|
| `delete`  | It's safe to just get rid of the app.                                         |
| `replace` | Replace the app with something else, preferably, with one of the suggestions. |
| `caution` | It might not be safe to get rid of the app.                                   |
| `unsafe`  | It is unsafe to get rid of the app. `warning` must be set for this.           |

### Values for `tags`

Will be added when required.

### Suggestions

The filename of the suggestion is its _Suggestion ID_. The file contains a list of suggested apps. Each suggestion has
the following format:

- `id` (string) - Package name of the app.
- `label` (string) - The readable name of the app in English.
- `reason` (string) _optional_ - Why this is suggested, under 100 words. This value must only be filled for cases where
  no valid alternative exists.
- `source` (string) _optional_ - Source of the app. This is a predefined value, could be `fgas` where `f` stands for
  F-Droid, `g` for Google Play Store, `a` for Amazon Appstore and `s` for Samsung Galaxy Store.
- `repo` (string) - Link to the project repository.

#### Criteria for suggestions

1. There does not already exist a suggestion for the _Suggestion ID_ that offers similar or better features. A new
   suggestion without a `reason` is preferable over an existing suggestion with `reason`.
2. Both source code and software have to be free (libre) and open source having an OSI approved license. Use of non-free
   assets or resources (that is, non-source code) in a software is permissible. But the `reason` must be included for
   such cases.
3. Interface or functions (UI/UX) should be very or nearly similar to the original app or even better. If no such
   alternative is available right now, `reason` must be included.
4. For crucial apps, such as instant messaging or password manager apps, only an audited app might be listed. Unaudited 
   yet popular apps should contain sufficient warning in the `reason` section.
5. The suggested app should not contain any trackers. If it does contain any reasonable tracker such as crash reporting
   framework, it should be disabled (that is, opt-out) by default. If there exists no such alternatives, the `reason`
   section must be updated with sufficient warning.
6. No credits or endorsement is allowed. If a suggested app contain or advertise its inclusion in this list, the app
   will be removed from the list in immediate effect.

## Contributing

The repository is divided into several folders to make it easier for the first-time contributors as well as those who
do not have access to a personal computer. However, we encourage you to [create a new issue](https://github.com/MuntashirAkon/android-debloat-list/issues/new/choose)
if you are not willing to investigate your proposed changes.

To contribute, read the JSON schema section above and the next subsections and then, create a pull request with the
desired changes. Minor changes should be accepted without much review, but major changes such as the addition of a new
package require a review. The review process is decided by the maintainers based on the type of change. Third-party
reviews are welcome but the mere approval does not count, the reviewer must leave helpful comments for authors as well
as the maintainers. 

### Adding or Amending a Bloatware
Each json file represents a list of bloatware for a category. For example, `aosp.json` contains a list of apps that
comes prebuilt with the AOSP itself, `oem.json` contains a list of apps from all the vendors or OEMs. The list names
are very generic, and if you have a new idea for a list, it has to be generic too. For example, apps related to
productivity can be separated from `misc.json` under a new json file. _All entries must appear in the alphabetic order
based on their `id`._ You should avoid contributing to the `pending.json` file as it is kept for compatibility reasons
only and shall be removed in the future.

### Adding or Amending a Suggestion
`suggestions` folder contains a list of suggestions for a certain category. The filename of each list is considered its
_ID_ and should not contain any non-ASCII or whitespace characters. It is done this way because multiple apps could be
assigned the same set of suggestions. Here are a few things to remember before making a suggestion:
1. The number of suggestions for _Suggestion ID_ must be as small as possible. This project does not aim at cataloguing
   all the alternatives available in the wild, rather it attempts to make it easy for a user to get started with a
   viable alternative.
2. If you have a better suggestion than the existing ones, you must present supporting arguments for it.
3. Each suggested app is thoroughly reviewed, therefore, separate issues/PRs should be created for each suggestion.
   Otherwise, the PR might be discarded due to the lack of reviewer.

## License

```
Copyright (C) 2022  Muntashir Al-Islam

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
```