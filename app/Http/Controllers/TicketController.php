<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Models\Ticket;
use App\Models\Tenant;
use App\Models\TicketLog;
use App\Services\TicketStateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    protected TicketStateService $stateService;

    /**
     * Create a new controller instance.
     */
    public function __construct(TicketStateService $stateService)
    {
        $this->stateService = $stateService;
    }

    /**
     * Display a listing of tickets.
     */
    public function index()
    {
        $tickets = Ticket::latest()->get();
        $tenants = Tenant::oldest()->get();

        $statusLabels = [
            'waiting_destination' => 'Menunggu Destinasi',
            'approved_destination' => 'Disetujui Destinasi',
            'approved_admin' => 'Disetujui Admin',
            'sended_cable' => 'Kabel Dikirim',
            'received_cable' => 'Kabel Diterima',
            'done' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        // Pre-map tickets to a clean array for safe JSON rendering in the view
        $ticketsJson = $tickets->map(function ($ticket) use ($statusLabels) {
            return [
                'id' => $ticket->id,
                'uuid' => $ticket->uuid,
                'label' => $ticket->label,
                'source' => $ticket->source_device,
                'destination' => $ticket->destination_device,
                'connector' => $ticket->connector_type,
                'status' => $ticket->status,
                'statusLabel' => $statusLabels[$ticket->status] ?? str_replace('_', ' ', $ticket->status),
                'user_name' => $ticket->getCableDetail('user_name'),
                'user_contact' => $ticket->getCableDetail('user_contact'),
                'notes' => $ticket->getCableDetail('notes'),
            ];
        });

        return view('dashboard', compact('tickets', 'tenants', 'ticketsJson'));
    }

    public function ticketList()
    {
        $tickets = Ticket::with('logs.user')->latest()->get();
        return view('ticket-list', compact('tickets'));
    }

    public function store(StoreTicketRequest $request)
    {
        Gate::authorize('create', Ticket::class);

        $validated = $request->validated();
        
        $sourceTenantId = $request->input('source_tenant_id');
        if ($sourceTenantId === 'NEW_TENANT' && $request->filled('new_source_tenant_name')) {
            $name = $request->input('new_source_tenant_name');
            $code = 'T-' . Str::upper(Str::slug($name, '-'));
            $tenant = Tenant::firstOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
            $sourceTenantId = $tenant->id;
        }

        $destTenantId = $request->input('destination_tenant_id');
        if ($destTenantId === 'NEW_TENANT' && $request->filled('new_destination_tenant_name')) {
            $name = $request->input('new_destination_tenant_name');
            $code = 'T-' . Str::upper(Str::slug($name, '-'));
            $tenant = Tenant::firstOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
            $destTenantId = $tenant->id;
        }

        unset($validated['source_tenant_id']);
        unset($validated['destination_tenant_id']);
        unset($validated['new_source_tenant_name']);
        unset($validated['new_destination_tenant_name']);

        $ticket = new Ticket($validated);
        $ticket->status = Ticket::STATUS_WAITING_DESTINATION;
        $ticket->uuid = (string) Str::uuid();
        $ticket->source_tenant_id = $sourceTenantId ?? $ticket->uuid;
        $ticket->destination_tenant_id = $destTenantId ?? $ticket->uuid;
        $ticket->save();

        return response()->json($ticket, 201);
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load('logs.user');
        
        $isPublic = !auth()->check();

        if ($isPublic) {
            if ($ticket->status === Ticket::STATUS_CANCELLED) {
                abort(403, 'Access to this ticket is restricted. Cancelled tickets are not viewable publicly.');
            }
            if ($ticket->status === Ticket::STATUS_DONE) {
                $completedLog = $ticket->logs()->where('to_state', Ticket::STATUS_DONE)->latest()->first();
                $completedAt = $completedLog ? $completedLog->created_at : $ticket->updated_at;

                if ($completedAt->addWeek()->isPast()) {
                    abort(403, 'Access to this ticket is restricted. Completed tickets are hidden from the public 1 week after completion.');
                }
            }
        }

        $tenants = Tenant::oldest()->get();

        // Pre-map ticket data for safe JSON rendering
        $ticketData = [
            'id' => $ticket->id,
            'uuid' => $ticket->uuid,
            'label' => $ticket->label,
            'source_device' => $ticket->source_device,
            'destination_device' => $ticket->destination_device,
            'source_tenant_id' => $ticket->source_tenant_id,
            'destination_tenant_id' => $ticket->destination_tenant_id,
            'connector_type' => $ticket->connector_type,
            'status' => $ticket->status,
            'length' => $ticket->getCableDetail('length', 0),
            'color' => $ticket->getCableDetail('color'),
            'type' => $ticket->getCableDetail('type'),
            'notes' => $ticket->getCableDetail('notes'),
            'user_name' => $ticket->getCableDetail('user_name'),
            'user_contact' => $ticket->getCableDetail('user_contact'),
            'backhaul' => $ticket->getCableDetail('backhaul'),
            'metro' => $ticket->getCableDetail('metro'),
            'destination_site' => $ticket->getCableDetail('destination_site'),
            'capacity' => $ticket->getCableDetail('capacity'),
            'alamat' => $ticket->getCableDetail('alamat'),
            'titik_koordinat' => $ticket->getCableDetail('titik_koordinat'),
            'link_maps' => $ticket->getCableDetail('link_maps'),
        ];

        // Pre-map logs data
        $logsData = $ticket->logs->map(function ($log) {
            return [
                'id' => $log->id,
                'from' => $log->from_state,
                'to' => $log->to_state,
                'user' => $log->user->name ?? 'System',
                'role' => $log->user->role ?? 'user',
                'time' => $log->created_at->format('H:i'),
                'date' => $log->created_at->format('d M Y'),
                'fab_file' => $log->fab_file,
                'ba_file' => $log->ba_file,
                'keterangan' => $log->keterangan,
            ];
        });

        return view('ticket-detail', compact('ticket', 'isPublic', 'tenants', 'ticketData', 'logsData'));
    }

    /**
     * Update the status of the ticket.
     */
    public function rollback(Ticket $ticket)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Hanya admin yang dapat mengembalikan tiket.'], 403);
        }

        $isPO = str_starts_with(strtoupper($ticket->label), 'PO-') || str_starts_with(strtoupper($ticket->label), 'UP-');
        $isSRV = str_starts_with(strtoupper($ticket->label), 'SRV-');

        $currentStatus = $ticket->status;
        $previousStatus = null;

        if ($currentStatus === Ticket::STATUS_APPROVED_DESTINATION) {
            $previousStatus = Ticket::STATUS_WAITING_DESTINATION;
        } elseif ($currentStatus === Ticket::STATUS_APPROVED_ADMIN) {
            $previousStatus = Ticket::STATUS_APPROVED_DESTINATION;
        } elseif ($currentStatus === Ticket::STATUS_SENDED_CABLE) {
            if ($isPO || $isSRV) {
                $previousStatus = Ticket::STATUS_APPROVED_DESTINATION;
            } else {
                $previousStatus = Ticket::STATUS_APPROVED_ADMIN;
            }
        } elseif ($currentStatus === Ticket::STATUS_RECEIVED_CABLE) {
            $previousStatus = Ticket::STATUS_SENDED_CABLE;
        } elseif ($currentStatus === Ticket::STATUS_DONE) {
            $previousStatus = Ticket::STATUS_RECEIVED_CABLE;
        }

        if (!$previousStatus) {
            return response()->json(['message' => 'Tidak dapat dikembalikan ke step sebelumnya dari status ini.'], 400);
        }

        $ticket->status = $previousStatus;
        $ticket->save();

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'from_state' => $currentStatus,
            'to_state' => $previousStatus,
            'keterangan' => 'Admin mengembalikan tiket ke step sebelumnya.',
        ]);

        return response()->json(['message' => 'Tiket berhasil dikembalikan ke step sebelumnya.']);
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        try {
            $status = $request->input('status');
            $isPO = str_starts_with(strtoupper($ticket->label), 'PO-') || str_starts_with(strtoupper($ticket->label), 'UP-');
            $isSRV = str_starts_with(strtoupper($ticket->label), 'SRV-');

            $rules = [
                'status' => 'required|string',
            ];

            if ($status === Ticket::STATUS_DONE && $isPO) {
                $rules['fab_file'] = 'required|file|mimes:pdf|max:51200';
                $rules['ba_file'] = 'required|file|mimes:pdf|max:51200';
                $rules['keterangan'] = 'required|string|max:5000';
            }
            if ($status === Ticket::STATUS_DONE && $isSRV) {
                $rules['keterangan'] = 'required|string|max:5000';
            }

            if ($status === Ticket::STATUS_RECEIVED_CABLE && $isPO) {
                $rules['btest_proof'] = 'required';
                $rules['qos_proof'] = 'required|file|image|mimes:png,jpg,jpeg|max:51200';
                $rules['ip_ptp'] = 'required|string|max:255';
                $rules['ip_public'] = 'required|string|max:255';
                $rules['vlan'] = 'nullable|string|max:255';
                $rules['device_name'] = 'nullable|string|max:255';
                $rules['device_port'] = 'nullable|string|max:255';
                $rules['source_device'] = 'nullable|string|max:255';
                $rules['destination_device'] = 'nullable|string|max:255';
            }

            $request->validate($rules);
            
            $statusToPolicyMap = [
                Ticket::STATUS_APPROVED_DESTINATION => 'approveDestination',
                Ticket::STATUS_APPROVED_ADMIN       => 'approveAdmin',
                Ticket::STATUS_SENDED_CABLE         => 'sendCable',
                Ticket::STATUS_RECEIVED_CABLE       => 'receiveCable',
                Ticket::STATUS_DONE                 => 'markDone',
                Ticket::STATUS_CANCELLED            => 'cancel',
            ];

            $policyMethod = $statusToPolicyMap[$status] ?? null;

            if (!$policyMethod || !method_exists(\App\Policies\TicketPolicy::class, $policyMethod)) {
                return response()->json(['message' => "Invalid transition action for status '{$status}'."], 422);
            }

            Gate::authorize($policyMethod, $ticket);

            $extraData = [];
            if ($status === Ticket::STATUS_DONE && $isPO) {
                if ($request->hasFile('fab_file')) {
                    $fabPath = $request->file('fab_file')->store('uploads/tickets/' . $ticket->uuid, 'public');
                    $extraData['fab_file'] = '/storage/' . $fabPath;
                }
                if ($request->hasFile('ba_file')) {
                    $baPath = $request->file('ba_file')->store('uploads/tickets/' . $ticket->uuid, 'public');
                    $extraData['ba_file'] = '/storage/' . $baPath;
                }
                if ($request->has('keterangan')) {
                    $extraData['keterangan'] = $request->input('keterangan');
                }
            }
            if ($status === Ticket::STATUS_DONE && $isSRV) {
                if ($request->has('keterangan')) {
                    $extraData['keterangan'] = $request->input('keterangan');
                }
            }

            if ($status === Ticket::STATUS_RECEIVED_CABLE && $isPO) {
                $cableDetails = $ticket->cable_details ?? [];
                
                if ($request->hasFile('btest_proof')) {
                    $btestFiles = $request->file('btest_proof');
                    if (!is_array($btestFiles)) {
                        $btestFiles = [$btestFiles];
                    }
                    $btestPaths = [];
                    foreach ($btestFiles as $file) {
                        $btestPaths[] = '/storage/' . $file->store('uploads/tickets/' . $ticket->uuid, 'public');
                    }
                    $cableDetails['btest_proof'] = $btestPaths;
                }
                if ($request->input('remove_existing_qos') === '1') {
                    unset($cableDetails['qos_proof']);
                }
                if ($request->hasFile('qos_proof')) {
                    $qosPath = $request->file('qos_proof')->store('uploads/tickets/' . $ticket->uuid, 'public');
                    $cableDetails['qos_proof'] = '/storage/' . $qosPath;
                }
                
                $cableDetails['ip_ptp'] = $request->input('ip_ptp');
                $cableDetails['ip_public'] = $request->input('ip_public');
                $cableDetails['vlan'] = $request->input('vlan');
                $cableDetails['device_name'] = $request->input('device_name');
                $cableDetails['device_port'] = $request->input('device_port');
                
                $ticket->update([
                    'cable_details' => $cableDetails,
                    'source_device' => $request->input('source_device'),
                    'destination_device' => $request->input('destination_device'),
                ]);
            }

            try {
                $this->stateService->transition($ticket, $status, $extraData);
            } catch (\InvalidArgumentException $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return response()->json($ticket);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return response()->json([
                'message' => $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine(),
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Update the specified ticket details.
     */
    public function update(Request $request, Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        $validated = $request->validate([
            'label' => 'required|string|unique:tickets,label,' . $ticket->id,
            'source_device' => 'nullable|string',
            'destination_device' => 'nullable|string',
            'source_tenant_id' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value !== 'NEW_TENANT' && !\Illuminate\Support\Str::isUuid($value)) {
                        $fail('The selected ' . str_replace('_', ' ', $attribute) . ' is invalid.');
                    }
                }
            ],
            'new_source_tenant_name' => 'required_if:source_tenant_id,NEW_TENANT|nullable|string|max:255',
            'destination_tenant_id' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value !== 'NEW_TENANT' && !\Illuminate\Support\Str::isUuid($value)) {
                        $fail('The selected ' . str_replace('_', ' ', $attribute) . ' is invalid.');
                    }
                }
            ],
            'new_destination_tenant_name' => 'required_if:destination_tenant_id,NEW_TENANT|nullable|string|max:255',
            'connector_type' => 'nullable|string',
            'cable_details' => 'required|array',
            'cable_details.user_name' => 'required|string',
            'cable_details.user_contact' => 'nullable|string',
            'cable_details.backhaul' => 'nullable|string',
            'cable_details.metro' => 'nullable|string',
            'cable_details.destination_site' => 'nullable|string',
            'cable_details.capacity' => 'nullable|string',
            'cable_details.length' => 'nullable|integer',
            'cable_details.color' => 'nullable|string',
            'cable_details.type' => 'nullable|string',
            'cable_details.notes' => 'nullable|string',
            'cable_details.alamat' => 'nullable|string',
            'cable_details.titik_koordinat' => 'nullable|string',
            'cable_details.link_maps' => 'nullable|string',
            'cable_details.ip_ptp' => 'nullable|string',
            'cable_details.ip_public' => 'nullable|string',
            'cable_details.vlan' => 'nullable|string',
            'cable_details.device_name' => 'nullable|string',
            'cable_details.device_port' => 'nullable|string',
            'btest_proof' => 'nullable',
            'qos_proof' => 'nullable|file|image|mimes:png,jpg,jpeg|max:51200',
        ]);

        $sourceTenantId = $request->input('source_tenant_id', $ticket->source_tenant_id);
        if ($sourceTenantId === 'NEW_TENANT' && $request->filled('new_source_tenant_name')) {
            $name = $request->input('new_source_tenant_name');
            $code = 'T-' . Str::upper(Str::slug($name, '-'));
            $tenant = Tenant::firstOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
            $sourceTenantId = $tenant->id;
        }

        $destTenantId = $request->input('destination_tenant_id', $ticket->destination_tenant_id);
        if ($destTenantId === 'NEW_TENANT' && $request->filled('new_destination_tenant_name')) {
            $name = $request->input('new_destination_tenant_name');
            $code = 'T-' . Str::upper(Str::slug($name, '-'));
            $tenant = Tenant::firstOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
            $destTenantId = $tenant->id;
        }

        unset($validated['new_source_tenant_name']);
        unset($validated['new_destination_tenant_name']);
        unset($validated['source_tenant_id']);
        unset($validated['destination_tenant_id']);

        $cableDetails = $ticket->cable_details ?? [];

        // For BTest
        $keptBtests = $request->input('existing_btests', []);
        $newBtests = [];
        if ($request->hasFile('btest_proof')) {
            $btestFiles = $request->file('btest_proof');
            if (!is_array($btestFiles)) {
                $btestFiles = [$btestFiles];
            }
            foreach ($btestFiles as $file) {
                $newBtests[] = '/storage/' . $file->store('uploads/tickets/' . $ticket->uuid, 'public');
            }
        }
        $mergedBtests = array_merge($keptBtests, $newBtests);
        if (!empty($mergedBtests)) {
            $cableDetails['btest_proof'] = $mergedBtests;
        } else {
            unset($cableDetails['btest_proof']);
        }

        // For QoS
        if ($request->filled('existing_qos')) {
            $cableDetails['qos_proof'] = $request->input('existing_qos');
        } else {
            unset($cableDetails['qos_proof']);
            if ($request->hasFile('qos_proof')) {
                $qosPath = $request->file('qos_proof')->store('uploads/tickets/' . $ticket->uuid, 'public');
                $cableDetails['qos_proof'] = '/storage/' . $qosPath;
            }
        }

        $validated['cable_details'] = array_merge($cableDetails, $validated['cable_details'] ?? []);
        $validated['source_tenant_id'] = $sourceTenantId;
        $validated['destination_tenant_id'] = $destTenantId;
        unset($validated['btest_proof']);
        unset($validated['qos_proof']);

        $ticket->update($validated);

        \App\Models\TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'from_state' => 'edit_details',
            'to_state' => 'edit_details',
        ]);

        return response()->json($ticket);
    }

    /**
     * Add a note to the specified ticket.
     */
    public function addNote(Request $request, Ticket $ticket)
    {
        $request->validate([
            'keterangan' => 'required|string|max:5000',
        ]);

        \App\Models\TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'from_state' => $ticket->status,
            'to_state' => $ticket->status,
            'keterangan' => $request->input('keterangan'),
        ]);

        return response()->json([
            'message' => 'Catatan berhasil ditambahkan.',
            'ticket' => $ticket->load('logs.user')
        ]);
    }

    /**
     * Delete the specified ticket.
     */
    public function destroy(Ticket $ticket)
    {
        Gate::authorize('delete', $ticket);

        $ticket->logs()->delete();
        $ticket->delete();

        return response()->json(['message' => 'Tiket berhasil dihapus.']);
    }
}
