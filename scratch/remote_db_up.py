import pexpect
import sys

HOST = "110.76.147.65"
USER = "root"
PASSWORD = "password~"

child = pexpect.spawn(f"ssh -o StrictHostKeyChecking=no {USER}@{HOST}", encoding='utf-8')
child.expect("password:")
child.sendline(PASSWORD)
child.expect("#")

print("Starting docker compose...")
child.sendline("cd ~/ticket-v01 && docker compose up -d")
child.expect("#", timeout=120)
print(child.before)

child.sendline("exit")
