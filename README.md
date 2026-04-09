# Poll Management Project

A robust Poll Management system built on Drupal 11. This project features a custom-built, production-grade module (`poll_manager`) that provides a decoupled voting architecture, RESTful API, and administrative tools.

## Key Components

### 1. Poll Manager Module
The core of the system, located in `web/modules/custom/poll_manager`. 

- **PollAvailabilityManager**: Manages availability states (global and per-poll).
- **VoteManager**: Processes and persists votes with duplicate prevention.
- **PollResultManager**: Aggregates and calculates poll statistics.

### 2. Entities
- **Poll Question**: The main poll item with status and result visibility control.
- **Poll Choice**: Options for a specific poll, supporting images and descriptions.
- **Poll Submission**: Records of votes, supporting both logged-in and external API voters.

### 3. REST API
The system exposes a standardized API for external integrations:
- `GET /api/v1/polls/questions`: List active polls.
- `GET /api/v1/polls/questions/{uuid}`: Poll details and choices.
- `POST /api/v1/polls/questions/{uuid}/vote`: Cast a vote.
- `GET /api/v1/polls/questions/{uuid}/results`: Real-time results.

## Technical Requirements
- **Drupal**: 11.x
- **PHP**: 8.3+
- **Database**: mariadb:10.11

## Setup Instructions

1. **Install Dependencies**:
   ```bash
   lando composer install
   ```

2. **Import the database**:
   ```bash
   lando db-import dump/simplevoting.sql
   ```

3. **Configure Settings**:
   Navigate to `/admin/config/poll-manager/settings` to enable global voting.

4. **Manage Polls**:
   Use the administration interface at `/admin/poll-manager/questions` to create and manage polls.

## Architecture & Integrity
The project prioritizes data integrity and performance:
- **Database Constraints**: Critical unique keys prevent duplicate votes at the schema level.
- **Service Decoupling**: Business logic is isolated from the delivery layer (Forms/API).
- **Strict Typing**: Modern PHP 8.3 features used throughout the codebase.

---
