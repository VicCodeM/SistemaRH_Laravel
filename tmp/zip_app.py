import os
import zipfile

def create_zip():
    zip_filename = 'deploy.zip'
    exclude_dirs = {'vendor', 'node_modules', '.git', '.agents', '.claude', '.kilo', '.specify', 'tmp'}
    
    # Remove existing deploy.zip if present
    if os.path.exists(zip_filename):
        os.remove(zip_filename)
        
    with zipfile.ZipFile(zip_filename, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk('.'):
            # Modify dirs in-place to exclude unwanted directories
            dirs[:] = [d for d in dirs if d not in exclude_dirs]
            
            for file in files:
                if file == zip_filename or file.endswith('.pyc') or file == '.env' or file.endswith('.py'):
                    continue
                    
                file_path = os.path.join(root, file)
                arcname = os.path.relpath(file_path, '.')
                zipf.write(file_path, arcname)
                
    print(f"Created {zip_filename} successfully.")

if __name__ == '__main__':
    create_zip()
