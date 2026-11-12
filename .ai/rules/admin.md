---
paths:
  - app/Http/Controllers/Admin/ProjectAdminController.php
---

# Admin

## Projects: council is the town; no project type or developer input
Per the client (Avenix, 2026-11-12) the project form no longer asks for project type, developer/authority or town/city. Council (required) doubles as the town: locationAttributes() copies it into the NOT NULL `city` column (falling back to a posted city/district/region for old clients), developer_name defaults to '' and project_type to "Land Project"/"Service Project". Display location with RealEstateProject::location_label and status with status_label; do not reintroduce developer_name or project_type in views.
