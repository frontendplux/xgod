(function(){

    // Inject CSS programmatically
    const css = `
        .palert-overlay {
            position: fixed;
            top:0; left:0;
            width:100%; height:100%;
            background: rgba(0,0,0,0.55);
            display:flex;
            justify-content:center;
            align-items:center;
            z-index: 999999;
            opacity:0;
            transition: opacity .25s ease;
        }
        .palert-overlay.show { opacity:1; }

        .palert-box {
            background:white;
            padding:25px;
            border-radius:15px;
            width:85%;
            max-width:350px;
            text-align:center;
            box-shadow:0 5px 20px rgba(0,0,0,0.25);
            animation: palert-pop .25s ease;
            font-family: Arial, sans-serif;
        }

        @keyframes palert-pop {
            0% { transform: scale(0.7); opacity:0; }
            100% { transform: scale(1); opacity:1; }
        }

        .palert-title {
            font-size:20px;
            font-weight:700;
            margin-bottom:10px;
        }
        .palert-msg {
            font-size:15px;
            margin-bottom:20px;
            color:#555;
        }

        .palert-btn {
            padding:10px 18px;
            border:none;
            border-radius:8px;
            color:white;
            cursor:pointer;
            font-weight:bold;
            width:110px;
        }

        .palert-success { background:#22c55e; }
        .palert-error   { background:#ef4444; }
        .palert-warning { background:#f59e0b; }
    `;

    const styleTag = document.createElement("style");
    styleTag.textContent = css;
    document.head.appendChild(styleTag);

    // Main function
    window.showAlert = function(type, title, message, onClose=null){
        const overlay = document.createElement("div");
        overlay.className = "palert-overlay";

        const box = document.createElement("div");
        box.className = "palert-box";

        box.innerHTML = `
            <div class="palert-title">${title}</div>
            <div class="palert-msg">${message}</div>
            <button class="palert-btn palert-${type}">OK</button>
        `;

        overlay.appendChild(box);
        document.body.appendChild(overlay);

        // Fade-in
        setTimeout(()=> overlay.classList.add("show"), 20);

        // Button action
        box.querySelector("button").onclick = () => {
            overlay.classList.remove("show");
            setTimeout(()=> overlay.remove(), 250);
            if(onClose) onClose();
        };
    };

})();

        class BottomSheet {
            constructor(title, content) {
                this.id = "sheet-" + Math.random().toString(36).substring(2);
                this.render(title, content);
                this.enableDrag();
            }

            render(title, content) {
                const root = document.getElementById("popup-root");

                // Overlay
                this.overlay = document.createElement("div");
                this.overlay.className = "sheet-overlay show";

                // Sheet
                this.sheet = document.createElement("div");
                this.sheet.className = "bottom-sheet show";
                this.sheet.innerHTML = `
                    <div class="drag-handle"></div>
                    <h5>${title}</h5>
                    <div>${content}</div>
                `;

                // Close when overlay clicked
                this.overlay.addEventListener("click", () => this.close());

                // Append
                root.appendChild(this.overlay);
                root.appendChild(this.sheet);
            }

            close() {
                this.sheet.classList.remove("show");
                this.overlay.classList.remove("show");

                setTimeout(() => {
                    this.sheet.remove();
                    this.overlay.remove();
                }, 300);
            }

            enableDrag() {
                let startY = 0;
                let currentY = 0;
                let dragging = false;

                const sheet = this.sheet;

                const onStart = (e) => {
                    dragging = true;
                    startY = e.touches ? e.touches[0].clientY : e.clientY;
                };

                const onMove = (e) => {
                    if (!dragging) return;
                    currentY = e.touches ? e.touches[0].clientY : e.clientY;

                    let diff = currentY - startY;
                    if (diff > 0) sheet.style.transform = `translateY(${diff}px)`;
                };

                const onEnd = () => {
                    if (!dragging) return;

                    dragging = false;

                    if (currentY - startY > 120) {
                        this.close();
                    } else {
                        sheet.style.transform = `translateY(0)`;
                    }
                };

                // Events
                sheet.addEventListener("mousedown", onStart);
                sheet.addEventListener("touchstart", onStart);

                window.addEventListener("mousemove", onMove);
                window.addEventListener("touchmove", onMove);

                window.addEventListener("mouseup", onEnd);
                window.addEventListener("touchend", onEnd);
            }
        }
