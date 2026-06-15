# Product Requirements Document: General Ticketing System

## 1. Overview
The General Ticketing System is a module built with Laravel and PostgreSQL designed to manage the lifecycle of equipment and cable deployment. It tracks technical metadata and enforces a strict 6-stage progression with mandatory audit logging.

## 2. Technical Stack
- **Framework:** Laravel (PHP)
- **Database:** PostgreSQL
- **Key Patterns:** State Machine, RBAC, Audit Logging.

## 3. Metadata Requirements
Every ticket must store the following technical details:
- `source_device`: (String) Originating equipment identifier.
- `destination_device`: (String) Target equipment identifier.
- `source_tenant`: (UUID/ID) Tenant owning the source device.
- `destination_tenant`: (UUID/ID) Tenant owning the destination device.
- `connector_type`: (Enum) Type of interface (e.g., LC, SC, RJ45).
- `label`: (String, Unique) Human-readable ticket identifier.
- `cable_details`: (JSON) Optional specs such as length, color, and fiber type.

## 4. State Machine (6 Stages)
Tickets must progress through these stages in order:
1. **Waiting Destination:** Initial state upon creation.
2. **Approved Destination:** Destination verified by the Destination Manager or Admin.
3. **Approved Admin:** Final administrative review and approval.
4. **Sended Cable:** Cable is dispatched or in transit.
5. **Received Cable:** Cable has arrived at the destination.
6. **Done:** Deployment is complete and verified.

## 5. RBAC & Validation Matrix
| Transition | Allowed Roles | Validation Rules |
| :--- | :--- | :--- |
| **Create (Waiting Dest)** | Staff, Dest. Manager, Admin | `label` must be unique. |
| **Approve Destination** | Dest. Manager, Admin | Must be in `Waiting Destination`. |
| **Approve Admin** | Admin | Must be in `Approved Destination`. |
| **Send Cable** | Admin | Must be in `Approved Admin`. |
| **Receive Cable** | Admin | Must be in `Sended Cable`. |
| **Mark Done** | Admin | Must be in `Received Cable`. |

## 6. Audit Logging
Every state transition MUST be recorded in a dedicated `ticket_logs` table:
- `ticket_id`: Reference to the ticket.
- `user_id`: The ID of the user executing the transition.
- `from_state`: Previous state.
- `to_state`: New state.
- `timestamp`: Precise time of the transition.

## 7. User Stories
- **As Staff:** I want to create a ticketing request with full technical metadata so that it can be processed.
- **As Destination Manager:** I want to approve tickets destined for my tenant so that I can control incoming deployments.
- **As Admin:** I want to manage the full lifecycle (Admin Approval -> Done) to ensure system-wide integrity.
