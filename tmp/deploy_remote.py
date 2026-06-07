import paramiko
import os
import time

def deploy():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    local_zip = 'deploy.zip'
    remote_dir = '/home/victor/sistemarh'
    remote_zip = f"{remote_dir}/deploy.zip"
    
    print("Connecting to Raspberry Pi...")
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        print("Uploading deploy.zip via SFTP...")
        sftp = ssh.open_sftp()
        sftp.put(local_zip, remote_zip)
        sftp.close()
        print("Upload complete.")
        
        commands = [
            f"cd {remote_dir} && unzip -q -o deploy.zip",
            f"cd {remote_dir} && sed -i 's|^APP_URL=.*|APP_URL=https://rh.vmsoi.xyz/|' .env || echo 'APP_URL=https://rh.vmsoi.xyz/' >> .env",
            f"cd {remote_dir} && composer install --no-dev --optimize-autoloader",
            f"cd {remote_dir} && php artisan optimize:clear",
            f"cd {remote_dir} && php artisan migrate --force",
            f"sudo chown -R www-data:www-data {remote_dir}/storage {remote_dir}/bootstrap/cache",
            f"sudo chmod -R 775 {remote_dir}/storage {remote_dir}/bootstrap/cache"
        ]
        
        for cmd in commands:
            print(f"--- Running: {cmd} ---")
            stdin, stdout, stderr = ssh.exec_command(cmd)
            # wait for command to finish
            exit_status = stdout.channel.recv_exit_status()
            out = stdout.read().decode('utf-8', errors='replace').strip()
            err = stderr.read().decode('utf-8', errors='replace').strip()
            
            if out:
                print(out.encode('ascii', 'replace').decode('ascii'))
            if err:
                print(f"Error output: {err.encode('ascii', 'replace').decode('ascii')}")
            print(f"Exit status: {exit_status}\n")
            
    except Exception as e:
        print(f"Deployment failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    deploy()
