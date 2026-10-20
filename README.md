# candor

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

**Initial password for newly created accounts:** `LearnArea@2026`. This shared password is for controlled testing/development only; change it at first sign-in and do not use it for production accounts.

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
