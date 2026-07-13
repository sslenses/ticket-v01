import requests
import json

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

# 3. Create Ticket
resp = session.get('http://localhost:8080/')
csrf_token = ''
for line in resp.text.split('\n'):
    if 'name="_token"' in line:
        csrf_token = line.split('value="')[1].split('"')[0]
        break

resp = session.post('http://localhost:8080/api/tickets', json={
    'label': 'UP-00003',
    'user_name': 'Test User',
    'user_contact': '123456',
    'status': 'draft',
    'jenis_layanan': 'Uplink',
    'keterangan_layanan': 'Test',
    'alamat': 'Test',
    'titik_koordinat': '0,0',
    'link_maps': 'http://maps',
    'backhaul': 'BH-01',
    'bandwidth': '10',
    'cable_details': {
        'user_name': 'Test'
    }
}, headers={
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrf_token,
    'Accept': 'application/json'
})

ticket_data = resp.json()
print("Create Ticket:", resp.status_code)
if 'uuid' in ticket_data:
    ticket_uuid = ticket_data['uuid']
    # 4. Add Note
    resp = session.post(f'http://localhost:8080/api/tickets/{ticket_uuid}/note', json={
        'keterangan': 'Ini adalah catatan pengujian.'
    }, headers={
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf_token,
        'Accept': 'application/json'
    })
    print("Add Note:", resp.status_code, resp.text)
else:
    print(ticket_data)
