import paramiko

def check_env():
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect('10.10.10.10', username='victor', password='6433', timeout=10)
        
        commands = [
            "sudo netstat -tulnp | grep -E ':80|:8000'",
            "ls -la /var/www/",
            "ls -la /home/victor/",
            "sudo cat /etc/apache2/sites-enabled/000-default.conf"
        ]
        
        for cmd in commands:
            print(f"--- Running: {cmd} ---")
            stdin, stdout, stderr = ssh.exec_command(cmd)
            out = stdout.read().decode('utf-8').strip()
            err = stderr.read().decode('utf-8').strip()
            if out: print(out)
            if err: print(f"Error: {err}")
            
    except Exception as e:
        print(f"Connection failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    check_env()
