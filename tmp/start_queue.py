import paramiko

def manage_queue():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        # 1. Check if there are jobs
        cmd_check = "cd /home/victor/sistemarh && sudo mysql -e 'SELECT count(*) FROM sistemarh_laravel.jobs;'"
        print(f"--- Running: {cmd_check} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd_check)
        out = stdout.read().decode('utf-8', errors='replace').strip()
        print(out)
        
        # 2. Check if a service for sistemarh queue exists
        cmd_svc = "systemctl list-units --type=service | grep -i queue"
        print(f"--- Running: {cmd_svc} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd_svc)
        out = stdout.read().decode('utf-8', errors='replace').strip()
        print(out)
        
        # 3. Start a basic queue worker in the background using systemd or nohup if no service exists
        # Actually, let's create a systemd service for it to make it permanent
        service_file = """[Unit]
Description=SistemaRH Laravel Queue Worker
After=network.target

[Service]
User=victor
Group=www-data
Restart=always
ExecStart=/usr/bin/php /home/victor/sistemarh/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
"""
        cmd_install_svc = f"echo '{service_file}' | sudo tee /etc/systemd/system/sistemarh-queue.service"
        ssh.exec_command(cmd_install_svc)
        
        # Start and enable the service
        ssh.exec_command("sudo systemctl daemon-reload")
        ssh.exec_command("sudo systemctl enable sistemarh-queue.service")
        ssh.exec_command("sudo systemctl restart sistemarh-queue.service")
        print("sistemarh-queue.service installed and started.")
        
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    manage_queue()
