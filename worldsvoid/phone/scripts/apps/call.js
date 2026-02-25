// VOID OS: CALL APP ENGINE (Powered by PeerJS WebRTC)
let myPeer;

function initCallApp() {
    // 1. Get Character Name securely from the main game data
    if (typeof VOID_DATA === 'undefined' || !VOID_DATA.charName) return; 

    // Create a clean "Phone Number" ID (e.g., "timetoken")
    const myPhoneNumber = VOID_DATA.charName.toLowerCase().replace(/[^a-z0-9]/g, '');
    
    // 2. Connect to PeerJS
    myPeer = new Peer(myPhoneNumber);

    myPeer.on('open', function(id) {
        console.log('Call App Online. Your number is: ' + id);
    });

    // 3. Receive Calls
    myPeer.on('call', function(call) {
        if(confirm(`Incoming Call from ${call.peer}... Answer?`)) {
            navigator.mediaDevices.getUserMedia({video: false, audio: true})
            .then((stream) => {
                call.answer(stream); // Send our audio
                call.on('stream', (remoteStream) => {
                    document.getElementById('phoneAudio').srcObject = remoteStream;
                });
            });
        }
    });
}

// 4. Make a Call (Triggered by the openApp('call') function)
function startCall() {
    const target = prompt("Enter Name to Call:");
    if (!target) return;
    
    const targetID = target.toLowerCase().replace(/[^a-z0-9]/g, '');

    navigator.mediaDevices.getUserMedia({video: false, audio: true})
    .then((stream) => {
        console.log("Dialing " + targetID + "...");
        const call = myPeer.call(targetID, stream);
        
        call.on('stream', (remoteStream) => {
            document.getElementById('phoneAudio').srcObject = remoteStream;
        });

        call.on('close', () => {
            alert("Call ended.");
        });
    })
    .catch((err) => alert("Microphone Access Denied. Cannot make calls."));
}

// Start the network listener after a short delay so the game loads first
setTimeout(initCallApp, 2000);