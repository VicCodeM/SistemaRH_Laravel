import paramiko

def fix_composer():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        commands = [
            "cd /home/victor/sistemarh && composer install --no-dev --optimize-autoloader",
            "cd /home/victor/sistemarh && php artisan optimize:clear"
        ]
        
        for cmd in commands:
            print(f"--- Running: {cmd} ---")
            stdin, stdout, stderr = ssh.exec_command(cmd)
            out = stdout.read().decode('utf-8', errors='replace').strip()
            err = stderr.read().decode('utf-8', errors='replace').strip()
            if out: print(out.encode('ascii', 'replace').decode('ascii'))
            if err: print(f"Error: {err.encode('ascii', 'replace').decode('ascii')}")
            
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    fix_composer()
