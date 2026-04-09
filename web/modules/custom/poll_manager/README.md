# Poll Manager — Drupal Module

Production-grade poll management system for Drupal 10/11. Designed as a clean service-oriented module.

---

## Features

- **Specialized Service Layer**:
  - `VoteManager`: Handles and validates vote submissions.
  - `PollResultManager`: Aggregates and calculates statistics.
  - `PollAvailabilityManager`: Checks global and per-poll status.
- **Custom Entities**: `PollQuestion`, `PollChoice`, `PollSubmission`.
- **Administrative UI**: Full management of polls and choices at `/admin/poll-manager/questions`.
- **Global Toggle**: Enable/disable all voting at `/admin/config/poll-manager/settings`.
- **Public Voting**: CMS voting form and block support.
- **REST API**: Standardized JSON endpoints with explicit error codes.
- **Database Integrity**: UNIQUE constraints on `(question_id, user_id)` and `(question_id, external_voter_identifier)`.

---

## Requirements

| Tool       | Version   |
| ---------- | --------- |
| PHP        | 8.2+      |
| Drupal     | 10 or 11  |
| MariaDB    | 10.6+     |

---

## Local Setup

### 1. Enable the module

```bash
drush en poll_manager basic_auth -y
drush cr
```

### 2. Configuration

Access `/admin/config/poll-manager/settings` to enable global voting.

---

## REST API

Base path: `/api/v1/polls`

### Endpoints

| Method | Path | Description |
| --- | --- | --- |
| `GET` | `/questions` | List active polls. |
| `GET` | `/questions/{uuid}` | Get specific poll details and choices. |
| `POST` | `/questions/{uuid}/vote` | Submit a vote. |
| `GET` | `/questions/{uuid}/results` | Get poll statistics. |

### Error Codes

- `VOTE_DUPLICATE`: User/External ID has already voted.
- `VOTING_DISABLED`: Global voting is turned off.
- `QUESTION_CLOSED`: Specific poll is inactive.
- `INVALID_CHOICE`: The selected choice does not belong to the poll.

---

## Architecture

The module follows a service-oriented architecture to decouple business logic from controllers and forms.

```
poll_manager/
├── src/
│   ├── Controller/
│   │   ├── Api/          # REST API Controllers
│   │   └── ...           # Admin and UI Controllers
│   ├── Entity/           # Content Entities
│   ├── Form/             # Admin, Settings, and Voting Forms
│   ├── Service/          # Domain Services (VoteManager, etc.)
│   └── Plugin/Block/     # Voting Block
├── config/
│   ├── install/          # Default configuration
│   └── schema/           # Config schema
├── poll_manager.info.yml
├── poll_manager.install  # DB constraints
├── poll_manager.routing.yml
└── poll_manager.services.yml
```
