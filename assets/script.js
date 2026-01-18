// assets/script.js
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        var msg = document.getElementById('flash-message');
        if(msg) {
            msg.style.transition = "opacity 1s";
            msg.style.opacity = "0"; 
            setTimeout(function(){ msg.remove(); }, 1000); 
        }
    }, 3000); // Disparaît après 3 secondes
});