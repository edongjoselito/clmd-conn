# candor

## Learning-area dashboard test accounts

Sign in at [http://localhost/clmd-conn](http://localhost/clmd-conn). Each account opens its assigned learning-area dashboard.

**Default test password for all accounts:** `LearnArea@2026`

| Learning area | Username |
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

Passwords can be changed directly on each dashboard. The password above applies until changed.

To create these test accounts in another local installation, run:

```sh
/Applications/XAMPP/xamppfiles/bin/php scripts/seed_learning_area_accounts.php
```

The script skips existing usernames and does not reset their passwords.
