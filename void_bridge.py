import os
import subprocess
import time

# CONFIGURATION
PROJECT_DIR = "./" # Your game directory
OBSERVER_INTERVAL = 5 # Seconds

def sync_to_github():
    print("🚀 Syncing to GitHub Knowledge Base...")
    try:
        subprocess.run(["git", "add", "."], check=True)
        subprocess.run(["git", "commit", "-m", "Auto-sync from Gemini Bridge"], check=True)
        subprocess.run(["git", "push", "origin", "main"], check=True)
        print("✅ Sync Complete.")
    except Exception as e:
        print(f"❌ Sync failed: {e}")

if __name__ == "__main__":
    print("👾 Worlds Void Bridge Active. Type 'sync' to push updates.")
    while True:
        cmd = input("Bridge Command: ").lower()
        if cmd == "sync":
            sync_to_github()
        elif cmd == "exit":
            break