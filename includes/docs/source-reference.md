# Boards source reference

> Generated from the current checkout and then intended for human review.
> Paths are relative to the package root.

## Inventory summary

| Artifact | Count |
|---|---:|
| PHP files | 29 |
| Smarty templates | 24 |
| JavaScript files | 0 |
| CSS files | 2 |

## Bootstrap and schema artifacts

- `admin/schema_inc.php`
- `admin/upgrade_inc.php`
- `admin/upgrades/1.0.1.php`
- `includes/bit_setup_inc.php`

## First-party classes and interfaces

- `includes/classes/BitBoard.php:27` — `class BitBoard extends LibertyMime {`
- `includes/classes/BitBoardPost.php:23` — `class BitBoardPost extends LibertyComment {`
- `includes/classes/BitBoardTopic.php:29` — `class BitBoardTopic extends LibertyMime {`

## Web-facing PHP controllers

- `admin/admin_boards_inc.php`
- `admin/boardsync_cron.php`
- `admin/boardsync_inc.php`
- `admin/phpbb_migrate.php`
- `admin/schema_inc.php`
- `admin/update_lists.php`
- `admin/upgrade_inc.php`
- `admin/upgrades/1.0.1.php`
- `ajax.php`
- `assign.php`
- `boards_rss.php`
- `edit.php`
- `edit_topic.php`
- `index.php`
- `mailing_list.php`
- `modules/mod_recent_posts.php`
- `rss.php`
- `topic_move.php`

## Declared schema tables

- `boards`
- `boards_map`
- `boards_posts`
- `boards_sections`
- `boards_topics`
- `boards_tracking`

## Plugin and module directories

- `modules/`

## Templates

- `modules/help_mod_recent_posts.tpl`
- `modules/mod_recent_posts.tpl`
- `templates/admin_boards.tpl`
- `templates/ajax.tpl`
- `templates/board_assign.tpl`
- `templates/board_cat.tpl`
- `templates/board_edit.tpl`
- `templates/board_table.tpl`
- `templates/boards_edit_mini_inc.tpl`
- `templates/boards_rss_form.tpl`
- `templates/boards_service_icons.tpl`
- `templates/comment_post.tpl`
- `templates/flipswitch.tpl`
- `templates/html_head_inc.tpl`
- `templates/legend_inc.tpl`
- `templates/list_boards.tpl`
- `templates/list_posts.tpl`
- `templates/list_topics.tpl`
- `templates/mailing_list.tpl`
- `templates/menu_boards.tpl`
- `templates/menu_boards_admin.tpl`
- `templates/post_display.tpl`
- `templates/topic_move.tpl`
- `templates/user_preferences_inc.tpl`

## Reading cautions

- Presence in this inventory does not make a file a supported public API.
- Bundled third-party libraries must be distinguished from package-owned code.
- Base schema files do not prove the migration state of a deployed database.
- Controllers may rely on include files, globals, services, and template callbacks not visible from their filename alone.
