import pexpect
import sys

HOST = "110.76.147.65"
USER = "root"
PASSWORD = "K@m4r00t2026!"

try:
    print(f"Connecting to {USER}@{HOST}...")
    child = pexpect.spawn(f"ssh -o StrictHostKeyChecking=no {USER}@{HOST}", encoding='utf-8')
    child.expect("password:")
    child.sendline(PASSWORD)
    child.expect("#")
    
    print("Fetching db logs...")
    child.sendline("cd ~/ticket-v01 && docker compose logs db --tail=50")
    child.expect("#")
    print(child.before)
    
    child.sendline("exit")
    child.expect(pexpect.EOF)
except Exception as e:
    print(f"Error: {e}")
