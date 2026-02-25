#!/bin/bash

echo "🤖 Worlds Void Watcher Online."
echo "Checking for saved files every 10 seconds..."
echo "Press [CTRL+C] to stop."

while true; do
    # Check if Git sees any modified or new files
    if [[ `git status --porcelain` ]]; then
        echo "🚀 Changes detected! Syncing to GitHub..."
        
        git add .
        # Uses the current date and time as the commit message
        git commit -m "Auto-sync: $(date +'%Y-%m-%d %H:%M:%S')"
        git push
        
        echo "✅ Sync complete. Watching for new changes..."
    fi
    
    # Wait 10 seconds before checking again (prevents spamming your CPU)
    sleep 10
done
