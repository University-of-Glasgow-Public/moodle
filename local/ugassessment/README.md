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
- `timeextracted` is the current time

---


## Course Codes Handling

MyCampus Course Codes and Subjects are sourced from:

mdl_enrol_gudatabase_codes

Rules:

- A course can have:
  - ✅ 0 codes → `coursecode = null`
  - ✅ 1 code → stored normally
  - ❌ Multiple codes → `coursecode = MULTIPLE_CODES`

- When multiple codes are detected:
  - The course is still included in the extract
  - `coursesubject` is set to null
  - A warning is logged in cron output:
    - Example:
      UGAssessment WARNING: multiple codes found for courseids: 12,45,78

This ensures:
- Data consistency in the API
- Visibility of data issues without dropping records


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
            "id": 2,
            "coursefullname": "MyGrades Test course for UofG user grade report",
            "coursecode": "ENG2025",
            "coursesubject": "ENG",
            "coursevisible": true,
            "academicyear": "26/27",
            "qualification": "UG",
            "semester": "Semester 1",
            "studentmygrades": "Yes",
            "cmid": 52115,
            "assessmenttype": "assign",
            "activityname": "Assign02",
            "activityvisible": true,
            "timecloseordue": 1760569200,
            "timelimit": 0,
            "teamsubmission": false,
            "tags": "",
            "url": "https://learntest5.gla.ac.uk/mod/assign/view.php?id=52115",
            "timeextracted": 1779972574,
            "deleted": false
        },
        {
            "id": 1,
            "coursefullname": "MyGrades Test course for UofG user grade report",
            "coursecode": "ENG2025",
            "coursesubject": "ENG",
            "coursevisible": true,
            "academicyear": "26/27",
            "qualification": "UG",
            "semester": "Semester 1",
            "studentmygrades": "Yes",
            "cmid": 47034,
            "assessmenttype": "assign",
            "activityname": "Assign01",
            "activityvisible": true,
            "timecloseordue": 1760569200,
            "timelimit": 3600,
            "teamsubmission": false,
            "tags": "",
            "url": "https://learntest5.gla.ac.uk/mod/assign/view.php?id=47034",
            "timeextracted": 1779972662,
            "deleted": false
        }
    ],
    "hasmore": false,
    "lasttime": 1779972662,
    "lastcmid": 47034
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
