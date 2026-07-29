# Boards package documentation

> Engineering documentation derived from the source in this package. The
> package's `includes/` directory must be denied to direct HTTP requests.

## Purpose

Boards provides discussion forums, topics, posts, moderation, and forum-oriented Liberty content.

## Responsibility

Owns board and topic organization, post workflows, subscriptions, and forum presentation.

## Dependencies

kernel, liberty, users, themes.

Dependency direction matters: this package may depend on the packages above;
the dependencies do not thereby depend on this package.

## Boundary

Does not replace Liberty comments or own site-wide authentication.

## Documentation map

- [Architecture](architecture.md) — initialization, components, and request flow.
- [Source reference](source-reference.md) — source-derived files, classes,
  controllers, schema artifacts, plugins, and templates.
- [Development guide](development.md) — safe change workflow, extension points,
  validation, and maintenance guidance.
- [Security](security.md) — trust boundaries and direct-HTTP access requirements.
- [Discussion model](discussion-model.md) — boards, topics, posts, threading,
  tracking, moderation, notifications, and Liberty integration.
