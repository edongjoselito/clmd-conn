# candor

## Learning-area dashboard themes

Each of the nine learning areas has an independent visual theme, used on its regional and division dashboards and on `/region/learning-areas`. Customize its `primary`, `dark`, `soft`, `tagline`, and `logo` values in `application/config/learning_area_dashboards.php`. Individual SVG logos are in `assets/images/learning-areas/`; shared theme styling is in `assets/css/learning-area-themes.css`. Keep text/background contrast readable when changing colors. These are project-designed subject symbols, not official departmental seals.

## Regional learning-area dashboard accounts

Seed the default regional learning-area accounts from the repository root:

```sh
/Applications/XAMPP/xamppfiles/bin/php scripts/seed_learning_area_accounts.php
```

By default, accounts are assigned to Region XI (region ID `12`). To create the same set for another region, pass that region's ID:

```sh
/Applications/XAMPP/xamppfiles/bin/php scripts/seed_learning_area_accounts.php 13
```

For Region XI, the usernames are listed below. For another region, the script appends `_r<region_id>` to each username (for example, `la_filipino_r13`). These seeded accounts are regional-level accounts: they have no division assignment and can view matching learning-area records across all divisions in their assigned region.

Learning-area accounts can be regional-level or division-level. Regional managers can create either from User Management: leave Division unselected for regional access, or assign a division for division-only access. Division and ICT managers can create learning-area accounts only for their own division. The account's assigned learning area and level determine which records it can view.

The script creates missing regional accounts and assigns existing matching default accounts to the requested region only when they have no region assigned. It does not overwrite existing passwords or change accounts already assigned to a region. Newly created accounts require a password change at first sign-in.

**Initial password for regional accounts newly created by the seed script:** `LearnArea@2026`. This shared password is for controlled testing/development only; change it at first sign-in and do not use it for production accounts. Existing regional accounts retain their passwords. The Davao Oriental division accounts below have separate temporary passwords.

Sign in at [http://localhost/clmd-conn](http://localhost/clmd-conn). Each account opens its assigned learning-area dashboard.

| Learning area | Region XI username |
| --- | --- |
| Filipino | `la_filipino` |
| English | `la_english` |
| Mathematics | `la_mathematics` |
| Science | `la_science` |
| Araling Panlipunan (AP) | `la_ap` |
| Good Manners and Right Conduct (GMRC) / Values Education (VE) | `la_gmrc_ve` |
| Music and Arts | `la_music_arts` |
| Physical Education and Health | `la_pe_health` |
| Technology and Livelihood Education (TLE) | `la_tle` |

## Davao Oriental division learning-area accounts

Created on October 21, 2026, for Davao Oriental (division ID `61`) in Region XI – Davao (region ID `12`). There is one division account per learning area, separate from its regional account. For example, `la_filipino` views Filipino records across Region XI, while `la_filipino_davao_oriental` views Filipino records only within Davao Oriental.

Sign in at [http://localhost/clmd-conn](http://localhost/clmd-conn) using the credentials below. Each account requires a password change at first sign-in; these temporary passwords stop working after they are changed.

| Learning area | Davao Oriental username | Temporary password |
| --- | --- | --- |
| Filipino | `la_filipino_davao_oriental` | `615e819b787365b2Aa!` |
| English | `la_english_davao_oriental` | `455b30467379d166Aa!` |
| Mathematics | `la_mathematics_davao_oriental` | `9a3e291f86a7978aAa!` |
| Science | `la_science_davao_oriental` | `9fa6122f4050d0ddAa!` |
| Araling Panlipunan (AP) | `la_ap_davao_oriental` | `570c98589d89d00eAa!` |
| GMRC / Values Education (VE) | `la_gmrc_ve_davao_oriental` | `d4656d54c4533a22Aa!` |
| Music and Arts | `la_music_arts_davao_oriental` | `cafcde5c0863d396Aa!` |
| Physical Education and Health | `la_pe_health_davao_oriental` | `fe38fe1d3dfbc482Aa!` |
| Technology and Livelihood Education (TLE) | `la_tle_davao_oriental` | `a4cb65d2e72f02eeAa!` |

These nine division accounts were provisioned separately; the regional seed script above does not create them. Account assignments and password hashes were verified after creation, and the existing regional/division learning-area access-scope test passed.
