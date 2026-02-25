// MAIN PHONE OS LOGIC

document.addEventListener("DOMContentLoaded", () => {
    // Apply saved background from database immediately
    if (typeof VOID_DATA !== 'undefined' && VOID_DATA.phoneBg) {
        document.getElementById('phoneScreenBg').style.backgroundImage = `url('phone/images/${VOID_DATA.phoneBg}')`;
    }

    const phoneWrapper = document.getElementById('phone-wrapper');
    const homeBar = document.getElementById('phone-home-bar');

    // 1. CLICK TO OPEN: If phone is closed, clicking anywhere on it opens it
    phoneWrapper.addEventListener('click', (e) => {
        if (!phoneWrapper.classList.contains('active')) {
            phoneWrapper.classList.add('active');
        }
    });

    // 2. CLICK TO CLOSE: Clicking the home bar closes it
    homeBar.addEventListener('click', (e) => {
        e.stopPropagation(); // Stop the phone from immediately reopening
        if (document.getElementById('phone-settings-screen').style.display === 'flex') {
            goHome(); // Just go back to app grid if in settings
        } else {
            closePhone(); // Close the whole phone
        }
    });

    // 3. DRAG TO CLOSE: Swipe down with the mouse to hide the phone
    let startY = 0;
    let isDragging = false;

    phoneWrapper.addEventListener('mousedown', (e) => {
        if (phoneWrapper.classList.contains('active')) {
            startY = e.clientY;
            isDragging = true;
            phoneWrapper.style.transition = 'none'; // Remove smooth animation while dragging
        }
    });

    window.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        let deltaY = e.clientY - startY;
        if (deltaY > 0) { // Only allow dragging downwards
            phoneWrapper.style.transform = `translateY(${deltaY}px)`;
        }
    });

    window.addEventListener('mouseup', (e) => {
        if (!isDragging) return;
        isDragging = false;
        phoneWrapper.style.transition = ''; // Restore smooth CSS animation
        
        let deltaY = e.clientY - startY;
        if (deltaY > 150) { 
            // If dragged down more than 150px, close it completely
            closePhone();
        } else {
            // Otherwise, snap it back up
            phoneWrapper.style.transform = '';
        }
    });
});

function closePhone() {
    const wrapper = document.getElementById('phone-wrapper');
    wrapper.classList.remove('active');
    wrapper.style.transform = ''; // Clear inline drag styles
    setTimeout(goHome, 400); // Reset to home screen while hiding
}

// Return to the main icon grid
function goHome() {
    document.getElementById('phone-settings-screen').style.display = 'none';
    document.getElementById('phone-home-screen').style.display = 'grid';
}

// App Router
function openApp(appName) {
    console.log("Void OS -> Opening App: " + appName);
    
    switch(appName) {
        case 'call':
            if(typeof startCall === 'function') startCall(); else alert("Call app loading...");
            break;
        case 'settings':
            if(typeof openSettingsApp === 'function') openSettingsApp(); else alert("Settings app loading...");
            break;
        case 'messages':
             alert("Messages app coming soon!");
             break;
        default:
            alert("The " + appName + " app is currently offline.");
    }
}