# Boards discussion model

## Content hierarchy

```text
BitBoard extends LibertyMime
BitBoardTopic extends LibertyMime
BitBoardPost extends LibertyComment
```

A board is a Liberty content container. Topics are Liberty content mapped to a
board. Posts reuse Liberty's comment/thread model, with Boards adding moderation
metadata.

## Tables

- `boards` — board identity, Liberty `content_id`, section, and position.
- `boards_sections` — optional board grouping.
- `boards_map` — board-content to topic-content mapping.
- `boards_topics` — topic state keyed by root/parent content: locked, moved,
  sticky, and migration identity.
- `boards_posts` — post moderation state keyed to `liberty_comments.comment_id`.
- `boards_tracking` — per-user topic read/notification state.

Text, ownership, status, attachments, and history remain in Liberty tables.

## Board lifecycle

`BitBoard::verify()` and `store()` coordinate the Liberty row and board-specific
row. Mapping helpers add/remove related topic content. `verifyIntegrity()` and
`fixContentMap()` exist because board/topic mappings can drift; normal code
should preserve them transactionally rather than relying on repair.

Expunging a board must define what happens to mapped topics/posts. Do not delete
Liberty content by manipulating only `boards` or `boards_map`.

## Topic lifecycle

`BitBoardTopic` loads the root content and board context. It owns lock, sticky,
move, read-tracking, and notification behavior.

- `lock()` affects whether new replies are allowed; authorization is separate.
- `sticky()` affects ordering/presentation.
- `moveTo()` must update mapping without losing the topic/post thread.
- `readTopic()`/`readTopicSet()` maintain user tracking.
- `notify()` and notification helpers manage subscription state.

Validate board visibility when moving or displaying a topic; access to the
topic alone must not expose a protected destination board.

## Posts and moderation

`BitBoardPost` extends `LibertyComment`, so comment verification/storage remains
foundational. Boards adds approval, warning, and moderation methods:
`modApprove()`, `modReject()`, and `modWarn()`.

Moderation state is not equivalent to Liberty content status. List and count
queries must consistently exclude unapproved/rejected material for callers who
lack moderation authority.

## Service integration

Boards registers Liberty callbacks for content display/edit/store/verify/
expunge and comment storage. These callbacks connect generic content/comment
events to linked boards and must tolerate content types that are not board
objects.

## Permissions

Registered defaults distinguish board administration, creation, update, post
update, read, and removal. Apply Liberty object permissions in addition to
package permissions. Locking, stickying, moving, warning, and approval require
explicit moderator/admin authority.

## Notifications

Notification sends should occur only after durable post state. Avoid duplicates
on retries. Do not disclose private board titles/content in mail to users who
lost access after subscribing.

## Testing

- Board create/update/expunge and section ordering.
- Topic create, lock, sticky, move, and map integrity.
- Nested/flat post display modes.
- Moderated versus normal user lists and counts.
- Read/unread tracking and notification toggles.
- Access revocation after subscription.
