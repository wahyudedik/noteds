# RBAC Policy

## Roles
- admin: full access (create/read/update/delete/export)
- analyst: create/read/export; no update/delete of events
- viewer: read (summary-only); no create/export/update/delete

## Resources
- Analytics Events: `/api/analytics/events`, `/analytics/dashboard`, `/analytics/events/export`

## Permissions Matrix
| Role   | create | read | update | delete | export |
|--------|--------|------|--------|--------|--------|
| admin  | yes    | yes  | yes    | yes    | yes    |
| analyst| yes    | yes  | no     | no     | yes    |
| viewer | no     | limited summary | no     | no     | no     |

## Examples
- Viewer membuka dashboard: mendapat ringkasan agregat; raw events disembunyikan.
- Analyst mengekspor data: diizinkan; gunakan `/analytics/events/export`.
- Viewer mengekspor data: ditolak oleh middleware dengan HTTP 403.

## Menambah Role/Permission
- Tambahkan logic di `User::hasRole()`.
- Perbarui `AnalyticsEventPolicy` untuk role baru.
- Terapkan middleware/custom gate pada route terkait.
