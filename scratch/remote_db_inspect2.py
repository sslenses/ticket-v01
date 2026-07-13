import pexpect
import sys

HOST = "110.76.147.65"
USER = "root"
PASSWORD = "password~"

child = pexpect.spawn(f"ssh -o StrictHostKeyChecking=no {USER}@{HOST}", encoding='utf-8')
child.expect("password:")
child.sendline(PASSWORD)
child.expect("#")

child.sendline("docker inspect --format='{{json .State.Health}}' ticket-v01-db-1 | python3 -m json.tool")
child.expect("#")
print(child.before)

child.sendline("exit")
