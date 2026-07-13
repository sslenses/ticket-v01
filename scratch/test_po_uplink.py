import requests

session = requests.Session()

# 1. Get CSRF token
resp = session.get('http://localhost:8080/login')
csrf_token = ''
for line in resp.text.split('\n'):
    if 'name="_token"' in line:
        csrf_token = line.split('value="')[1].split('"')[0]
        break

# 2. Login
resp = session.post('http://localhost:8080/login', data={
    '_token': csrf_token,
    'email': 'admin@admin.com',
    'password': 'password'
})
# print(resp.status_code)

# 3. Create Ticket
resp = session.get('http://localhost:8080/')
csrf_token = ''
for line in resp.text.split('\n'):
    if 'name="_token"' in line:
        csrf_token = line.split('value="')[1].split('"')[0]
        break

resp = session.post('http://localhost:8080/api/tickets', json={
    'label': 'PO-UPLINK-00001',
    'user_name': 'Test User',
    'user_contact': '123456',
    'status': 'kirim_uji_terima_po',
    'jenis_layanan': 'Uplink',
    'keterangan_layanan': 'Test',
    'alamat': 'Test',
    'titik_koordinat': '0,0',
    'link_maps': 'http://maps',
    'backhaul': 'BH-01',
    'bandwidth': '10',
    'tenant_id': None,
    'destination_tenant_id': None,
    'source_device': 'SW-01',
    'destination_device': 'SW-02'
}, headers={
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrf_token,
    'Accept': 'application/json'
})
print("Create Ticket:", resp.status_code, resp.text)

# 4. Get Ticket UUID
ticket_data = resp.json()
if 'uuid' in ticket_data:
    ticket_uuid = ticket_data['uuid']
    # 5. Transition to Next Step
    resp = session.post(f'http://localhost:8080/api/tickets/{ticket_uuid}/status', json={
        'status': 'received_cable'
    }, headers={
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf_token,
        'Accept': 'application/json'
    })
    print("Transition Ticket to received_cable:", resp.status_code, resp.text)
