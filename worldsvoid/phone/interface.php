<link rel="stylesheet" href="phone/style.css?v=<?php echo time(); ?>">

<div id="phone-wrapper">
    
    <div class="phone-screen-bg" id="phoneScreenBg"></div>

    <div class="phone-content-area">
        
        <div id="phone-home-screen">
            <img src="phone/images/call_button.png" class="app-icon" onclick="openApp('call')" title="Call">
            <img src="phone/images/message_button.png" class="app-icon" onclick="openApp('messages')" title="Messages">
            <img src="phone/images/contacts_button.png" class="app-icon" onclick="openApp('contacts')" title="Contacts">
            
            <img src="phone/images/map_button.png" class="app-icon" onclick="openApp('gps')" title="GPS">
            <img src="phone/images/net_button.png" class="app-icon" onclick="openApp('browser')" title="Net">
            <img src="phone/images/party_button.png" class="app-icon" onclick="openApp('party')" title="Party">
            
            <img src="phone/images/camera_button.png" class="app-icon" onclick="openApp('camera')" title="Camera">
            <img src="phone/images/radio_button.png" class="app-icon" onclick="openApp('radio')" title="Radio">
            <img src="phone/images/settings_button.png" class="app-icon" onclick="openApp('settings')" title="Settings">
        </div>

        <div id="phone-settings-screen">
            
            <div id="settings-main" class="settings-view active">
                <h3>Settings</h3>
                <div class="settings-menu-item" onclick="openSettingsMenu('display')">
                    <span>Display & Wallpaper</span> <span>></span>
                </div>
                <div class="settings-menu-item" onclick="openSettingsMenu('about')">
                    <span>About Phone</span> <span>></span>
                </div>
                
                <div class="settings-menu-item" onclick="openSettingsMenu('power')" style="border-color: #ff0044; color: #ff0044;">
                    <span>Power & System</span> <span>></span>
                </div>

                <button onclick="goHome()" class="settings-btn" style="background: #ff007f; margin-top: auto;">Close Settings</button>
            </div>

            <div id="settings-display" class="settings-view">
                <h3>Display</h3>
                <div class="bg-thumbnail-grid" id="bgGrid"></div>
                <button onclick="openSettingsMenu('main')" class="settings-btn" style="margin-top: auto;">< Back</button>
            </div>

            <div id="settings-about" class="settings-view">
                <h3>About Phone</h3>
                <div id="about-phone-content" class="about-card"></div>
                <button onclick="openSettingsMenu('main')" class="settings-btn" style="margin-top: auto;">< Back</button>
            </div>

            <div id="settings-power" class="settings-view">
                <h3 style="color: #ff0044;">Power</h3>
                
                <div class="settings-menu-item" onclick="window.location.href='portal.php'" style="border-color: #00f3ff; color: #00f3ff; margin-top: 20px;">
                    <span>Character List</span>
                </div>
                
                <div class="settings-menu-item" onclick="window.location.href='logout.php'" style="border-color: #ff0044; color: #ff0044;">
                    <span>System Power (Log Out)</span>
                </div>

                <button onclick="openSettingsMenu('main')" class="settings-btn" style="margin-top: auto;">< Back</button>
            </div>

        </div>

        <div class="phone-nav">
            <div class="home-bar" id="phone-home-bar"></div>
        </div>

        <audio id="phoneAudio" autoplay></audio>
    </div>

    <div id="phone-frame-overlay"></div>

</div>

<script src="https://unpkg.com/peerjs@1.5.2/dist/peerjs.min.js"></script>
<script src="phone/phone.js?v=<?php echo time(); ?>"></script>
<script src="phone/scripts/apps/call.js?v=<?php echo time(); ?>"></script>
<script src="phone/scripts/apps/settings.js?v=<?php echo time(); ?>"></script>