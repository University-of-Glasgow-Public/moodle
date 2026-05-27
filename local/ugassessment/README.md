# local_ugassessment

Moodle local plugin that builds a structured snapshot of assessment data
(assignments, quizzes, etc.) and exposes it via a REST API for reporting
and external system integration.

---

## Features

- Builds a cached snapshot table of assessment data
- Incremental snapshot updates (no full rebuild required)
- Soft delete handling for removed activities
- REST API with:
  - Incremental sync (`since`)
  - Stable cursor-based paging (`timeextracted + cmid`)
  - Result limiting and paging (`limit`, `hasmore`)
- Filters courses by:
  - Custom field (e.g. academic year)
  - Optional StudentMyGrades flag
- Filters activities by:
  - Grade category keyword (e.g. "summative")
  - Activity type
- Extracts:
  - Assessment dates (due / close)
  - Tags
  - Activity-specific metadata (e.g. team submission)
- Admin tools:
  - Full rebuild
  - Incremental update
  - Reset snapshot
  - Export CSV
  - Record counts (including deleted)

---

## Snapshot Behaviour

The plugin stores assessment data in a snapshot table instead of querying Moodle live.

Benefits:
- Improved performance
- Consistent API output
- Enables incremental updates

Snapshot modes:

- Full rebuild (`reset=true`)
  - Deletes all records
  - Rebuilds from scratch
  - Resets deleted flags

- Incremental update (`reset=false`)
  - Inserts new records
  - Updates changed records only
  - Flags missing records as deleted

---

## Soft Delete

Records are never physically removed.

Fields:
- `deleted` (0/1)

If an activity no longer matches filters:
- `deleted = 1`
  - the activity is actually deleted from Moodle
  - the activity has been in the snapshot but due to the new settings or gradebook category it is no longer qualified

If it reappears:
- `deleted = 0`

---

## Installation

Place plugin in:

local/ugassessment

Then visit:

Site administration → Notifications

---

## Configuration

Site administration → Plugins → Local plugins → UG Assessment

Configure:

- Course custom field
- Field value
- Ignore StudentMyGrades
- Grade category keyword
- Included activity types
- Pagination limit

---

## Scheduled Task

Automatically updates snapshot every 15 minutes.

Location:
Site administration → Server → Scheduled tasks

---

## CLI Script

php local/ugassessment/cli/update_snapshot.php

---

## Web Service Setup

1. Enable web services
2. Enable REST protocol
3. Create service: UG Assessment Service
4. Add function: local_ugassessment_get_data
5. Create token
6. Assign capabilities:
   - webservice/rest:use
   - local/ugassessment:view

---

## API

Endpoint:

/webservice/rest/server.php

### Parameters

| Parameter | Description |
|----------|------------|
| since | Unix timestamp for incremental sync |
| lastcmid | Cursor for stable paging |
| limit | Max records per request (min:1, max is configurable: 1000, 2500, 5000, 10000) |

### Limit behaviour
| input | Output |
|----------|------------|
|no limit|uses config ✅|
|limit = 500|returns 500 ✅|
|limit = 5000, config=10000|returns 5000 ✅|
|limit = 20000, config=10000|returns 10000 ✅|

---

### Example Request

.../server.php?wstoken=XXX&wsfunction=local_ugassessment_get_data&moodlewsrestformat=json&since=1716400000&lastcmid=0&limit=1000

### Response example

```json
{
  "data": [
    {
      "id": 1,
      "coursefullname": "Aggregated course",
      "coursevisible": true,
      "academicyear": "26/27",
      "qualification": "UG",
      "semester": "Semester 1",
      "studentmygrades": "Yes",
      "cmid": 318,
      "assessmenttype": "assign",
      "activityname": "Assign01",
      "activityvisible": false,
      "timecloseordue": 1762473600,
      "teamsubmission": false,
      "tags": "!testtag1;!testtag2",
      "url": "http://localhost/mygrades-gu45/mod/assign/view.php?id=318",
      "timeextracted": 1779464679,
      "deleted": false,
    }
  ],
  "lastcmid": 326,
  "lasttime": 1779698232,
  "hasmore": true
}
```

---

## Paging Model

- Ordered by timeextracted ASC, cmid ASC
- Cursor-based paging (`since` + `lastcmid`)

- Boomi flow:
  - Call with `since=0`, `lastcmid=0`
  - Store `lasttime`, `lastcmid`

  - If `hasmore=true`:
    - Call with:
      - `since = lasttime`
      - `lastcmid = lastcmid`
    - Repeat until `hasmore=false`

  - If `hasmore=false`:
    - Next run should still call with:
      - `since = lasttime`
      - `lastcmid = lastcmid`

---

## Status

- Production-ready architecture
- Incremental sync implemented
- Cursor-based paging implemented
- Scheduled updates active
- API tested and looks good
