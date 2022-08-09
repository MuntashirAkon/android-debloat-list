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
- `description` (string) - Description of the package under 200 words. This might include investigation report.
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
- `reason` (string) _optional_ - Why this is suggested, under 100 words.
- `source` (string) _optional_ - Source of the app. This is a predefined value, could be `fgas` where `f` stands for
  F-Droid, `g` for Google Play Store, `a` for Amazon Appstore and `s` for Samsung Galaxy Store.
- `repo` (string) - Link to the project repository.

#### Criteria for suggestions

1. It has to be free and open source having an OSI approved license.
2. Interface or functions (UI/UX) should be very or nearly similar to the original app or even better. If no such
   alternative is available right now, the `reason` must be included.
3. For crucial apps, for example, messaging apps, only audited apps might be listed. Unaudited yet popularly used apps
   should contain sufficient warning in the `reason` section.
4. The app should not contain any trackers. If it does contain any reasonable tracker such as crash reporting framework,
   it should be disabled by default. If there are no such alternative, the `reason` section must be updated with
   sufficient warning.
5. No credits or endorsement is allowed. If a suggested app contain or advertise its inclusion in this list, the app
   will be removed from the list in immediate effect.

## Contributing

The repository is divided into several folders to make it easier for the first-time contributors as well as those who
do not have access to a personal computer.

Each json file represents a list of debloatling materials for a category. For example, `aosp.json` contains a list of
apps that comes prebuilt with the AOSP itself, `oem.json` contains a list of apps from all the vendors or OEMs. The list
names are very generic, and if you have a new idea for a list, it has to be generic too. For example, apps related to
productivity can be separated from `misc.json` under a new json file. All entries must appear in the alphabetic order
based on their `id`.

`suggestions` folder contains a list of suggestions for a certain category. The filename of each list is considered its
_ID_ and should not contain any non-ASCII or whitespace characters. It is done this way because multiple apps could be
assigned the same set of suggestions.

To contribute, read the schema section above and make a pull request with the desired changes. Minor changes should be
accepted without much review, but major changes such as the addition of a new package require a review. The review
process is decided by the maintainer based on the type of change.

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