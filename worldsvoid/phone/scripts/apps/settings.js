// VOID OS: SETTINGS APP

const availableBackgrounds = [
    "smart_phone_bg1.png", "smart_phone_bg2.png", "smart_phone_bg3.png",
    "smart_phone_bg4.png", "smart_phone_bg5.png", "smart_phone_bg6.png", "smart_phone_bg7.png"
];

function openSettingsApp() {
    document.getElementById('phone-home-screen').style.display = 'none';
    document.getElementById('phone-settings-screen').style.display = 'flex';
    openSettingsMenu('main'); // Always open to the main menu first
}

// Router for inside the Settings App
function openSettingsMenu(menuName) {
    // Hide all views
    document.getElementById('settings-main').classList.remove('active');
    document.getElementById('settings-display').classList.remove('active');
    document.getElementById('settings-about').classList.remove('active');
    // Hide the new power menu
    document.getElementById('settings-power').classList.remove('active'); 

    // Show the requested view
    document.getElementById('settings-' + menuName).classList.add('active');

    // Load dynamic content based on the view
    if (menuName === 'display') loadThumbnails();
    if (menuName === 'about') loadAboutInfo();
}

function loadThumbnails() {
    const grid = document.getElementById('bgGrid');
    if (grid.innerHTML.trim() === '') {
        availableBackgrounds.forEach(bg => {
            const img = document.createElement('img');
            img.src = `phone/images/${bg}`;
            img.className = 'bg-thumbnail';
            img.onclick = () => changeWallpaper(bg);
            grid.appendChild(img);
        });
    }
}

function loadAboutInfo() {
    const content = document.getElementById('about-phone-content');
    content.innerHTML = `
        <div style="color: #aaa; font-size: 0.8em; text-transform: uppercase;">Registered Owner</div>
        <div style="color: #fff; font-size: 1.2em; margin-bottom: 15px; font-weight: bold;">${VOID_DATA.charName}</div>
        
        <div style="color: #aaa; font-size: 0.8em; text-transform: uppercase;">Phone Number</div>
        <div style="color: #00f3ff; font-size: 1.2em; margin-bottom: 15px; font-weight: bold;">${VOID_DATA.phoneNumber}</div>
        
        <div style="color: #aaa; font-size: 0.8em; text-transform: uppercase;">Device IMEI</div>
        <div style="color: #fff; font-size: 0.9em; margin-bottom: 15px; font-family: monospace;">${VOID_DATA.imei}</div>
        
        <div style="color: #aaa; font-size: 0.8em; text-transform: uppercase;">OS Version</div>
        <div style="color: #fff; font-size: 0.9em;">Void OS v1.1.0</div>
    `;
}

function changeWallpaper(bgImage) {
    document.getElementById('phoneScreenBg').style.backgroundImage = `url('phone/images/${bgImage}')`;
    
    const formData = new FormData();
    formData.append('char_id', VOID_DATA.charId);
    formData.append('bg_image', bgImage);

    fetch('phone/scripts/save_phone_bg.php', { method: 'POST', body: formData })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === "success") {
            VOID_DATA.phoneBg = bgImage; 
        }
    });
}