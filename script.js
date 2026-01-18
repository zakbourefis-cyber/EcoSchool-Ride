setTimeout(function() {
    var msg = document.getElementById('flash-message');
    if(msg) {
        msg.style.transition = "opacity 1s";
        msg.style.opacity = "0"; // On le rend transparent
        setTimeout(function(){ msg.remove(); }, 1000); // On le supprime du HTML
    }
}, 4000); // 4000 ms = 4 secondes
