import pexpect
import sys
import time

def run_ssh_commands(host, password, commands):
    print(f"Connecting to {host}...")
    child = pexpect.spawn(f'ssh -o StrictHostKeyChecking=no {host}', encoding='utf-8')
    child.logfile = sys.stdout

    index = child.expect(['[Pp]assword:', pexpect.EOF, pexpect.TIMEOUT], timeout=30)
    if index == 0:
        child.sendline(password)
        child.expect(['#', '\\$'], timeout=15)

        for cmd in commands:
            print(f"\n--- Running remote command: {cmd} ---")
            child.sendline(cmd)
            child.expect(['#', '\\$'], timeout=600)

        child.sendline('exit')
        child.expect(pexpect.EOF, timeout=60)
        print("\n--- SSH commands completed successfully. ---")
        return True
    else:
        print("SSH connection failed.")
        return False

def sync_codebase(host, password, local_path, remote_path):
    print(f"Syncing codebase from {local_path} to {host}:{remote_path}...")
    cmd = (
        f"rsync -avz -e 'ssh -o StrictHostKeyChecking=no' "
        f"--exclude 'node_modules' --exclude 'vendor' --exclude '.git' "
        f"{local_path}/ {host}:{remote_path}"
    )
    child = pexpect.spawn(cmd, encoding='utf-8')
    child.logfile = sys.stdout

    index = child.expect(['[Pp]assword:', pexpect.EOF, pexpect.TIMEOUT], timeout=30)
    if index == 0:
        child.sendline(password)
        child.expect(pexpect.EOF, timeout=300)
        print("\n--- Code sync completed successfully. ---")
        return True
    else:
        print("Rsync failed to initiate.")
        return False

def main():
    host = "root@110.76.147.65"
    password = "password~"
    local_path = "/home/sidiq/Projek/ticket-v01"
    remote_path = "/root/ticket-v01"

    # Stop containers and remove volumes first to clean the ransomware!
    pre_cmds = [
        f"cd {remote_path}",
        "docker compose down -v"
    ]
    if not run_ssh_commands(host, password, pre_cmds):
        sys.exit(1)

    if not sync_codebase(host, password, local_path, remote_path):
        sys.exit(1)

    remote_cmds = [
        f"cd {remote_path}",
        "docker compose up -d --build",
        "docker compose exec -T app php artisan config:clear",
        "docker compose exec -T app php artisan cache:clear",
        "docker compose exec -T app php artisan view:clear"
    ]
    if not run_ssh_commands(host, password, remote_cmds):
        sys.exit(1)

    print("=============================================")
    print("DEPLOYMENT SUCCESSFUL!")
    print("=============================================")

if __name__ == "__main__":
    main()
